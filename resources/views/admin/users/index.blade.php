@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Users</h1>
                <p class="mt-1 text-sm text-slate-600">Create users and assign roles from here.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <a href="{{ route('admin.users.create') }}"
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold
                           bg-slate-900 text-white hover:bg-slate-800 transition">
                    + Create User
                </a>

                <form method="GET" class="flex gap-2">
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}"
                            class="w-64 max-w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm
                                   text-slate-900 placeholder:text-slate-400
                                   focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300"
                            placeholder="Search name or email...">
                    </div>
                    <button
                        class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold
                               border border-slate-200 bg-white text-slate-900 hover:bg-slate-50 transition">
                        Search
                    </button>
                </form>
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-900">
                {{ session('error') }}
            </div>
        @endif

        {{-- Table Card --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr class="text-left">
                            <th class="px-5 py-4 font-semibold">Name</th>
                            <th class="px-5 py-4 font-semibold">Email</th>
                            <th class="px-5 py-4 font-semibold">Roles</th>
                            <th class="px-5 py-4 font-semibold text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="text-slate-900 hover:bg-slate-50/60 transition">
                                <td class="px-5 py-4">
                                    <div class="font-semibold">{{ $user->name }}</div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="text-slate-700">{{ $user->email }}</div>
                                </td>

                                <td class="px-5 py-4">
                                    @php $roleNames = $user->getRoleNames(); @endphp

                                    @if ($roleNames->count())
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($roleNames as $r)
                                                <span
                                                    class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                    {{ $r }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="inline-flex items-center justify-center rounded-xl px-3 py-2 text-sm font-semibold
                                               border border-slate-200 bg-white text-slate-900 hover:bg-slate-50 transition">
                                        Edit Role
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center">
                                    <div class="text-slate-900 font-semibold">No users found</div>
                                    <div class="text-sm text-slate-500 mt-1">Try a different search keyword.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer / Pagination --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-200 px-5 py-4">
                <div class="text-sm text-slate-600">
                    Showing <span class="font-semibold text-slate-900">{{ $users->firstItem() ?? 0 }}</span>
                    to <span class="font-semibold text-slate-900">{{ $users->lastItem() ?? 0 }}</span>
                    of <span class="font-semibold text-slate-900">{{ $users->total() }}</span> users
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        </div>

    </div>
@endsection
