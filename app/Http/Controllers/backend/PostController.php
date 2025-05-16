<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'filepond' => 'nullable|array',
                'filepond.*' => 'nullable|string',
                'selected_media_ids' => 'nullable|string',
                'media_order' => 'nullable|string',
            ]);

            DB::beginTransaction();

            try {
                $post = new Post();
                $post->savePost($validatedData);

                DB::commit();
                Log::info('Controller: Post created and media synced successfully.');

                return redirect()->route('backend.posts.index')->with('success', 'Post created successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Controller: Database transaction rolled back during post creation: ' . $e->getMessage());

                return redirect()->back()->withInput()->withErrors(['media_error' => 'Ошибка при создании поста и привязке медиа: ' . $e->getMessage()]);
            }
        }

        $this->authorize('create-posts');
        Log::info('Controller: Rendering create form (GET). Post related media collection:', ['count' => 0, 'ids' => []]);
        return view('backend.posts.form');
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
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'filepond' => 'nullable|array',
                'filepond.*' => 'nullable|string',
                'selected_media_ids' => 'nullable|string',
                'media_order' => 'nullable|string',
            ]);

            DB::beginTransaction();

            try {
                $post->savePost($validatedData);

                DB::commit();
                Log::info('Controller: Post updated and media synced successfully.');

                return redirect()->route('backend.posts.update', $post)->with('success', 'Post updated successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Controller: Database transaction rolled back during post update: ' . $e->getMessage());

                return redirect()->back()->withInput()->withErrors(['media_error' => 'Ошибка при обновлении поста и привязке медиа: ' . $e->getMessage()]);
            }
        }

        $this->authorize('edit-posts');
        $postMedia = $post->relatedMedia;

        $postMedia = $postMedia->sortBy('pivot.order_column')->values();
        Log::info('Controller: Post related media collection explicitly sorted by pivot.order_column before view:', ['count' => $postMedia->count(), 'ids' => $postMedia->pluck('id')->toArray()]);

        foreach ($postMedia as $mediaItem) {
            Log::info('Controller: Post Related Media Item Details before view:', [
                'id' => $mediaItem->id ?? 'N/A',
                'type' => get_class($mediaItem),
                'is_media_object' => $mediaItem instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media,
                'order_column_from_pivot' => $mediaItem->pivot->order_column ?? null,
            ]);
        }
        return view('backend.posts.form', compact('post', 'postMedia'));
    }

    public function delete(Post $post)
    {
        $this->authorize('delete-posts');
        $post->delete();
        return redirect()->route('backend.posts.index')->with('success', 'Post deleted successfully.');
    }
}
