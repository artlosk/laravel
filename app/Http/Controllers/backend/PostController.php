<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $this->authorize('read-posts');
        $posts = Post::all();
        return view('backend.posts.index', compact('posts'));
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->authorize('create-posts');
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);
            Post::create([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'user_id' => Auth::id(),
            ]);
            return redirect()->route('backend.posts.index')->with('success', 'Post created successfully.');
        }
        $this->authorize('create-posts');
        return view('backend.posts.create');
    }

    public function show(Post $post)
    {
        $this->authorize('read-posts');
        return view('backend.posts.show', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        if ($request->isMethod('put')) {
            $this->authorize('edit-posts');
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);
            $post->update([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
            ]);
            return redirect()->route('backend.posts.index')->with('success', 'Post updated successfully.');
        }
        $this->authorize('edit-posts');
        return view('backend.posts.update', compact('post'));
    }

    public function delete(Post $post)
    {
        $this->authorize('delete-posts');
        $post->delete();
        return redirect()->route('backend.posts.index')->with('success', 'Post deleted successfully.');
    }
}
