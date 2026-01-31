@extends('layouts.app')

@section('title', 'Edit User Roles')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Edit User Roles</h1>
                <p class="mt-1 text-sm text-slate-600">Select roles for this user and save changes.</p>
            </div>

            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold
                      border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition">
                ← Back
            </a>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">

            {{-- User Info --}}
            <div class="mb-6">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">User</div>
                <div class="mt-2">
                    <div class="text-lg font-bold text-slate-900">{{ $user->name }}</div>
                    <div class="text-sm text-slate-600">{{ $user->email }}</div>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Roles</label>

                    <div class="grid sm:grid-cols-2 gap-3">
                        @foreach ($roles as $role)
                            <label
                                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3
                                       hover:bg-slate-50 transition">
                                <input
                                    type="checkbox"
                                    name="roles[]"
                                    value="{{ $role->name }}"
                                    @checked($user->hasRole($role->name))
                                    class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900/10">
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900">{{ $role->name }}</div>
                                    <div class="text-xs text-slate-500">Assign this role to the user</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    @error('roles')
                        <div class="text-sm text-rose-600 mt-2">{{ $message }}</div>
                    @enderror
                    @error('roles.*')
                        <div class="text-sm text-rose-600 mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                    <a href="{{ route('admin.users.index') }}"
                       class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold
                              border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 transition">
                        Cancel
                    </a>

                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold
                                   bg-slate-900 text-white hover:bg-slate-800 transition">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
