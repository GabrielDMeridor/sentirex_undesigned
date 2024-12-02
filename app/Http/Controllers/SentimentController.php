<?php

namespace App\Http\Controllers;

use App\Models\Sentiment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Sentiment\Analyzer;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

class SentimentController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function create()
    {
        return view('analyze');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sentiment_input' => 'nullable|string',
            'fileInput' => 'nullable|file|mimes:txt,docx,pdf',
        ]);
    
        $text = $request->sentiment_input ?? ''; // Start with the input text or an empty string.
    
        // Process uploaded file if present
        if ($request->hasFile('fileInput')) {
            $file = $request->file('fileInput');
            $extension = $file->getClientOriginalExtension();
    
            try {
                if ($extension === 'txt') {
                    $text .= file_get_contents($file->getRealPath());
                } elseif ($extension === 'docx') {
                    $phpWord = IOFactory::load($file->getRealPath());
                    $sections = $phpWord->getSections();
                    foreach ($sections as $section) {
                        $elements = $section->getElements();
                        foreach ($elements as $element) {
                            if (method_exists($element, 'getText')) {
                                $text .= $element->getText() . ' ';
                            }
                        }
                    }
                } elseif ($extension === 'pdf') {
                    $parser = new Parser();
                    $pdf = $parser->parseFile($file->getRealPath());
                    $text .= $pdf->getText();
                }
            } catch (\Exception $e) {
                \Log::error('File parsing error: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to process the uploaded file.'], 500);
            }
        }
    
        // Check if we now have valid text
        if (empty(trim($text))) {
            return response()->json(['error' => 'No valid text found in the input or uploaded file.'], 400);
        }

        $lowercaseText = strtolower($text);
    
        // Step 1: Analyze sentiment using PHP Sentiment Analyzer
        $analyzer = new Analyzer();
        $sentimentScores = $analyzer->getSentiment($text);
    
        $positiveCount = $sentimentScores['pos'];
        $negativeCount = $sentimentScores['neg'];
        $positiveMatches = [];
        $negativeMatches = [];
    
        // Step 2: Load lexicon files for unmatched words
        $positiveWordsPath = base_path('storage/app/lexicon/positive_words.txt');
        $negativeWordsPath = base_path('storage/app/lexicon/negative_words.txt');
    
        $positiveWords = array_map('trim', explode("\n", file_get_contents($positiveWordsPath)));
        $negativeWords = array_map('trim', explode("\n", file_get_contents($negativeWordsPath)));
    
        // Count words using lexicon files
        $words = preg_split('/\s+/', $lowercaseText);
    
        foreach ($words as $word) {
            $cleanWord = trim($word, " \t\n\r\0\x0B.,!?");
    
            // Check if the word is not already accounted for by the analyzer
            if (!array_key_exists($cleanWord, $sentimentScores)) {
                if (in_array($cleanWord, $positiveWords)) {
                    $positiveCount++;
                    $positiveMatches[] = $cleanWord;
                }
                if (in_array($cleanWord, $negativeWords)) {
                    $negativeCount++;
                    $negativeMatches[] = $cleanWord;
                }
            }
        }
    
        // Step 3: Determine overall sentiment and emotion
        $sentimentResult = 'Neutral';
        $sentimentEmotion = 'Neutral';
    
        if ($positiveCount > $negativeCount) {
            $sentimentResult = 'Positive';
            $sentimentEmotion = 'Happy';
        } elseif ($negativeCount > $positiveCount) {
            $sentimentResult = 'Negative';
            $sentimentEmotion = 'Sad';
        }
    
        // Check for all-caps (adjust emotion if needed)
        $textFeatures = [];
        if (preg_match('/[A-Z]{2,}/', $text)) {
            $textFeatures[] = 'Contains all-caps';
            if ($sentimentResult === 'Positive') {
                $sentimentEmotion = 'Excited';
            } elseif ($sentimentResult === 'Negative') {
                $sentimentEmotion = 'Angry';
            }
        }
    
        // Save sentiment analysis to the database
        Sentiment::create([
            'sentiment_input' => $text,
            'sentiment_result' => $sentimentResult,
            'sentiment_emotion' => $sentimentEmotion,
            'text_features' => implode('; ', $textFeatures),
            'sentiment_date' => now(),
        ]);
    
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
    }
    
    public function history()
    {
        $sentiments = Sentiment::whereNull('deleted_at')->get();
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
        $sentiment = Sentiment::findOrFail($id);

        $data = [
            'input' => $sentiment->sentiment_input,
            'result' => $sentiment->sentiment_result,
            'emotion' => $sentiment->sentiment_emotion,
            'text_features' => $sentiment->text_features,
            'date' => $sentiment->sentiment_date,
        ];

        $pdf = Pdf::loadView('report', $data);
        $filename = "sentiment_report_{$sentiment->id}.pdf";
        return $pdf->download($filename);
    }
}
