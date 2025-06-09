<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideoToHLS;
use App\Models\Video;
use Illuminate\Http\Request;
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
            'title' => 'required|string',
            'video' => 'required|mimes:mp4|max:1024000',
        ]);

        $filename = uniqid() . '.mp4';

        $originalPath = 'videos/original/' . $filename;
        $request->file('video')->storeAs('videos/original', $filename);

        $video = Video::create([
            'title' => $request->title,
            'path' => null, // path to .m3u8 will be updated later by the job
            'original_path' => $originalPath,
        ]);

        ProcessVideoToHLS::dispatch($video);

        return redirect()->back()->with('success', 'Uploaded & queued for conversion.');
    }

    public function watch(Video $video)
    {
        return view('watch', [
            'videoUrl' => asset('storage/hls/' . $video->id . '/playlist.m3u8'),
        ]);
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
