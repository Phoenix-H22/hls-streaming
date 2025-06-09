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
        $ffmpegPath = '/usr/bin/ffmpeg';
        $ffmpeg = "\"{$ffmpegPath}\"";

        $source = escapeshellarg(storage_path('app/' . $this->video->path));
        $outputDir = storage_path("app/public/hls/{$this->video->id}");
        File::makeDirectory($outputDir, 0777, true, true);

// Use forward slashes
        $segments = '"' . str_replace('\\', '/', $outputDir) . '/file%03d.ts"';
        $playlist = '"' . str_replace('\\', '/', $outputDir) . '/playlist.m3u8"';

        $cmd = "{$ffmpeg} -i {$source} -preset veryfast -g 48 -sc_threshold 0 -map 0:0 -map 0:1 "
            . "-c:v libx264 -b:v 800k -c:a aac -b:a 128k -f hls -hls_time 10 -hls_playlist_type vod "
            . "-hls_segment_filename {$segments} {$playlist} 2>&1";

        exec($cmd, $output, $status);

        Log::info('FFmpeg command executed: ' . $cmd);
        Log::info('FFmpeg output: ' . implode("\n", $output));
        Log::info('FFmpeg status: ' . $status);


        $this->video->update([
            'path' => 'storage/hls/' . $this->video->id . '/playlist.m3u8'
        ]);
    }

}
