<!DOCTYPE html>
<html>
<head>
    <a href="{{ route('home') }}">Home</a> |
    <a href="{{ route('analyze') }}">Analyze Sentiments</a> 
    <title>Sentiment History</title>

    <!-- Include jQuery from CDN (or you can download it locally) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Sentiment Analysis History</h1>

    <!-- Success message placeholder -->
    <div id="success-message" style="display: none; color: green; margin-bottom: 15px;"></div>

    <table>
        <thead>
            <tr>
                <th>Input</th>
                <th>Result</th>
                <th>Emotion</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sentiments as $sentiment)
                <tr id="row-{{ $sentiment->id }}">
                    <td>{{ $sentiment->sentiment_input }}</td>
                    <td>{{ $sentiment->sentiment_result }}</td>
                    <td>{{ $sentiment->sentiment_emotion }}</td>
                    <td>{{ $sentiment->sentiment_date }}</td>
                    <td>
                        <button class="delete-btn" data-id="{{ $sentiment->id }}">Delete</button>
                        <a href="{{ route('generateReport', $sentiment->id) }}" target="_blank">Generate Report</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- AJAX Script -->
    <script>
        $(document).ready(function () {
            // Handle delete button click
            $('.delete-btn').click(function () {
                const sentimentId = $(this).data('id'); // Get sentiment ID
                const row = $('#row-' + sentimentId); // Get the row element

                if (confirm('Are you sure you want to delete this sentiment?')) {
                    // Send AJAX POST request to delete the sentiment
                    $.ajax({
                        url: "{{ route('softDelete', '') }}/" + sentimentId, // Append ID to the route
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}' // Include CSRF token
                        },
                        success: function (response) {
                            // Show success message
                            $('#success-message').text(response.message).fadeIn().delay(800).fadeOut();

                            // Remove the row from the table
                            row.fadeOut(0, function () {
                                $(this).remove();
                            });
                        },
                        error: function (xhr) {
                            alert('Error deleting sentiment: ' + xhr.responseJSON.message);
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
