<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HLS Video Upload</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #progressWrapper {
            display: none;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body class="bg-light">

<div class="container py-5">
    <h1 class="mb-4 text-center">🎥 Upload Video for HLS Conversion</h1>

    <div id="progressWrapper" class="mb-4">
        <label class="form-label">Upload Progress</label>
        <div class="progress">
            <div id="uploadProgress" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
        </div>
    </div>

    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <form id="uploadForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Video Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload Destination</label>
                    <select name="upload_target" class="form-select" required>
                        <option value="server">Local Server (HLS with FFmpeg)</option>
                        <option value="bunny">Bunny Stream</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Video File (.mp4)</label>
                    <input type="file" name="video" class="form-control" accept="video/mp4" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Upload & Convert</button>
            </form>
        </div>
    </div>

    <h2 class="mb-3">📂 Uploaded Videos</h2>

    @if($videosWithLinks->isEmpty())
        <p class="text-muted">No videos uploaded yet.</p>
    @else
        <div class="list-group">
            @foreach ($videosWithLinks as $video)
                <div class="list-group-item d-flex justify-content-between">
                    <strong>{{ $video->title }}</strong>
                    <span class="text-muted">{{ $video->created_at->diffForHumans() }}</span>
                    @switch ($video->status)
                        @case('uploaded')
                            <span class="badge bg-info text-dark">Uploaded</span>
                            @break
                        @case('processing')
                            <span class="badge bg-warning text-dark">Processing</span>
                            @break
                        @case('completed')
                            <span class="badge bg-success">Completed</span>
                            @break
                        @case('failed')
                            <span class="badge bg-danger">Failed</span>
                            @break
                        @default
                            <span class="badge bg-secondary">Unknown</span>
                            @break
                    @endswitch
                    <div>
                        <a href="{{ $video->signed_url }}" class="btn btn-sm btn-outline-success">▶ Watch</a>
                        <form action="{{ route('videos.destroy', $video) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this video?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">🗑 Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    document.getElementById('uploadForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = e.target;
        const file = form.video.files[0];
        const title = form.title.value;
        const chunkSize = 5 * 1024 * 1024;
        const totalChunks = Math.ceil(file.size / chunkSize);
        let currentChunk = 0;

        document.getElementById('progressWrapper').style.display = 'block';

        while (currentChunk < totalChunks) {
            const formData = new FormData();
            const start = currentChunk * chunkSize;
            const end = Math.min(start + chunkSize, file.size);
            const blob = file.slice(start, end);

            formData.append('video', blob);
            formData.append('title', title);
            formData.append('chunkNumber', currentChunk + 1);
            formData.append('totalChunks', totalChunks);
            formData.append('upload_target', form.upload_target.value);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            await fetch('{{ route('videos.upload') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest' // ADD THIS LINE
                },
                body: formData,
                credentials: 'same-origin' // ✅ VERY IMPORTANT
            });

            currentChunk++;
            const progress = Math.floor((currentChunk / totalChunks) * 100);
            const bar = document.getElementById('uploadProgress');
            bar.style.width = progress + '%';
            bar.innerText = progress + '%';
        }

        window.location.reload();
    });
</script>
</body>
</html>
