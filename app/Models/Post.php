<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'content',
    ];

    public function relatedMedia(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'entity', 'media_relation_entity')
            ->withPivot('order_column', 'created_at', 'updated_at')
            ->orderBy('order_column');
    }

    public function savePost(array $data): void
    {
        $this->fill($data);
        $this->save();

        $this->syncMedia($data);
    }

    protected function syncMedia(array $data): void
    {
        $filepondFiles = $data['filepond'] ?? [];

        $validFilepondFiles = array_filter($filepondFiles, function($path) {
            return $path !== null && $path !== '';
        });

        $newMediaIds = [];
        $filepondIdMap = [];

        foreach ($validFilepondFiles as $filepondPath) {
            if (Storage::disk('public')->exists($filepondPath)) {
                try {
                    $mediaItem = $this->addMediaFromDisk($filepondPath, 'public')
                        ->toMediaCollection('images');
                    $newMediaIds[] = $mediaItem->id;
                    $filepondIdMap[$filepondPath] = $mediaItem->id;
                } catch (\Exception $e) {
                }
            }
        }

        $receivedMediaOrderString = $data['media_order'] ?? '';
        $receivedMediaOrder = array_map('trim', explode(',', $receivedMediaOrderString));
        $receivedMediaOrder = array_filter($receivedMediaOrder);

        $syncData = [];
        $order = 1;
        $processedMediaIds = [];

        foreach ($receivedMediaOrder as $mediaIdentifier) {
            $mediaId = null;
            if (is_numeric($mediaIdentifier)) {
                $mediaId = (int) $mediaIdentifier;
            } elseif (is_string($mediaIdentifier) && Str::startsWith($mediaIdentifier, 'filepond-tmp/')) {
                if (isset($filepondIdMap[$mediaIdentifier])) {
                    $mediaId = $filepondIdMap[$mediaIdentifier];
                } else {
                    continue;
                }
            } else {
                continue;
            }

            if ($mediaId !== null) {
                if (!in_array($mediaId, $processedMediaIds)) {
                    $syncData[$mediaId] = ['order_column' => $order];
                    $processedMediaIds[] = $mediaId;
                    $order++;
                }
            }
        }

        try {
            $this->relatedMedia()->sync($syncData);
        } catch (\Exception $e) {
            throw new \Exception('Failed to sync media relationships: ' . $e->getMessage());
        }

        $this->unsetRelation('relatedMedia');
        $this->load(['relatedMedia' => function($query) {
            $query->withPivot('order_column');
        }]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public')
            ->useFallbackUrl('/images/placeholder.jpg')
            ->useFallbackPath(public_path('/images/placeholder.jpg'));
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
