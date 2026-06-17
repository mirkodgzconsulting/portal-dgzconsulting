<?php

use App\Http\Controllers\PostPreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/client-media', [\App\Http\Controllers\Api\ClientMediaController::class, 'index'])
    ->middleware(['web', 'auth:client,client_user'])
    ->name('client.media');

Route::get('/preview/post/{post}', PostPreviewController::class)
    ->middleware(['web', 'auth:web,client,client_user'])
    ->name('post.preview');
