<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $posts_count = \App\Models\Post::where('type', 'post')->where('status', 'publish')->count();
        $pages_count = \App\Models\Post::where('type', 'page')->where('status', 'publish')->count();
        $users_count = \App\Models\User::count();
        $media_count = \App\Models\Media::count();

        return view('admin.dashboard', compact('posts_count', 'pages_count', 'users_count', 'media_count'));
    }
}
