@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Permissions</h3>
            @can('manage-permissions')
                <a href="{{ route('backend.permissions.create') }}" class="btn btn-primary float-right">Create Permission</a>
            @endcan
        </div>
        <div class="card-body">
            @if ($permissions->isEmpty())
                <p>No permissions available.</p>
            @else
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->getAttribute('name') }}</td>
                            <td>
                                @can('manage-permissions')
                                    <a href="{{ route('backend.permissions.edit', $permission) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('backend.permissions.destroy', $permission) }}" method="POST" style="display:inline;">
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
                    {{ $permissions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
