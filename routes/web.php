<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgoraController;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
//    Route::get('/video-call', fn () => view('call'))->name('video-call');
    Route::get('/video-call', [AgoraController::class, 'join'])->name('video-call');
    Route::get('/agora/token', [AgoraController::class, 'generateToken']);
    Route::post('/send-message', [MessageController::class, 'sendMessage'])->name('send-message');
});
