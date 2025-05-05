@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Create New User</h1>

        <form action="{{ route('backend.users.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror"
                       id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror"
                       id="password" name="password" required>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" class="form-control"
                       id="password_confirmation" name="password_confirmation" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Roles</label>
                @foreach($roles as $role)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               id="role_{{ $role->id }}" name="roles[]"
                               value="{{ $role->name }}"
                               @if(old('roles') && in_array($role->name, old('roles'))) checked @endif>
                        <label class="form-check-label" for="role_{{ $role->id }}">
                            {{ $role->name }}
                        </label>
                    </div>
                @endforeach
                @error('roles')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="{{ route('backend.users.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
