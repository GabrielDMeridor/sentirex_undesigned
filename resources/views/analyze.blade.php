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

        #highlightedTextSection {
            display: none;
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
    <form id="analyzeForm" enctype="multipart/form-data">
        @csrf
        <textarea name="sentiment_input" id="sentiment_input" placeholder="Enter text to analyze" ></textarea><br>
        <label for="fileInput">Or upload a file:</label>
        <input type="file" name="fileInput" id="fileInput" accept=".txt,.docx,.pdf"><br>
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
        <p><strong>Text Features:</strong> <span id="textFeatures"></span></p>
    </div>

    <!-- Highlighted Text Section -->
    <div id="highlightedTextSection" style="margin-top: 20px;">
        <h2>Highlighted Text:</h2>
        <p id="highlightedText" style="white-space: pre-wrap; background-color: #f9f9f9; padding: 10px; border: 1px solid #ccc; border-radius: 5px;"></p>
    </div>

    <script>
    $(document).on('submit', '#analyzeForm', function (e) {
        e.preventDefault();

        const formData = new FormData();
        const fileInput = $('#fileInput')[0].files[0];

        formData.append('sentiment_input', $('#sentiment_input').val());
        if (fileInput) {
            formData.append('fileInput', fileInput);
        }
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '{{ route("store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#inputText').text(response.sentiment_input);
                $('#positiveCount').text(response.positive_count);
                $('#negativeCount').text(response.negative_count);
                $('#positiveMatches').text(response.positive_matches.join(', '));
                $('#negativeMatches').text(response.negative_matches.join(', '));
                $('#sentimentResult').text(response.sentiment_result);
                $('#sentimentEmotion').text(response.sentiment_emotion);
                $('#textFeatures').text(response.text_features);

                // Highlight positive and negative words
                let text = response.sentiment_input;
                let positiveWords = response.positive_matches;
                let negativeWords = response.negative_matches;

                let highlightedText = text.split(/\b/).map(word => {
                    let cleanWord = word.trim().toLowerCase();
                    if (positiveWords.includes(cleanWord)) {
                        return `<span style="background-color: #d4edda; color: #155724;">${word}</span>`;
                    } else if (negativeWords.includes(cleanWord)) {
                        return `<span style="background-color: #f8d7da; color: #721c24;">${word}</span>`;
                    }
                    return word;
                }).join('');

                $('#highlightedText').html(highlightedText);
                $('#highlightedTextSection').show();

                $('#analysisResults').show();
                $('#successMessage')
                    .text('Input has been successfully analyzed.')
                    .fadeIn()
                    .delay(800)
                    .fadeOut();
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    alert(xhr.responseJSON.error);
                } else {
                    alert('An error occurred while analyzing the text.');
                }
            }

                });
            });
    </script>
</body>
</html>
