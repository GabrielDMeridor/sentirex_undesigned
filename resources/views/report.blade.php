<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border-radius: 10px;
        }
        .content {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin: 20px 0;
            background: #fff;
        }
        .section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .label {
            font-weight: bold;
            color: #374151;
        }
        .value {
            margin-left: 10px;
            color: #1f2937;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            padding: 20px;
            background: #f9fafb;
            border-radius: 10px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
        }
        .stat-label {
            font-size: 14px;
            color: #6b7280;
        }
        .positive { color: #10b981; }
        .negative { color: #ef4444; }
        .neutral { color: #6b7280; }
        @page {
            margin: 0cm 0cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated on {{ $date->format('F j, Y \a\t h:i A') }}</p>
    </div>

    <div class="content">
        <div class="stats">
            <div class="stat-item">
                <div class="stat-value positive">{{ $scores['positive'] }}%</div>
                <div class="stat-label">Positive</div>
            </div>
            <div class="stat-item">
                <div class="stat-value neutral">{{ $scores['neutral'] }}%</div>
                <div class="stat-label">Neutral</div>
            </div>
            <div class="stat-item">
                <div class="stat-value negative">{{ $scores['negative'] }}%</div>
                <div class="stat-label">Negative</div>
            </div>
        </div>

        <div class="section">
            <span class="label">Input Text:</span>
            <p class="value">{{ $sentiment->sentiment_input }}</p>
        </div>

        <div class="section">
            <span class="label">Sentiment Result:</span>
            <span class="value">{{ $sentiment->sentiment_result }}</span>
        </div>

        <div class="section">
            <span class="label">Emotion:</span>
            <span class="value">{{ $sentiment->sentiment_emotion }}</span>
        </div>

        <div class="section">
            <span class="label">Text Features:</span>
            <p class="value">{{ $sentiment->text_features }}</p>
        </div>
    </div>
</body>
</html>