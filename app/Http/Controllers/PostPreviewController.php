<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostPreviewController extends Controller
{
    public function __invoke(Request $request, Post $post)
    {
        return view('posts.preview', [
            'post' => $post->load(['site', 'category']),
        ]);
    }
}
