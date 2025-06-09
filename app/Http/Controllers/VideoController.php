<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideoToHLS;
use App\Jobs\UploadVideoToBunny;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::latest()->get();

        $videosWithLinks = $videos->map(function ($video) {
            $video->signed_url = URL::signedRoute('videos.watch', ['video' => $video->id], now()->addMinutes(30));
            return $video;
        });

        return view('welcome', compact('videosWithLinks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'video' => 'required|file',
            'chunkNumber' => 'required|integer',
            'totalChunks' => 'required|integer',
            'title' => 'required|string',
        ]);

        $filename = md5($request->title) . '.mp4';
        $tempDir = storage_path('app/temp_chunks/' . $filename);

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $chunkPath = $tempDir . "/chunk_" . $request->chunkNumber;
        file_put_contents($chunkPath, $request->file('video')->get());

        // ✅ Check if all chunks exist
        $allChunksPresent = true;
        for ($i = 1; $i <= $request->totalChunks; $i++) {
            if (!file_exists($tempDir . "/chunk_$i")) {
                $allChunksPresent = false;
                break;
            }
        }

        if ($allChunksPresent) {
            $finalPath = "videos/original/" . $filename;
            $fullFinalPath = storage_path('app/' . $finalPath);

            // Safeguard: remove if already exists
            if (file_exists($fullFinalPath)) {
                unlink($fullFinalPath);
            }

            $handle = fopen($fullFinalPath, 'ab');
            for ($i = 1; $i <= $request->totalChunks; $i++) {
                $chunkFile = "$tempDir/chunk_$i";
                fwrite($handle, file_get_contents($chunkFile));
                unlink($chunkFile);
            }
            fclose($handle);
            rmdir($tempDir);

            if ($request->upload_target === 'bunny') {
                // Dispatch BunnyStream upload job
                $video = Video::create([
                    'title' => $request->title,
                    'path' => null,
                    'original_path' => $finalPath,
                    'status' => 'uploaded',
                    'upload_target' => 'bunny',
                ]);

                UploadVideoToBunny::dispatch($video);
            } else {
                // Continue with FFmpeg job
                $video = Video::create([
                    'title' => $request->title,
                    'path' => null,
                    'original_path' => $finalPath,
                    'status' => 'uploaded',
                    'upload_target' => 'local',
                ]);

                ProcessVideoToHLS::dispatch($video);
            }

        }

        return response()->json(['status' => true]);
    }


    public function watch(Video $video)
    {
        if ($video->status !== 'completed') {
            return redirect()->route('home')->with('error', 'Video is not ready yet.');
        }
        if ($video->upload_target === 'bunny') {
            $videoUrl = $this->generateBunnyIframeEmbedUrl($video->path);
            return view('watch_bunny', compact('videoUrl'));
        } else {
            $videoUrl = asset('storage/hls/' . $video->id . '/playlist.m3u8');
            return view('watch', compact('videoUrl'));
        }
    }
    protected function generateBunnySignedUrl($videoGuid)
    {
        $baseUrl = config('services.bunny_stream.playback_url');
        $libraryId = config('services.bunny_stream.library_id');
        $tokenKey = config('services.bunny_stream.token_key');
        $expires = time() + 600; // valid for 10 mins

        $videoPath = "/$videoGuid/playlist.m3u8";
        $hash = hash_hmac('sha256', "$videoPath$expires", $tokenKey);

        return "$baseUrl$videoPath?token=$hash&expires=$expires";
    }
    protected function generateBunnyIframeEmbedUrl($videoGuid)
    {
        $libraryId = config('services.bunny_stream.library_id');
        $tokenKey  = config('services.bunny_stream.token_key');
        $expires   = time() + 600;

        $stringToHash = $tokenKey . $videoGuid . $expires;
        $token = hash('sha256', $stringToHash);

        return "https://iframe.mediadelivery.net/embed/{$libraryId}/{$videoGuid}?token={$token}&expires={$expires}";
    }




    public function destroy(Video $video)
    {
        // Delete original mp4
        if ($video->original_path && Storage::exists($video->original_path)) {
            Storage::delete($video->original_path);
        }

        // Delete HLS folder
        $hlsDir = 'public/hls/' . $video->id;
        if (Storage::exists($hlsDir)) {
            Storage::deleteDirectory($hlsDir);
        }

        // Delete DB record
        $video->delete();

        return redirect()->route('home')->with('success', 'Video deleted successfully.');
    }

}
