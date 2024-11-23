<!DOCTYPE html>
<html>
<head>
    <title>Analyze Sentiments</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #successMessage {
            display: none;
            padding: 10px;
            margin-bottom: 10px;
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <a href="{{ route('home') }}">Home</a> |
    <a href="{{ route('history') }}">View History</a>
    <h1>Analyze Sentiments</h1>

    <!-- Success Message -->
    <div id="successMessage"></div>

    <!-- Sentiment Analysis Form -->
    <form id="analyzeForm">
        @csrf
        <textarea name="sentiment_input" id="sentiment_input" placeholder="Enter text to analyze" required></textarea><br>
        <button type="submit">Analyze</button>
    </form>

    <!-- Results Section -->
    <div id="analysisResults" style="display: none; margin-top: 20px;">
        <h2>Analysis Results:</h2>
        <p><strong>Input Text:</strong> <span id="inputText"></span></p>
        <p><strong>Positive Words Count:</strong> <span id="positiveCount"></span></p>
        <p><strong>Negative Words Count:</strong> <span id="negativeCount"></span></p>
        <p><strong>Positive Words Found:</strong> <span id="positiveMatches"></span></p>
        <p><strong>Negative Words Found:</strong> <span id="negativeMatches"></span></p>
        <p><strong>Overall Sentiment:</strong> <span id="sentimentResult"></span></p>
        <p><strong>Emotion:</strong> <span id="sentimentEmotion"></span></p>
    </div>

    <script>
        $(document).on('submit', '#analyzeForm', function(e) {
            e.preventDefault(); // Prevent default form submission

            const formData = {
                sentiment_input: $('#sentiment_input').val(),
                _token: '{{ csrf_token() }}' // Include CSRF token
            };

            $.ajax({
                url: '{{ route("store") }}', // Laravel route for sentiment analysis
                method: 'POST',
                data: formData,
                success: function(response) {
                    // Update the results section with response data
                    $('#inputText').text(response.sentiment_input);
                    $('#positiveCount').text(response.positive_count);
                    $('#negativeCount').text(response.negative_count);
                    $('#positiveMatches').text(response.positive_matches.join(', '));
                    $('#negativeMatches').text(response.negative_matches.join(', '));
                    $('#sentimentResult').text(response.sentiment_result);
                    $('#sentimentEmotion').text(response.sentiment_emotion);

                    // Show the results section
                    $('#analysisResults').show();

                    // Show success message
                    $('#successMessage')
                        .text('Input has been successfully analyzed.')
                        .fadeIn()
                        .delay(800) // Show for 2 seconds
                        .fadeOut(); // Then fade out
                },
                error: function(xhr) {
                    alert('An error occurred while analyzing the text.');
                }
            });
        });
    </script>
</body>
</html>
