@extends('layouts.admin')

@section('admin_content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dashboard</h3>
        </div>
        <div class="card-body">
            <p>Welcome to the Admin Panel, {{ Auth::user()->name }}!</p>
            @if (Auth::user()->hasRole('admin'))
                <p>You have full access to all features.</p>
            @elseif (Auth::user()->hasRole('author'))
                <p>You can manage your own posts.</p>
            @endif
        </div>
    </div>
@endsection
