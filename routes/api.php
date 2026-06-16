<?php

use App\Http\Controllers\Api\SitePostsController;
use Illuminate\Support\Facades\Route;

Route::get('/sites/{site:slug}/posts', [SitePostsController::class, 'index']);
