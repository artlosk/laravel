@extends('adminlte::page')

@section('title', 'Admin Panel')

@section('content_header')
    <h1>Admin Dashboard</h1>
@endsection

@section('content')
    <div class="container-fluid">
        <p>Welcome, {{ Auth::user()->name }}!</p>
        @yield('admin_content')
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .nav-icon.fa-user-shield { color: #17a2b8; }
        .nav-icon.fa-key { color: #28a745; }
    </style>
@endsection

@section('js')
    <script src="{{ asset('js/app.js') }}"></script>
@endsection
