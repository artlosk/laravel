<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $mediaItems = Media::latest()->paginate(20);
        return view('backend.media.index', compact('mediaItems'))->render();
    }

    public function getByIds(Request $request)
    {
        $ids = $request->input('ids', []);
        $validIds = array_filter(array_map('intval', (array) $ids), function($id) {
            return $id > 0;
        });

        if (empty($validIds)) {
            return response()->json([]);
        }

        $mediaItems = Media::whereIn('id', $validIds)->get()->map(function($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'url' => $media->getUrl(),
                'url_thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : null,
                'mime_type' => $media->mime_type,
            ];
        });

        return response()->json($mediaItems);
    }

    public function uploadFilepond(Request $request)
    {
        try {
            Log::info('Upload request: ', $request->all());

            $request->validate([
                'filepond.*' => 'required|file|max:10240|mimes:jpg,jpeg,png',
            ]);

            $files = $request->file('filepond');
            if (empty($files)) {
                Log::error('No files uploaded');
                return response()->json(['error' => 'No files uploaded'], 422);
            }

            $file = $files[0];
            $path = $file->store('filepond-tmp', 'public');

            Log::info('File stored: ', ['path' => $path]);

            return $path;
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ', $e->errors());
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Upload error: ', [$e->getMessage()]);
            return response()->json(['error' => 'Failed to upload file'], 500);
        }
    }

    public function deleteFilepond(Request $request)
    {
        $path = $request->getContent();
        Log::info('Received deleteFilepond request for path: ' . $path);

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info('Deleted temporary file: ' . $path);
            } else {
                Log::warning('Attempted to delete non-existent temporary file: ' . $path);
            }
            return response('', 200);
        } catch (\Exception $e) {
            Log::error('FilePond Delete Error: ' . $e->getMessage());
            return Response::json(['error' => 'Ошибка при удалении временного файла: ' . $e->getMessage()], 500);
        }
    }

    public function deleteMedia(Media $media)
    {
        Log::info('Attempting to delete media item completely.', ['media_id' => $media->id, 'file_name' => $media->file_name]);
        try {
            $media->delete();
            Log::info('Media item deleted successfully.', ['media_id' => $media->id]);
            return Response::json(['message' => 'Медиафайл успешно удален.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting media item.', ['media_id' => $media->id, 'error' => $e->getMessage()]);
            return Response::json(['error' => 'Ошибка при удалении медиафайла: ' . $e->getMessage()], 500);
        }
    }
}
