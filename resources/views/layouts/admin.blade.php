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
