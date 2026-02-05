<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'profile_image_id' => 'nullable|exists:media,id',
            'role' => 'required|string|in:administrator,editor,author,contributor,subscriber',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'bio' => $request->bio,
            'profile_image_id' => $request->profile_image_id,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // We specifically retrieve the user instance again from DB for update if needed, but auth()->user() is an instance.
        // User model update requires instance.
        // auth()->user() returns Authenticatable contract, but in standard Laravel it is the User model.

        /** @var \App\Models\User $user */
        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }
}
