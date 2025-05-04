@extends('layouts.admin')

@section('content_header')
    <h1>Posts</h1>
@endsection

@section('admin_content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Posts</h3>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @can('create-posts')
                <a href="{{ route('backend.posts.create') }}" class="btn btn-primary mb-3">Create Post</a>
            @endcan
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($posts as $post)
                    <tr>
                        <td>
                            @can('read-posts')
                                <a href="{{ route('backend.posts.show', $post) }}">{{ $post->title }}</a>
                            @else
                                {{ $post->getAttribute('title') }}
                            @endcan
                        </td>
                        <td>{{ $post->user->name }}</td>
                        <td>
                            @can('edit-posts')
                                <a href="{{ route('backend.posts.update', $post) }}"
                                   class="btn btn-sm btn-warning">Edit</a>
                            @endcan
                            @can('delete-posts')
                                <form action="{{ route('backend.posts.delete', $post) }}" method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">Delete
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
