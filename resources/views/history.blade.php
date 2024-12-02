<!DOCTYPE html>
<html>
<head>
    <title>Sentiment History</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Ensures the table has a fixed layout */
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
        }

        /* For scrollable cells */
        td {
            max-height: 100px; /* Set a maximum height */
            overflow: auto; /* Enable scroll for overflowing content */
            word-wrap: break-word; /* Break words for long unbroken text */
        }

        td:first-child {
            max-width: 200px; /* Set a maximum width for the Input column */
        }

        td a {
            text-decoration: none;
            color: blue;
        }

        .delete-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }

        .delete-btn:hover {
            background-color: darkred;
        }

        #success-message {
            display: none;
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <a href="{{ route('home') }}">Home</a> |
    <a href="{{ route('analyze') }}">Analyze Sentiments</a>
    <h1>Sentiment Analysis History</h1>

    <div id="success-message"></div>

    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Input</th>
                <th style="width: 10%;">Result</th>
                <th style="width: 10%;">Emotion</th>
                <th style="width: 20%;">Text Features</th>
                <th style="width: 15%;">Date</th>
                <th style="width: 20%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sentiments as $sentiment)
                <tr id="row-{{ $sentiment->id }}">
                    <td title="{{ $sentiment->sentiment_input }}">
                        <div style="max-height: 100px; overflow: auto;">
                            {{ $sentiment->sentiment_input }}
                        </div>
                    </td>
                    <td>{{ $sentiment->sentiment_result }}</td>
                    <td>{{ $sentiment->sentiment_emotion }}</td>
                    <td>
                        <div style="max-height: 100px; overflow: auto;">
                            {{ $sentiment->text_features }}
                        </div>
                    </td>
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
