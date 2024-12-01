<!DOCTYPE html>
<html>
<head>
    <title>Sentiment History</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <a href="{{ route('home') }}">Home</a> |
    <a href="{{ route('analyze') }}">Analyze Sentiments</a>
    <h1>Sentiment Analysis History</h1>

    <div id="success-message" style="display: none; color: green; margin-bottom: 15px;"></div>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Input</th>
                <th>Result</th>
                <th>Emotion</th>
                <th>Text Features</th>
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
                    <td>{{ $sentiment->text_features}}</td>
                    <td>{{ $sentiment->sentiment_date }}</td>
                    <td>
                        <button class="delete-btn" data-id="{{ $sentiment->id }}">Delete</button>
                        <a href="{{ route('generateReport', $sentiment->id) }}" target="_blank">Generate Report</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        $(document).ready(function () {
            $('.delete-btn').click(function () {
                const sentimentId = $(this).data('id');
                const row = $('#row-' + sentimentId);

                if (confirm('Are you sure you want to delete this sentiment?')) {
                    $.ajax({
                        url: "{{ route('softDelete', '') }}/" + sentimentId,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (response) {
                            $('#success-message').text(response.message).fadeIn().delay(800).fadeOut();
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
