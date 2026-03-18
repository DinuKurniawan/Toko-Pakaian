<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AppCache;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
        ]);

        $query = User::query()
            ->latest()
            ->when($filters['search'] ?? null, function ($builder, $search) {
                $builder->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, fn ($builder, $role) => $builder->where('role', $role));

        return inertia('Admin/Users/Index', [
            'users' => $query->paginate(10)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function show(User $user)
    {
        $user->load(['orders' => function ($q) {
            $q->latest()->take(10)->withCount('items');
        }]);

        return inertia('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        return inertia('Admin/Users/Edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if (filled($validated['password'] ?? null)) {
            $user->password = $validated['password'];
        }

        $user->save();
        AppCache::forgetAdminDashboard();

        return redirect()->route('admin.users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();
        AppCache::forgetAdminDashboard();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
