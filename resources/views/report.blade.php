<!DOCTYPE html>
<html>
<head>
    <title>Sentiment Analysis Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .content {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sentiment Analysis Report</h1>
    </div>
    <div class="content">
        <p><strong>Input Text:</strong> {{ $input }}</p>
        <p><strong>Sentiment Result:</strong> {{ $result }}</p>
        <p><strong>Emotion:</strong> {{ $emotion }}</p>
        <p><strong>Text Features:</strong> {{ $text_features }}</p>
        <p><strong>Date:</strong> {{ $date }}</p>
    </div>
</body>
</html>
