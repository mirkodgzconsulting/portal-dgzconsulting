<?php

use App\Http\Controllers\PostPreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/preview/post/{post}', PostPreviewController::class)
    ->middleware(['web', 'auth:web,client,client_user'])
    ->name('post.preview');
