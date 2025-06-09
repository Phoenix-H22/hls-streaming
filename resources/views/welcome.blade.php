<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HLS Video Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h1 class="mb-4 text-center">🎥 Upload Video for HLS Conversion</h1>

    @if(session('success'))
        <div class="alert alert-success text-center">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <form method="POST" action="{{ route('videos.upload') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Video Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Enter title" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Video File (.mp4)</label>
                    <input type="file" name="video" class="form-control" accept="video/mp4" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Upload & Convert
                </button>
            </form>
        </div>
    </div>

    <h2 class="mb-3">📂 Uploaded Videos</h2>

    @if($videosWithLinks->isEmpty())
        <p class="text-muted">No videos uploaded yet.</p>
    @else
        <ul class="list-group">
            @foreach ($videosWithLinks as $video)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $video->title }}</strong>
                    </div>
                    <a href="{{ $video->signed_url }}" target="_blank" class="btn btn-outline-success btn-sm">
                        ▶ Watch
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>

</body>
</html>
