<?php

namespace App\Jobs;

use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\HttpClient\Psr18Client;
use ToshY\BunnyNet\Client\BunnyClient;
use ToshY\BunnyNet\StreamAPI;
use Illuminate\Support\Facades\Log;

class UploadVideoToBunny implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $video;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    public function handle()
    {
        try {
            // Step 1: Initialize Bunny Stream client
            $psrClient = new Psr18Client();
            $bunnyClient = new BunnyClient(client: $psrClient);
            $stream = new StreamAPI(apiKey: config('services.bunny_stream.api_key'), client: $bunnyClient);
            Log::info("Bunny Stream client initialized successfully.");
            $response = $stream->createVideo(
                config('services.bunny_stream.library_id'),
                ['title' => $this->video->title]
            );

            $data = $response->getContents();
            $videoId = $data['guid'];
            Log::info("Video created in Bunny Stream", $data);

            $filePath = storage_path('app/' . $this->video->original_path);

            // Step 3: Upload the video file
            $stream->uploadVideo(
                config('services.bunny_stream.library_id'),
                $videoId,
                fopen($filePath, 'r')
            );
            Log::info("Video file uploaded to Bunny Stream: {$filePath}");

            // Step 4: Mark video as completed
            $this->video->update([
                'path' => $videoId,
                'status' => 'completed',
                'upload_target' => 'bunny',
            ]);

            Log::info("Video uploaded to Bunny Stream: {$videoId}");

        } catch (\Throwable $e) {
            Log::error("Upload to Bunny Stream failed: {$e->getMessage()}");
            $this->video->update(['status' => 'failed']);
        }
    }
}
