<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $this->authorize('read-posts');
        $posts = Post::latest()->get();
        return view('frontend.posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        $this->authorize('read-posts');
        return view('frontend.posts.show', compact('post'));
    }
}
