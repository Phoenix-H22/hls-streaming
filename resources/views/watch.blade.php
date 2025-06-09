<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Watch Video</title>

	{{-- Bootstrap 5 --}}
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

	{{-- Video.js --}}
	<link href="https://vjs.zencdn.net/7.21.1/video-js.css" rel="stylesheet" />

	<style>
        body {
            background-color: #f8f9fa;
        }

        .card-style {
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .video-js {
            border-radius: 0.5rem;
        }

        .btn-back {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 10;
            font-size: 1rem;
            padding: 0.6rem 1rem;
        }

        h2 {
            font-size: 1.5rem;
        }

        p {
            font-size: 1rem;
        }

        @media (max-width: 576px) {
            .btn-back {
                font-size: 1.1rem;
                padding: 0.7rem 1.2rem;
            }

            h2 {
                font-size: 1.3rem;
            }

            p {
                font-size: 0.95rem;
            }
        }
	</style>
</head>
<body>

<div class="container py-5">
	<a href="{{ route('home') }}" class="btn btn-secondary btn-back">← Back</a>

	<div class="row justify-content-center mt-5">
		<div class="col-md-10 col-lg-8">
			<div class="card card-style p-4 bg-white">
				<h2 class="text-center mb-4 fw-bold text-primary">🎬 Video Playback</h2>
				<p class="text-center text-muted mb-4">This video is streamed securely via HLS and protected by signed URLs.</p>

				<video
						id="videoPlayer"
						class="video-js vjs-default-skin vjs-fluid"
						controls
						preload="auto"
						data-setup='{"fluid": true}'
				>
					<source src="{{ $videoUrl }}" type="application/x-mpegURL">
				</video>
			</div>
		</div>
	</div>
</div>

{{-- Video.js --}}
<script src="https://vjs.zencdn.net/7.21.1/video.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        videojs('videoPlayer');
    });
</script>

</body>
</html>
