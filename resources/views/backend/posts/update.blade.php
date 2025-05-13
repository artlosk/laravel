@extends('layouts.admin')

@section('content_header')
    <h1>Edit Post</h1>
@endsection

@section('admin_content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Post</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="create-post" action="{{ route('backend.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" class="form-control"
                           value="{{ old('title', $post->title) }}" required>
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea name="content" id="content" class="form-control" rows="5"
                              required>{{ old('content', $post->content) }}</textarea>
                </div>

                <!-- Filepond для загрузки нового изображения -->
                <div class="form-group">
                    <label for="filepond">Изображение</label>
                    <input type="file" class="filepond" name="filepond[]" multiple>
                </div>
                <!-- Вывод всех изображений -->
                @if ($post->getMedia('images')->isNotEmpty())
                    <div>
                        <h3>Изображения:</h3>
                        <div class="row">
                            @foreach($post->getMedia('images') as $media)
                                <div class="media-item" id="media-{{ $media->id }}">
                                    <a href="{{ $media->getUrl() }}" target="_blank">
                                        <img src="{{ $media->getUrl() }}" alt="Post Image" class="img-thumbnail" width="150">
                                    </a>
                                    <!-- Кнопка удаления изображения -->
                                    <button class="btn btn-danger btn-sm remove-media" data-media-id="{{ $media->id }}" data-post-id="{{ $post->id }}">
                                        Удалить
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p>Изображения не загружены.</p>
                @endif
                <button type="submit" class="btn btn-primary">Update Post</button>
                <a href="{{ route('backend.posts.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
@section('css')
<!-- Подключаем Filepond CSS -->
<link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
@endsection
@section('js')
    <!-- Подключение Filepond JS -->
    <script src="https://cdn.jsdelivr.net/npm/filepond/dist/filepond.min.js"></script>

    <script>
        FilePond.registerPlugin();

        FilePond.setOptions({
            allowMultiple: true,
            acceptedFileTypes: ['image/*'],  // Разрешаем только изображения
            server: {
                process: {
                    url: '{{ route("admin.upload-media") }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    onload: (res) => {
                        const { id } = JSON.parse(res);
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'media_ids[]';
                        input.value = id;
                        document.querySelector('.create-post').appendChild(input);
                        return id;
                    },
                    onerror: (res) => {
                        console.error('Ошибка загрузки', res);
                    },
                },
                revert: null, // если хочешь включить удаление
            },
        });

        FilePond.create(document.querySelector('input.filepond'));
        document.addEventListener('DOMContentLoaded', function () {
            // Обработчик события для кнопки удаления
            document.querySelectorAll('.remove-media').forEach(function (button) {
                button.addEventListener('click', function (e) {
                    const mediaId = e.target.getAttribute('data-media-id');
                    const postId = e.target.getAttribute('data-post-id');

                    // Подтверждение удаления
                    if (confirm('Вы уверены, что хотите удалить это изображение?')) {
                        // Отправка AJAX-запроса
                        fetch(`/posts/${postId}/remove-media/${mediaId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Удаляем элемент из DOM
                                    document.getElementById('media-' + mediaId).remove();
                                } else {
                                    alert('Произошла ошибка при удалении изображения.');
                                }
                            })
                            .catch(error => {
                                console.error('Ошибка:', error);
                                alert('Произошла ошибка при удалении изображения.');
                            });
                    }
                });
            });
        });

    </script>
@endsection
