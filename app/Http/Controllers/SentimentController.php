<?php

namespace App\Http\Controllers;

use App\Models\Sentiment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Sentiment\Analyzer;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SentimentController extends Controller
{
    private $connectionString = "DefaultEndpointsProtocol=https;AccountName=lexiconwords;AccountKey=7pNvKXsdw2dVdaHjmJTf3eZTsWn17KYz//VsTbAr1TfmKcgz7dXqxFUmmt8gikYKBAn3w0IicF+H+AStmlXlTQ==;EndpointSuffix=core.windows.net";
    
    public function index()
    {
        $stats = $this->getDashboardStats();
        return view('home', $stats);
    }

    private function getDashboardStats()
    {
        $today = Carbon::today();
        $totalAnalysis = Sentiment::whereDate('created_at', $today)->count();
        
        $positiveCount = Sentiment::whereDate('created_at', $today)
            ->where('sentiment_result', 'Positive')
            ->count();
        
        $positiveRate = $totalAnalysis > 0 
            ? round(($positiveCount / $totalAnalysis) * 100, 1) 
            : 0;

        return [
            'total_analysis' => $totalAnalysis,
            'positive_rate' => $positiveRate,
            'accuracy' => 99,
            'recent_analyses' => Sentiment::latest()->take(5)->get()
        ];
    }

    public function create()
    {
        return view('analyze');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'sentiment_input' => 'nullable|string',
                'fileInput' => 'nullable|file|mimes:txt,docx,pdf',
            ]);

            $text = $request->sentiment_input ?? '';

            if ($request->hasFile('fileInput')) {
                $text .= $this->processUploadedFile($request->file('fileInput'));
            }

            if (empty(trim($text))) {
                return response()->json(['error' => 'No valid text found in the input or uploaded file.'], 400);
            }

            $analyzer = new Analyzer();
            $sentimentScores = $analyzer->getSentiment($text);
            $words = $this->getLexiconWords();
            
            list($positiveCount, $negativeCount, $positiveMatches, $negativeMatches) = 
                $this->analyzeWords(strtolower($text), $sentimentScores, $words);
            
            list($sentimentResult, $sentimentEmotion) = 
                $this->determineSentiment($positiveCount, $negativeCount);
            
            $textFeatures = $this->analyzeTextFeatures($text);
            $sentimentEmotion = $this->adjustEmotion($sentimentResult, $sentimentEmotion, $textFeatures);
            
            $this->saveSentimentToDatabase($text, $sentimentResult, $sentimentEmotion, $textFeatures);

            return response()->json([
                'sentiment_input' => $text,
                'positive_count' => $positiveCount,
                'negative_count' => $negativeCount,
                'positive_matches' => $positiveMatches,
                'negative_matches' => $negativeMatches,
                'sentiment_result' => $sentimentResult,
                'sentiment_emotion' => $sentimentEmotion,
                'text_features' => implode('; ', $textFeatures),
            ]);

        } catch (\Exception $e) {
            \Log::error('Sentiment analysis error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function processUploadedFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        try {
            switch ($extension) {
                case 'txt':
                    return file_get_contents($file->getRealPath());
                case 'docx':
                    return $this->processDocxFile($file);
                case 'pdf':
                    return $this->processPdfFile($file);
                default:
                    return '';
            }
        } catch (\Exception $e) {
            \Log::error('File processing error: ' . $e->getMessage());
            throw new \Exception('Failed to process the uploaded file.');
        }
    }

    private function processDocxFile($file)
    {
        $phpWord = IOFactory::load($file->getRealPath());
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . ' ';
                }
            }
        }
        return $text;
    }

    private function processPdfFile($file)
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($file->getRealPath());
        return $pdf->getText();
    }

    private function getLexiconWords()
    {
        $blobClient = BlobRestProxy::createBlobService($this->connectionString);
        try {
            $positiveBlob = $blobClient->getBlob('lexicon', 'positive_words.txt');
            $negativeBlob = $blobClient->getBlob('lexicon', 'negative_words.txt');
            
            return [
                'positive' => array_map('trim', explode("\n", stream_get_contents($positiveBlob->getContentStream()))),
                'negative' => array_map('trim', explode("\n", stream_get_contents($negativeBlob->getContentStream())))
            ];
        } catch (ServiceException $e) {
            \Log::error('Azure Blob Storage error: ' . $e->getMessage());
            throw new \Exception('Failed to retrieve lexicon files.');
        }
    }

    private function analyzeWords($text, $sentimentScores, $words)
    {
        $positiveCount = $sentimentScores['pos'];
        $negativeCount = $sentimentScores['neg'];
        $positiveMatches = [];
        $negativeMatches = [];

        foreach (preg_split('/\s+/', $text) as $word) {
            $cleanWord = trim($word, " \t\n\r\0\x0B.,!?");
            if (!array_key_exists($cleanWord, $sentimentScores)) {
                if (in_array($cleanWord, $words['positive'])) {
                    $positiveCount++;
                    $positiveMatches[] = $cleanWord;
                }
                if (in_array($cleanWord, $words['negative'])) {
                    $negativeCount++;
                    $negativeMatches[] = $cleanWord;
                }
            }
        }

        return [$positiveCount, $negativeCount, $positiveMatches, $negativeMatches];
    }

    private function determineSentiment($positiveCount, $negativeCount)
    {
        if ($positiveCount > $negativeCount) {
            return ['Positive', 'Happy'];
        } elseif ($negativeCount > $positiveCount) {
            return ['Negative', 'Sad'];
        }
        return ['Neutral', 'Neutral'];
    }

    private function adjustEmotion($sentimentResult, $emotion, $features)
    {
        if (in_array('Contains all-caps', $features)) {
            if ($sentimentResult === 'Positive') {
                return 'Excited';
            } elseif ($sentimentResult === 'Negative') {
                return 'Angry';
            }
        }
        return $emotion;
    }

    private function saveSentimentToDatabase($text, $result, $emotion, $features)
    {
        return Sentiment::create([
            'sentiment_input' => $text,
            'sentiment_result' => $result,
            'sentiment_emotion' => $emotion,
            'text_features' => implode('; ', $features),
            'sentiment_date' => now(),
        ]);
    }

    // Rest of your existing methods remain unchanged
    private function analyzeTextFeatures($text)
    {
        $features = [];
        
        if (preg_match('/[A-Z]{2,}/', $text)) {
            $features[] = 'Contains all-caps';
        }
        
        $wordCount = str_word_count($text);
        $features[] = "Word count: $wordCount";
        
        $sentenceCount = preg_match_all('/[.!?]+/', $text, $matches);
        $features[] = "Sentence count: $sentenceCount";
        
        $words = str_word_count($text, 1);
        if (count($words) > 0) {
            $avgWordLength = array_sum(array_map('strlen', $words)) / count($words);
            $features[] = "Average word length: " . number_format($avgWordLength, 1) . " characters";
        }
        
        $exclamationCount = substr_count($text, '!');
        if ($exclamationCount > 0) {
            $features[] = "Exclamation marks: $exclamationCount";
        }
        
        $questionCount = substr_count($text, '?');
        if ($questionCount > 0) {
            $features[] = "Question marks: $questionCount";
        }

        return $features;
    }

    public function history()
    {
        $sentiments = Sentiment::whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('history', compact('sentiments'));
    }

    public function softDelete($id)
    {
        $sentiment = Sentiment::findOrFail($id);
        $sentiment->delete();

        return response()->json([
            'message' => 'Sentiment deleted successfully.',
            'id' => $id,
        ]);
    }

    public function generateReport($id)
{
    if (request()->wantsJson()) {
        // For AJAX request, return just the report content
        $sentiment = Sentiment::findOrFail($id);
        $analyzer = new Analyzer();
        $sentimentScores = $analyzer->getSentiment($sentiment->sentiment_input);
        
        $total = array_sum($sentimentScores) ?: 1;
        $data = [
            'title' => 'Sentiment Analysis Report',
            'sentiment' => $sentiment,
            'date' => now(),
            'scores' => [
                'positive' => round(($sentimentScores['pos'] / $total) * 100, 1),
                'negative' => round(($sentimentScores['neg'] / $total) * 100, 1),
                'neutral' => round(100 - (($sentimentScores['pos'] + $sentimentScores['neg']) / $total) * 100, 1)
            ]
        ];

        return view('report', $data)->render();
    }
}

public function downloadReport($id)
{
    $sentiment = Sentiment::findOrFail($id);
    $analyzer = new Analyzer();
    $sentimentScores = $analyzer->getSentiment($sentiment->sentiment_input);
    
    $total = array_sum($sentimentScores) ?: 1;
    $data = [
        'title' => 'Sentiment Analysis Report',
        'sentiment' => $sentiment,
        'date' => now(),
        'scores' => [
            'positive' => round(($sentimentScores['pos'] / $total) * 100, 1),
            'negative' => round(($sentimentScores['neg'] / $total) * 100, 1),
            'neutral' => round(100 - (($sentimentScores['pos'] + $sentimentScores['neg']) / $total) * 100, 1)
        ]
    ];

    $pdf = PDF::loadView('report', $data);
    return $pdf->download('sentiment_report.pdf');
}

    public function export(Request $request)
{
    $ids = json_decode($request->ids);
    $format = $request->format;
    $sentiments = Sentiment::findMany($ids);
    
    switch($format) {
        case 'pdf':
            $pdf = PDF::loadView('report', [
                'title' => 'Batch Sentiment Analysis',
                'sentiments' => $sentiments,
                'date' => now()
            ]);
            return $pdf->download('sentiment_analysis.pdf');
            
        case 'csv':
            return response()->streamDownload(function() use ($sentiments) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Input', 'Result', 'Emotion', 'Features', 'Date']);
                foreach($sentiments as $sentiment) {
                    fputcsv($file, [
                        $sentiment->sentiment_input,
                        $sentiment->sentiment_result,
                        $sentiment->sentiment_emotion,
                        $sentiment->text_features,
                        $sentiment->created_at
                    ]);
                }
                fclose($file);
            }, 'sentiment_analysis.csv');
            
        default:
            return response()->json(['error' => 'Unsupported format'], 400);
    }
}

    private function generateBatchReport($sentiments)
    {
        $data = [
            'title' => 'Batch Sentiment Analysis Report',
            'sentiments' => $sentiments,
            'date' => now()
        ];

        $pdf = PDF::loadView('reports.batch', $data);
        return $pdf->download('sentiment_batch_report.pdf');
    }

    private function exportCSV($sentiments)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sentiments.csv"'
        ];

        $callback = function() use ($sentiments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Input', 'Result', 'Emotion', 'Features', 'Date']);

            foreach ($sentiments as $sentiment) {
                fputcsv($file, [
                    $sentiment->sentiment_input,
                    $sentiment->sentiment_result,
                    $sentiment->sentiment_emotion,
                    $sentiment->text_features,
                    $sentiment->created_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function settings()
{
    return view('settings', [
        'theme' => session('theme', 'dark'),
        'language' => session('language', 'en'),
        'notifications' => session('notifications', true)
    ]);
}

public function updateSettings(Request $request)
{
    $validated = $request->validate([
        'theme' => 'required|in:light,dark',
        'language' => 'required|in:en,es,fr',
        'notifications' => 'boolean'
    ]);

    session([
        'theme' => $validated['theme'],
        'language' => $validated['language'],
        'notifications' => $request->has('notifications')
    ]);

    return back()->with('success', 'Settings updated successfully');
}

}