<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Batch Sentiment Analysis Report</title>
    <style>
        body { font-family: Arial; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 20px; }
        .content { margin-top: 20px; }
        .sentiment { border-bottom: 1px solid #eee; padding: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Batch Sentiment Analysis Report</h1>
        <p>Generated on {{ $date->format('F j, Y \a\t h:i A') }}</p>
    </div>
    <div class="content">
        @foreach($sentiments as $sentiment)
        <div class="sentiment">
            <p><strong>Input:</strong> {{ $sentiment->sentiment_input }}</p>
            <p><strong>Result:</strong> {{ $sentiment->sentiment_result }}</p>
            <p><strong>Emotion:</strong> {{ $sentiment->sentiment_emotion }}</p>
            <p><strong>Features:</strong> {{ $sentiment->text_features }}</p>
            <p><strong>Date:</strong> {{ $sentiment->created_at }}</p>
        </div>
        @endforeach
    </div>
</body>
</html>