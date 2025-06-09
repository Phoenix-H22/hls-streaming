<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Bunny Stream Player</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
        body { background: #f8f9fa; }
        .container { padding-top: 60px; }
        iframe { width: 100%; height: 500px; border: none; border-radius: 10px; }
	</style>
</head>
<body>
<div class="container">
	<a href="{{ route('home') }}" class="btn btn-secondary mb-4">← Back</a>
	<div class="card shadow-sm p-3">
		<h2 class="text-center">🎬 Bunny Stream Playback</h2>
		<p class="text-center text-muted">This video is embedded securely using a signed iframe token.</p>
		<iframe src="{{ $videoUrl }}" allowfullscreen></iframe>
	</div>
</div>
</body>
</html>
