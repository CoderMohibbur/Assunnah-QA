@extends('layouts.app')

@section('title', 'Create User')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Create User</h1>
                <p class="mt-1 text-sm text-slate-600">Create a new user and assign roles instantly.</p>
            </div>

            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold
                      border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition">
                ← Back
            </a>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900
                               placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300"
                        placeholder="Full name">
                    @error('name')
                        <div class="text-sm text-rose-600 mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900
                               placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300"
                        placeholder="email@example.com">
                    @error('email')
                        <div class="text-sm text-rose-600 mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">
                        Password <span class="text-slate-500 font-medium">(optional)</span>
                    </label>
                    <input type="text" name="password" value="{{ old('password') }}"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900
                               placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300"
                        placeholder="Leave empty to auto-generate">
                    <p class="mt-1 text-xs text-slate-500">
                        If left empty, the system will generate a secure password automatically.
                    </p>
                    @error('password')
                        <div class="text-sm text-rose-600 mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Roles --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Roles</label>

                    <div class="grid sm:grid-cols-2 gap-3">
                        @foreach ($roles as $role)
                            <label
                                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3
                                       hover:bg-slate-50 transition">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                    @checked(in_array($role->name, (array) old('roles', [])))
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
                        Create
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection
