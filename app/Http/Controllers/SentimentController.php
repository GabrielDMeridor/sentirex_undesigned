<?php

namespace App\Http\Controllers;

use App\Models\Sentiment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;// Import the PDF facade

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
        $request->validate(['sentiment_input' => 'required|string']);
    
        $positiveWordsPath = base_path('storage/app/lexicon/positive_words.txt');
        $negativeWordsPath = base_path('storage/app/lexicon/negative_words.txt');
    
        $positiveWords = array_map('trim', explode("\n", file_get_contents($positiveWordsPath)));
        $negativeWords = array_map('trim', explode("\n", file_get_contents($negativeWordsPath)));
    
        $text = strtolower($request->sentiment_input);
        $words = preg_split('/\s+/', $text);
    
        $positiveCount = 0;
        $negativeCount = 0;
        $positiveMatches = [];
        $negativeMatches = [];
    
        foreach ($words as $word) {
            $word = trim($word, " \t\n\r\0\x0B.,!?");
            if (in_array($word, $positiveWords)) {
                $positiveCount++;
                $positiveMatches[] = $word;
            }
            if (in_array($word, $negativeWords)) {
                $negativeCount++;
                $negativeMatches[] = $word;
            }
        }
    
        $sentimentResult = 'Neutral';
        $sentimentEmotion = 'Neutral';
    
        if ($positiveCount > $negativeCount) {
            $sentimentResult = 'Positive';
            $sentimentEmotion = 'Happy';
        } elseif ($negativeCount > $positiveCount) {
            $sentimentResult = 'Negative';
            $sentimentEmotion = 'Sad';
        }
    
        Sentiment::create([
            'sentiment_input' => $request->sentiment_input,
            'sentiment_result' => $sentimentResult,
            'sentiment_emotion' => $sentimentEmotion,
            'sentiment_date' => now(),
        ]);
    
        return response()->json([
            'sentiment_input' => $request->sentiment_input,
            'positive_count' => $positiveCount,
            'negative_count' => $negativeCount,
            'positive_matches' => $positiveMatches,
            'negative_matches' => $negativeMatches,
            'sentiment_result' => $sentimentResult,
            'sentiment_emotion' => $sentimentEmotion,
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
    
        // Return a JSON response
        return response()->json([
            'message' => 'Sentiment deleted successfully.',
            'id' => $id,
        ]);
    }
    

    public function generateReport($id)
    {
        $sentiment = Sentiment::findOrFail($id);

        // Prepare the data for the report
        $data = [
            'input' => $sentiment->sentiment_input,
            'result' => $sentiment->sentiment_result,
            'emotion' => $sentiment->sentiment_emotion,
            'date' => $sentiment->sentiment_date,
        ];

        // Load a view for the PDF content
        $pdf = Pdf::loadView('report', $data);

        // Return the PDF for download
        $filename = "sentiment_report_{$sentiment->id}.pdf";
        return $pdf->download($filename);
    }
}
