<!DOCTYPE html>
<html>
<head>
    <title>Sentiment History</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; 
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

        td {
            max-height: 100px;
            overflow: auto;
            word-wrap: break-word;
        }

        td:first-child {
            max-width: 200px;
        }

        td a {
            text-decoration: none;
            color: blue;
        }

        .delete-btn, .report-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }

        .delete-btn:hover, .report-btn:hover {
            background-color: darkred;
        }

        #success-message {
            display: none;
            color: green;
            margin-bottom: 15px;
        }

        #reportModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border: 1px solid #ccc;
            padding: 20px;
            z-index: 1000;
            width: 80%;
            max-height: 80%;
            overflow-y: auto;
        }

        #modalBackdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        #downloadReport, #closeModal {
            margin-top: 10px;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 3px;
        }

        #downloadReport {
            background-color: green;
            color: white;
        }

        #closeModal {
            background-color: red;
            color: white;
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
                        <button class="report-btn" data-id="{{ $sentiment->id }}" style="background-color: green; color: white;">Generate Report</button>
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Modal for Report -->
    <div id="reportModal">
        <div id="reportContent"></div>
        <button id="downloadReport">Download PDF</button>
        <button id="closeModal">Close</button>
    </div>
    <div id="modalBackdrop"></div>

    <script>
        $(document).ready(function () {
            // Delete sentiment
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

            // Show report in modal
            $('.report-btn').click(function () {
                const sentimentId = $(this).data('id');

                // Fetch the report content
                $.ajax({
                    url: "{{ route('generateReport', '') }}/" + sentimentId,
                    type: 'GET',
                    data: { preview: true },
                    success: function (response) {
                        $('#reportContent').html(response); // Load the content into the modal
                        $('#reportModal, #modalBackdrop').show(); // Show modal and backdrop
                        $('#downloadReport').data('id', sentimentId); // Save ID for download
                    },
                    error: function (xhr) {
                        alert('Error generating report.');
                    }
                });
            });

            // Download report as PDF
            $('#downloadReport').click(function () {
                const sentimentId = $(this).data('id');
                window.location.href = "{{ route('generateReport', '') }}/" + sentimentId; // Redirect to download
            });

            // Close the modal
            $('#closeModal, #modalBackdrop').click(function () {
                $('#reportModal, #modalBackdrop').hide();
            });
        });
    </script>
</body>
</html>
