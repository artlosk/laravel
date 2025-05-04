<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        if ($request->isMethod('post')) {
            // Валидация данных
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            ]);

            // Обновление данных пользователя
            $user->update([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
            ]);

            return redirect()->route('backend.profile')->with('success', 'Profile updated successfully.');
        }

        // Показ формы редактирования
        return view('backend.profile.update', compact('user'));
    }

    public function changePassword(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('The current password is incorrect.');
                    }
                }],
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user->update([
                'password' => Hash::make($request->get('password')),
            ]);

            return redirect()->route('backend.password')->with('success', 'Password updated successfully.');
        }

        return view('backend.profile.password');
    }
}
