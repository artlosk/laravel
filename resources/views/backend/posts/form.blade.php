<!-- resources/views/posts/form.blade.php -->
@extends('layouts.admin')

@section('title', isset($post) ? 'Редактировать пост' : 'Создать пост')

@section('content_header')
    <h1>{{ isset($post) ? 'Редактировать пост' : 'Создать пост' }}</h1>
@stop

@section('admin_content')
    <div class="card">
        <div class="card-body">
            <form id="postForm" action="{{ isset($post) ? route('backend.posts.update', $post) : route('backend.posts.create') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($post))
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label for="title">Заголовок</label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $post->title ?? '') }}" required>
                    @error('title')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="content">Содержимое</label>
                    <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" rows="5" required>{{ old('content', $post->content ?? '') }}</textarea>
                    @error('content')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="filepond">Загрузить изображения (или перетащите сюда):</label>
                    <input type="file" class="filepond" multiple data-max-files="5">
                    <div id="filepond-errors" class="text-danger mt-2"></div>
                </div>

                <input type="hidden" name="selected_media_ids" id="selectedMediaIds" value="{{ old('selected_media_ids', isset($postMedia) ? $postMedia->pluck('id')->join(',') : '') }}">
                <input type="hidden" name="media_order" id="mediaOrder" value="{{ old('media_order', isset($postMedia) ? $postMedia->pluck('id')->join(',') : '') }}">

                <button type="button" class="btn btn-secondary mb-3" data-toggle="modal" data-target="#mediaLibraryModal">
                    Выбрать из галереи
                </button>

                <div id="selectedMediaPreview" class="mb-3 row"></div>

                <button type="submit" class="btn btn-primary">
                    {{ isset($post) ? 'Обновить' : 'Создать' }}
                </button>
            </form>
        </div>
    </div>

    @include('backend.partials.media_library_modal')
@stop
