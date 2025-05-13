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
            @if ($post->getMedia('images')->isNotEmpty())
                <div>
                    <h3>Изображения:</h3>
                    <div class="row">
                        @foreach ($post->getMedia('images') as $media)
                            <div class="col-md-3">
                                <a href="{{ $media->getUrl() }}" target="_blank">
                                    <img src="{{ $media->getUrl() }}" alt="Image" class="img-fluid" style="max-width: 300px;">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p>Изображения не загружены.</p>
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
