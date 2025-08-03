<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            $files = $request->file('filepond') ?? $request->file('media.filepond');
            
            if (empty($files)) {
                return response()->json(['error' => 'No files uploaded'], 422);
            }

            $validationRules = [];
            if ($request->hasFile('filepond')) {
                $validationRules['filepond.*'] = 'required|file|max:10240|mimes:jpg,jpeg,png';
            } elseif ($request->hasFile('media.filepond')) {
                $validationRules['media.filepond.*'] = 'required|file|max:10240|mimes:jpg,jpeg,png';
            }
            
            if (!empty($validationRules)) {
                $request->validate($validationRules);
            }

            $file = $files[0];
            $path = $file->store('filepond-tmp', 'public');

            return $path;
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to upload file'], 500);
        }
    }

    public function deleteFilepond(Request $request)
    {
        $path = $request->getContent();

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            return response('', 200);
        } catch (\Exception $e) {
            return Response::json(['error' => 'Ошибка при удалении временного файла: ' . $e->getMessage()], 500);
        }
    }

    public function deleteMedia(Media $media)
    {
        try {
            $media->delete();
            return Response::json(['message' => 'Медиафайл успешно удален.'], 200);
        } catch (\Exception $e) {
            return Response::json(['error' => 'Ошибка при удалении медиафайла: ' . $e->getMessage()], 500);
        }
    }
}
