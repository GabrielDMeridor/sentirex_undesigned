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
            background-color: #1a202c; /* Dark background */
            color: #e2e8f0; /* Light text for better contrast */
            font-size: 16px; /* Comfortable reading size */
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #4c51bf, #6b46c1); /* Darker gradient */
            color: #ffffff; /* White text */
            border-radius: 10px;
        }
        .header h1 {
            font-size: 2.5em;
            margin: 0;
        }
        .header p {
            font-size: 1em;
        }
        .content {
            padding: 20px;
            border: 1px solid #2d3748; /* Subtle border */
            border-radius: 10px;
            margin: 20px 0;
            background: #2d3748; /* Dark card background */
            color: #e2e8f0; /* Ensure light text */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4); /* Subtle shadow */
        }
        .section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #4a5568; /* Subtle section divider */
        }
        .label {
            font-weight: bold;
            color: #a0aec0; /* Muted light text */
            font-size: 1.1em;
        }
        .value {
            margin-left: 10px;
            color: #edf2f7; /* Lighter text for values */
            font-size: 1em;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            padding: 20px;
            background: #1a202c; /* Match body background */
            border-radius: 10px;
            border: 1px solid #2d3748; /* Subtle border */
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
            color: #a0aec0;
        }
        .positive { color: #48bb78; } /* Green for positive */
        .neutral { color: #ecc94b; } /* Yellow for neutral */
        .negative { color: #f56565; } /* Red for negative */
        a, a:visited {
            color: #90cdf4; /* Light blue for links */
            text-decoration: none;
        }
        a:hover {
            color: #63b3ed; /* Brighter blue on hover */
            text-decoration: underline;
        }
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
