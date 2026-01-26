@extends('layouts.app')

@section('title', 'Admin — Edit Category')

@section('content')
    <div class="qa-card">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.categories.index') }}" class="qa-btn qa-btn-outline">← Back</a>
                    <span class="qa-badge">Edit</span>
                    <span class="text-xs text-slate-500">#{{ $category->id }}</span>
                </div>

                <h1 class="mt-3 text-xl md:text-2xl font-extrabold text-slate-900">
                    {{ $category->name_bn }}
                </h1>

                <p class="text-sm text-slate-600 mt-1">
                    Slug: <span class="font-semibold">{{ $category->slug }}</span>
                </p>
            </div>

            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                    onsubmit="return confirm('Delete করতে চান? (soft delete হবে)')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="qa-btn qa-btn-outline">Delete</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Flash / Errors --}}
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

    <div class="mt-4 qa-card">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="text-sm font-bold text-slate-800">ক্যাটাগরি নাম (বাংলা) <span
                        class="text-red-600">*</span></label>
                <input type="text" name="name_bn" required class="qa-input mt-2 w-full"
                    value="{{ old('name_bn', $category->name_bn) }}">
                @error('name_bn')
                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold text-slate-800">Slug <span class="text-red-600">*</span></label>
                <input type="text" name="slug" required class="qa-input mt-2 w-full"
                    value="{{ old('slug', $category->slug) }}">
                @error('slug')
                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="text-sm font-bold text-slate-800">Description (optional)</label>
                <textarea name="description" rows="4" class="qa-input mt-2 w-full">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-800">Sort Order</label>
                    <input type="number" name="sort_order" class="qa-input mt-2 w-full"
                        value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0" max="9999">
                    @error('sort_order')
                        <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-2 flex items-center gap-2 pt-6">
                    <input type="hidden" name="is_active" value="0">

                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded"
                        @checked(old('is_active', $category->is_active ? 1 : 0))>
                    <label for="is_active" class="text-sm text-slate-700 font-semibold">Active রাখুন</label>
                </div>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row gap-2">
                <button type="submit" class="qa-btn qa-btn-primary w-full sm:w-auto">Save Changes</button>
                <a href="{{ route('admin.categories.index') }}" class="qa-btn qa-btn-outline w-full sm:w-auto">Cancel</a>
            </div>
        </form>
    </div>
@endsection
