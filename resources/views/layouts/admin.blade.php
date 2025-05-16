@extends('adminlte::page')

@section('title', 'Admin Panel')

@section('css')
    <!-- Подключение внешних стилей -->
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('js')
    <!-- Подключение внешних скриптов -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Передача маршрутов -->
    <script>
        window.appConfig = {
            routes: {
                mediaIndex: "{{ route('backend.media.index') }}",
                mediaGetByIds: "{{ route('backend.media.getByIds') }}",
                filepondUpload: "{{ route('backend.filepond.upload') }}",
                filepondDelete: "{{ route('backend.filepond.delete') }}"
            }
        };
    </script>
@stop

@vite(['resources/css/admin.css', 'resources/js/admin.js'])
@vite(['resources/js/app.js'])
<div id="app">
    <example-component></example-component>
</div>

@section('content_header')
    <h1>Admin Dashboard</h1>
@stop

@section('content')
    <div class="container-fluid">
        <p>Welcome, {{ Auth::user()->name }}!</p>
        @yield('admin_content')
    </div>
@stop
