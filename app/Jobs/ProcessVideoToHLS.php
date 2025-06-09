<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProcessVideoToHLS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $video;

    /**
     * Create a new job instance.
     */
    public function __construct($video)
    {
        $this->video = $video;
    }

    /**
     * Execute the job.
     */
    // ProcessVideoToHLS.php

    public function handle()
    {
        $ffmpegPath = 'C:\\Program Files\\ImageMagick-7.1.0-Q16\\ffmpeg.exe';
        $ffmpeg = "\"{$ffmpegPath}\"";

        // Get the raw video path
        $rawPath = $this->video->original_path;

        // Validate path existence
        if (empty($rawPath)) {
            Log::error("Video path is empty for video ID {$this->video->id}");
            return;
        }

        $fullSourcePath = storage_path('app/' . $rawPath);

        if (!File::exists($fullSourcePath)) {
            Log::error("Source file not found: {$fullSourcePath}");
            return;
        }

        $source = escapeshellarg($fullSourcePath);

        // Create HLS output directory
        $outputDir = storage_path("app/public/hls/{$this->video->id}");
        File::makeDirectory($outputDir, 0777, true, true);

        // Prepare FFmpeg output paths
        $segments = '"' . str_replace('\\', '/', $outputDir) . '/file%03d.ts"';
        $playlist = '"' . str_replace('\\', '/', $outputDir) . '/playlist.m3u8"';

        // Construct FFmpeg command
        $cmd = "{$ffmpeg} -threads 1 -i {$source} -preset veryfast -g 48 -sc_threshold 0 -map 0:0 -map 0:1 "
            . "-c:v libx264 -b:v 800k -c:a aac -b:a 128k -f hls -hls_time 10 -hls_playlist_type vod "
            . "-hls_segment_filename {$segments} {$playlist} 2>&1";
        $this->video->update(['status' => 'processing']);
        exec($cmd, $output, $status);

        Log::info('FFmpeg command executed: ' . $cmd);
        Log::info('FFmpeg output: ' . implode("\n", $output));
        Log::info('FFmpeg status: ' . $status);

        if ($status === 0 && File::exists(str_replace('"', '', $playlist))) {
            $this->video->update([
                'path' => 'storage/hls/' . $this->video->id . '/playlist.m3u8',
                'status' => 'completed',
            ]);
            Log::info("HLS conversion complete for video ID {$this->video->id}");
        } else {
            Log::error("HLS conversion failed for video ID {$this->video->id}");
        }
    }


}
