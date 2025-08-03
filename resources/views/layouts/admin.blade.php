@extends('adminlte::page')

@section('title', 'Admin Panel')

@section('css')
@stop

@section('js')
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
    @vite(['resources/css/admin.css', 'resources/js/admin.js', 'resources/backend/js/admin-media.js'])
@stop

@section('content_header')
    <h1>Admin Dashboard</h1>
@stop

@section('content')
    <div class="container-fluid">
        <p>Welcome, {{ Auth::user()->name }}!</p>
        @yield('admin_content')
    </div>
@stop
