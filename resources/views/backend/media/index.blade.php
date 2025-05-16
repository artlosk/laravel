<!-- resources/views/backend/media/index.blade.php -->
@forelse($mediaItems as $media)
    <div class="col-md-2 mb-4 media-item" data-media-id="{{ $media->id }}">
        <div class="card h-100">
            <div class="card-body p-2 text-center">
                @if($media->mime_type && Str::startsWith($media->mime_type, 'image/'))
                    <img src="{{ $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl() }}" class="img-fluid rounded" alt="{{ $media->name }}">
                @else
                    <i class="fas fa-file fa-4x text-muted"></i>
                @endif
            </div>
            <div class="card-footer p-1 text-center">
                <small class="d-block text-truncate" title="{{ $media->file_name }}">{{ $media->file_name }}</small>
                <button type="button" class="btn btn-danger btn-sm delete-media-btn" data-media-id="{{ $media->id }}">
                    <i class="fas fa-trash"></i> Удалить
                </button>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <p class="text-center">Медиафайлы не найдены.</p>
    </div>
@endforelse

<div class="col-12">
    {{ $mediaItems->links() }}
</div>
