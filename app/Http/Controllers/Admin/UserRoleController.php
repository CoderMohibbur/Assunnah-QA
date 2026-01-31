<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;


class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $users = User::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }



    public function create()
    {
        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],

            // ✅ Fix: simple rule
            'password' => ['nullable', 'string', 'min:8'],

            'roles'   => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $password = $data['password'] ?? str()->random(12);

        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($password),
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User created successfully. Generated password: {$password}");
    }

    public function edit(User $user)
    {
        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.edit', [
            'user'  => $user,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        // ✅ নিজের role accidentally remove prevent
        if (auth()->id() === $user->id && $request->filled('roles') === false) {
            return back()->with('error', 'আপনি নিজের সব role remove করতে পারবেন না।');
        }

        $data = $request->validate([
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $roles = $data['roles'] ?? [];

        $user->syncRoles($roles);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User role updated successfully.');
    }
}
