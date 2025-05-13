@extends('layouts.admin')

@section('content_header')
    <h1>Create Post</h1>
@endsection

@section('admin_content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">New Post</h3>
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

            <form action="{{ route('backend.posts.create') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required>
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea name="content" id="content" class="form-control" rows="5" required>{{ old('content') }}</textarea>
                </div>
                <!-- Filepond для загрузки изображения -->
                <div class="form-group">
                    <label for="filepond">Изображение</label>
                    <input type="file" class="filepond" name="filepond[]" multiple>
                </div>
                <div id="media_ids"></div>
                <button type="submit" class="btn btn-primary">Create Post</button>
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
                        document.querySelector('form').appendChild(input);
                        return id;
                    },
                    onerror: (res) => {
                        console.error('Ошибка загрузки', res);
                    },
                },
            }
        });

        FilePond.create(document.querySelector('input.filepond'));
    </script>
@endsection
