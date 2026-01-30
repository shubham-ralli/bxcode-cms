<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->has('s') && $search = $request->s) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role Filter
        if ($request->has('role') && $role = $request->role) {
            if ($role !== 'all') {
                $query->where('role', $role);
            }
        }

        // Counts for tabs
        $counts = [
            'all' => User::count(),
            'administrator' => User::where('role', 'administrator')->count(),
            'editor' => User::where('role', 'editor')->count(),
            'author' => User::where('role', 'author')->count(),
            'contributor' => User::where('role', 'contributor')->count(),
            'subscriber' => User::where('role', 'subscriber')->count(),
        ];

        $users = $query->latest()->paginate(20)->withQueryString();

        $status = $request->get('role', 'all');

        return view('admin.users.index', compact('users', 'counts', 'status'));
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'action' => 'required|in:delete',
        ]);

        if ($request->input('action') === 'delete') {
            $ids = $request->input('ids');
            // Prevent deleting self
            if (in_array(auth()->id(), $ids)) {
                return back()->with('error', 'You cannot delete yourself.');
            }

            User::destroy($ids);
            return back()->with('success', 'Selected users deleted.');
        }

        return back()->with('error', 'Invalid action.');
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'role' => 'required|in:subscriber,contributor,author,editor,administrator',
            'bio' => 'nullable|string',
            'profile_image_id' => 'nullable|exists:media,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'role' => $request->role,
            'bio' => $request->bio,
            'profile_image_id' => $request->profile_image_id,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'role' => 'required|in:subscriber,contributor,author,editor,administrator',
            'bio' => 'nullable|string',
            'profile_image_id' => 'nullable|exists:media,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'role' => $request->role,
            'bio' => $request->bio,
            'profile_image_id' => $request->profile_image_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
}
