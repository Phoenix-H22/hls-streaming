<?php

use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [VideoController::class, 'index'])->name('home');
Route::post('/upload', [VideoController::class, 'store'])->name('videos.upload');
Route::get('/watch/{video}', [VideoController::class, 'watch'])
    ->name('videos.watch')
    ->middleware('signed');
Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
Route::post('/videos/upload-chunk', [VideoController::class, 'uploadChunk'])->name('videos.upload.chunk');
