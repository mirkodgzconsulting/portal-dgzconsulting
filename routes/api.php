<?php

use App\Http\Controllers\Api\SiteCategoriesController;
use App\Http\Controllers\Api\SitePortfolioController;
use App\Http\Controllers\Api\SitePostsController;
use Illuminate\Support\Facades\Route;

Route::get('/sites/{site:slug}/posts', [SitePostsController::class, 'index']);
Route::get('/sites/{site:slug}/categories', [SiteCategoriesController::class, 'index']);
Route::get('/sites/{site:slug}/portfolio', [SitePortfolioController::class, 'index']);
