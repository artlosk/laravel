@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Roles</h3>
            @can('manage-roles')
                <a href="{{ route('backend.roles.create') }}" class="btn btn-primary float-right">Create Role</a>
            @endcan
        </div>
        <div class="card-body">
            @if ($roles->isEmpty())
                <p>No roles available.</p>
            @else
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ $role->getAttribute('name') }}</td>
                            <td>
                                @can('manage-roles')
                                    <a href="{{ route('backend.roles.edit', $role) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('backend.roles.destroy', $role) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
