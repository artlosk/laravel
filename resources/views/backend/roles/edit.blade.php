@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Role: {{ $role->getAttribute('name') }}</h3>
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

            <form action="{{ route('backend.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name">Role Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $role->getAttribute('name')) }}" required>
                </div>
                <div class="form-group">
                    <label>Permissions</label>
                    <select name="permissions[]" id="permissions" class="form-control select2" multiple>
                        @foreach ($permissions as $permission)
                            <option value="{{ $permission->getAttribute('name') }}"
                                {{ in_array($permission->getAttribute('name'), old('permissions', $rolePermissions)) ? 'selected' : '' }}>
                                {{ $permission->getAttribute('name') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Role</button>
                <a href="{{ route('backend.roles.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('.select2').select2({
                    placeholder: 'Select permissions',
                    allowClear: true
                });
            });
        </script>
    @endpush
@endsection
