<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\TempUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

            // Создаем пост
            $post = Post::create([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'user_id' => Auth::id(),
            ]);

            // Привязываем медиа, если они есть
            if ($request->has('media_ids')) {
                foreach ($request->input('media_ids') as $mediaId) {
                    $tempUpload = TempUpload::find($mediaId);

                    if ($tempUpload) {
                        // Сохраняем медиа в основную коллекцию
                        $media = Media::create([
                            'model_type' => Post::class,
                            'collection_name' => 'images',
                            'file_name' => $tempUpload->file_name,
                            'mime_type' => $tempUpload->mime_type,
                            'size' => $tempUpload->size,
                        ]);

                        // Привязываем медиа файл к посту
                        $media->addMediaFromDisk('public', 'temp/' . $tempUpload->file_name)
                            ->preservingOriginal()
                            ->toMediaCollection('images');

                        // Удаляем временную запись
                        $tempUpload->delete();
                    }
                }
            }

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
                //'filepond.*' => 'nullable|image|max:5120',  // Валидация для нескольких файлов
            ]);
            $post->update([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
            ]);
            // Проверка на наличие файлов и их добавление
            if ($request->has('media_ids')) {
                foreach ($request->input('media_ids') as $mediaId) {
                    $tempUpload = TempUpload::find($mediaId);

                    if ($tempUpload &&Storage::disk('public')->exists($tempUpload->file_path)) {
                        // Добавляем медиа к посту
                        $post->addMedia(storage_path('app/public/' . $tempUpload->file_path))
                            ->toMediaCollection('images');
                    }
                }
            }
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

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'filepond.*' => 'required|image|max:5120', // Ограничение на размер изображения
        ]);

        $filePaths = [];

        // Для каждого загружаемого файла
        foreach ($request->file('filepond') as $file) {
            // Сохраняем файл в хранилище
            $path = $file->store('temp/uploads', 'public'); // Папка storage/app/public/temp/uploads

            // Сохраняем информацию о файле в таблице TempUpload
            $tempUpload = TempUpload::create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path, // Путь к файлу в базе данных
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            $filePaths[] = $tempUpload->id; // Собираем все id медиа
        }

        // Возвращаем id файлов для дальнейшего использования
        return response()->json([
            'id' => $filePaths,
        ]);
    }

    public function removeMedia(Post $post, Media $media)
    {
// Проверка, что медиа связано с этим постом
        if ($media->model_id === $post->id && $media->model_type === Post::class) {
            $media->delete(); // Удаляем медиа
            return response()->json(['success' => true]);
        }

        // Ошибка
        return response()->json(['success' => false, 'message' => 'Не удалось удалить изображение.']);
    }
}
