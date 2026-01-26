@extends('layouts.app')

@section('title', 'Admin — Categories')

@section('content')
    <div class="qa-card">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-extrabold text-slate-900">Categories</h1>
                <p class="text-sm text-slate-600 mt-1">ক্যাটাগরি তৈরি/এডিট/ডিলিট করুন। Public Ask page এ এগুলো দেখাবে।</p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('admin.categories.create') }}" class="qa-btn qa-btn-primary">
                    + New Category
                </a>
            </div>
        </div>
    </div>

    {{-- Flash --}}
    <div class="mt-4">
        @if (session('success'))
            <div class="qa-card border border-emerald-200 bg-emerald-50 text-emerald-900">
                <div class="font-semibold">{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="qa-card border border-rose-200 bg-rose-50 text-rose-900 mt-3">
                <div class="font-extrabold">Fix these:</div>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Search --}}
    <div class="mt-4 qa-card">
        <form method="GET" action="{{ route('admin.categories.index') }}"
            class="flex flex-col sm:flex-row gap-3 sm:items-end">
            <div class="flex-1">
                <label class="text-xs text-slate-500">Search</label>
                <input name="q" value="{{ $q ?? '' }}" class="qa-input w-full"
                    placeholder="name_bn / slug দিয়ে সার্চ করুন" />
            </div>

            <div class="flex gap-2">
                <button class="qa-btn qa-btn-primary" type="submit">Filter</button>
                <a class="qa-btn qa-btn-outline" href="{{ route('admin.categories.index') }}">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="mt-4 qa-card">
        <div class="flex items-center justify-between">
            <div class="font-extrabold text-slate-900">তালিকা ({{ $categories->total() }})</div>
            <div class="text-xs text-slate-500">Sort: sort_order ASC</div>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500">
                        <th class="py-2 pr-4">#</th>
                        <th class="py-2 pr-4">Name (BN)</th>
                        <th class="py-2 pr-4">Slug</th>
                        <th class="py-2 pr-4">Sort</th>
                        <th class="py-2 pr-4">Active</th>
                        <th class="py-2 pr-4">Created</th>
                        <th class="py-2 pr-4">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $row)
                        <tr class="align-top">
                            <td class="py-3 pr-4 font-semibold text-slate-700">{{ $row->id }}</td>

                            <td class="py-3 pr-4">
                                <div class="font-extrabold text-slate-900">{{ $row->name_bn }}</div>
                                @if ($row->description)
                                    <div class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $row->description }}</div>
                                @endif
                            </td>

                            <td class="py-3 pr-4 text-slate-700">
                                <span class="qa-badge">{{ $row->slug }}</span>
                            </td>

                            <td class="py-3 pr-4 text-slate-700">
                                {{ $row->sort_order ?? 0 }}
                            </td>

                            <td class="py-3 pr-4">
                                @if ($row->is_active)
                                    <span class="qa-badge"
                                        style="background: rgba(16,185,129,.12); color:#065f46;">Active</span>
                                @else
                                    <span class="qa-badge"
                                        style="background: rgba(239,68,68,.10); color:#991b1b;">Inactive</span>
                                @endif
                            </td>

                            <td class="py-3 pr-4 text-slate-700">
                                {{ optional($row->created_at)->format('Y-m-d') }}
                            </td>

                            <td class="py-3 pr-4">
                                <div class="flex flex-wrap gap-2">
                                    <a class="qa-btn qa-btn-outline" href="{{ route('admin.categories.edit', $row) }}">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.categories.destroy', $row) }}"
                                        onsubmit="return confirm('Delete করতে চান? (soft delete হবে)')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="qa-btn qa-btn-outline">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-500">
                                কোনো category নেই। <a class="underline" href="{{ route('admin.categories.create') }}">এখানে
                                    ক্লিক করে</a> নতুন category বানান।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </div>
@endsection
