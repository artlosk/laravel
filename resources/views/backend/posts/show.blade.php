@extends('layouts.admin')

@section('content_header')
    <h1>Post Details</h1>
@endsection

@section('admin_content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $post->title }}</h3>
        </div>
        <div class="card-body">
            <p><strong>Author:</strong> {{ $post->user->name }}</p>
            <p><strong>Content:</strong></p>
            <p>{{ $post->content }}</p>
            <!-- Вывод всех изображений -->
            @if($post->relatedMedia->count() > 0) {{-- ИЗМЕНЕНО: используем связь relatedMedia --}}
            <h5>Изображения:</h5>
            <div class="row">
                @foreach($post->relatedMedia as $media) {{-- ИЗМЕНЕНО: используем связь relatedMedia --}}
                <div class="col-md-4 mb-4">
                    {{-- Используем миниатюру, если она есть, иначе оригинальный URL --}}
                    <img src="{{ $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl() }}" class="img-fluid rounded" alt="{{ $media->name }}">
                    {{-- Опционально: ссылка на оригинальное изображение --}}
                    {{-- <a href="{{ $media->getUrl() }}" target="_blank">Оригинал</a> --}}
                </div>
                @endforeach
            </div>
            @endif
            <p><strong>Created:</strong> {{ $post->created_at->format('d M Y H:i') }}</p>
            <p><strong>Updated:</strong> {{ $post->updated_at->format('d M Y H:i') }}</p>
            <div class="mt-3">
                @if (Auth::user()->hasRole('admin') || Auth::user()->id === $post->user_id)
                    <a href="{{ route('backend.posts.update', $post) }}" class="btn btn-warning">Edit</a>
                    <form action="{{ route('backend.posts.delete', $post) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
                    </form>
                @endif
                <a href="{{ route('backend.posts.index') }}" class="btn btn-secondary">Back to Posts</a>
            </div>
        </div>
    </div>
@endsection
