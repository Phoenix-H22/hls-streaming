# Video Upload & Streaming (HLS + Bunny CDN)

This project demonstrates a secure and scalable video upload and playback system built for LMS platforms. It supports both local HLS streaming and Bunny CDN integration with token-based video protection.

## 🔧 Features

- Upload `.mp4` videos via chunked upload
- Convert videos to HLS format (`.m3u8 + .ts`) using FFmpeg
- Store videos in structured folders by course and lesson
- Stream videos using Video.js player
- Tokenized playback using signed URLs (Laravel route signing)
- Bunny CDN Stream integration as an alternative video host
- Background processing with Laravel Queues and Jobs
- Admin panel preview with upload status (pending, processing, ready, failed)

## 🖥️ Demo

Live demo of the implementation:  
**https://hls.phoenixtechs.net/**

## ✅ How It Works

1. **Upload:**
   - Client uploads video in chunks.
   - Laravel stores and reassembles the full file.

2. **Processing:**
   - Once all chunks are received:
     - If upload target is `local`, dispatches a job to convert video via FFmpeg.
     - If upload target is `bunny`, dispatches a job to upload to Bunny Stream.

3. **Playback:**
   - Videos are streamed via:
     - Local HLS (with signed route)
     - Bunny CDN (with secure iframe token)

4. **Security:**
   - All video URLs are signed and expire after a short time.
   - Prevents public downloading or unauthorized access.

## 🧰 Tech Stack

- **Backend:** Laravel 10+
- **Queues:** Laravel Jobs + Redis + Supervisor
- **Video Conversion:** FFmpeg
- **Frontend:** Blade + Bootstrap 5
- **Video Player:** Video.js
- **External CDN:** Bunny Stream
- **Auth:** Laravel route signing (for token expiration)
- **Hosting:** Ubuntu VPS (NGINX, PHP-FPM)

## 📁 Folder Structure

```

storage/
├── app/
│   └── videos/
│       └── original/
│           └── course\_slug/
│               └── lesson\_slug.mp4
public/
└── storage/
└── hls/
└── video\_id/
├── playlist.m3u8
├── segment0.ts
└── ...

````

## 📦 Environment Variables

Add the following to your `.env`:

```env
BUNNY_STORAGE_ZONE="your-storage-zone"
BUNNY_API_KEY="your-bunny-storage-api-key"
BUNNY_REGION=ny
BUNNY_PULL_ZONE_URL="https://yourzone.b-cdn.net"

BUNNY_STREAM_API_KEY="your-bunny-stream-api-key"
BUNNY_STREAM_LIBRARY_ID=123456
BUNNY_STREAM_SIGNING_KEY="your-stream-signing-key"
BUNNY_STREAM_PLAYBACK_URL="https://vz-xxxxx.b-cdn.net"
````

## 🧪 Test Accounts / Access

No authentication is required for testing. Just upload a video from the UI or test via `POST /upload`.

## 📬 Contact

Developed by **Abdalrhman Alkady**

* Email: [alkady2019@gmail.com](mailto:alkady2019@gmail.com)
