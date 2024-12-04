@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Input</th>
                    <th>Result</th>
                    <th>Emotion</th>
                    <th>Features</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sentiments as $sentiment)
                <tr>
                    <td>{{ Str::limit($sentiment->sentiment_input, 50) }}</td>
                    <td>
                        <span class="badge badge-{{ $sentiment->sentiment_result === 'Positive' ? 'success' : ($sentiment->sentiment_result === 'Negative' ? 'error' : 'info') }}">
                            {{ $sentiment->sentiment_result }}
                        </span>
                    </td>
                    <td>
                        <span class="flex items-center">
                            {{ $sentiment->sentiment_emotion }}
                            <span class="ml-2">
                                {{ $sentiment->sentiment_emotion === 'Happy' ? '😊' : ($sentiment->sentiment_emotion === 'Sad' ? '😢' : '😐') }}
                            </span>
                        </span>
                    </td>
                    <td>{{ Str::limit($sentiment->text_features, 30) }}</td>
                    <td>{{ $sentiment->created_at->diffForHumans() }}</td>
                    <td>
                        <div class="flex gap-2">
                            <button onclick="showReport({{ $sentiment->id }})" class="btn btn-ghost btn-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </button>
                            <form action="{{ route('softDelete', $sentiment->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-sm text-error">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Report Modal -->
<dialog id="reportModal" class="modal">
    <div class="modal-box max-w-4xl">
        <h3 class="font-bold text-lg mb-4">Sentiment Analysis Report</h3>
        <div id="reportContent"></div>
        <div class="modal-action">
            <button onclick="downloadReport()" class="btn btn-primary">Download PDF</button>
            <form method="dialog">
                <button class="btn">Close</button>
            </form>
        </div>
    </div>
</dialog>

@push('scripts')
<script>
let currentReportId = null;

function showReport(id) {
    currentReportId = id;
    const modal = document.getElementById('reportModal');
    const reportContent = document.getElementById('reportContent');
    
    reportContent.innerHTML = '<div class="flex justify-center p-4"><span class="loading loading-spinner loading-lg"></span></div>';
    modal.showModal();

    fetch(`/report/${id}`)
        .then(response => response.text())
        .then(html => {
            reportContent.innerHTML = html;
        })
        .catch(error => {
            reportContent.innerHTML = '<div class="alert alert-error">Failed to load report</div>';
        });
}

function downloadReport() {
    if (currentReportId) {
        window.location.href = `/report/${currentReportId}/download`;
    }
}
</script>
@endpush
@endsection