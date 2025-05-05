<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostController extends Controller
{
// Просмотр списка (требует permission:read-posts)
    public function index(Request $request)
    {
        $this->authorize('read-posts');
        if ($request->has('id')) {
            $post = Post::findOrFail($request->id);
            return response()->json($post);
        }

        return response()->json(Post::all());
    }

    // Просмотр конкретного поста (требует permission:read-posts)
    public function show($id)
    {
        $post = Post::findOrFail($id);
        $this->authorize('read-posts');
        return response()->json($post);
    }

    // Создание поста (требует permission:create-posts)
    public function store(Request $request)
    {
        $this->authorize('create-posts');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post = Post::create($validated);
        return response()->json($post, Response::HTTP_CREATED);
    }

    // Редактирование поста (требует permission:edit-posts)
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $this->authorize('edit-posts', $post);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        $post->update($validated);
        return response()->json($post);
    }

    // Удаление поста (требует permission:delete-posts)
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $this->authorize('delete-posts', $post);

        $post->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
