<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private const PAGINATION_LIMIT = 10;

    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage-users']);
    }

    /**
     * Display a listing of users.
     */
    public function index(): View
    {
        $users = User::with('roles', 'permissions')
            ->paginate(self::PAGINATION_LIMIT);

        return view('backend.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::all();
        return view('backend.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            if ($request->roles) {
                $user->syncRoles($request->roles);
            }

            return redirect()->route('backend.users.index')
                ->with('success', __('User created successfully'));
        } catch (\Exception $e) {
            return back()->with('error', __('User creation failed'));
        }
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(User $user): View
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('name')->toArray();
        return view('backend.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->password) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);
            $user->syncRoles($request->roles);

            return redirect()->route('backend.users.index')
                ->with('success', __('User updated successfully'));
        } catch (\Exception $e) {
            return back()->with('error', __('User update failed'));
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            $user->delete();
            return redirect()->route('backend.users.index')
                ->with('success', __('User deleted successfully'));
        } catch (\Exception $e) {
            return back()->with('error', __('User deletion failed'));
        }
    }
}
