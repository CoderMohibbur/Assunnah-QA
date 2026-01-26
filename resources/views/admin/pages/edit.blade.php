@extends('layouts.app')

@section('title', 'Admin — Edit Page')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jodit@4.1.16/es2021/jodit.min.css">
@endpush

@section('content')
    <div class="qa-card">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pages.index') }}" class="qa-btn qa-btn-outline">← Back</a>
            <span class="qa-badge">Edit</span>
            <span class="text-xs text-slate-500">#{{ $page->id }}</span>
        </div>
        <h1 class="mt-3 text-xl font-extrabold text-slate-900">{{ $page->slug }}</h1>
    </div>

    @if ($errors->any())
        <div class="mt-4 qa-card border border-rose-200 bg-rose-50 text-rose-900">
            <div class="font-extrabold">Fix these:</div>
            <ul class="mt-2 list-disc pl-5 text-sm">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-4 qa-card">
        <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="text-sm font-bold text-slate-800">Slug *</label>
                <input name="slug" class="qa-input mt-2 w-full" value="{{ old('slug', $page->slug) }}">
            </div>

            <div>
                <label class="text-sm font-bold text-slate-800">Title</label>
                <input name="title" class="qa-input mt-2 w-full" value="{{ old('title', $page->title) }}">
            </div>

            <div>
                <label class="text-sm font-bold text-slate-800">Content (HTML)</label>
                <textarea id="content_html" name="content_html" class="qa-input mt-2 w-full min-h-[280px]">{{ old('content_html', $page->content_html) }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded" @checked(old('is_active', $page->is_active ? 1 : 0))>
                <span class="text-sm font-semibold text-slate-700">Active</span>
            </div>

            <div class="flex gap-2">
                <button class="qa-btn qa-btn-primary" type="submit">Save</button>
                <a href="{{ route('admin.pages.index') }}" class="qa-btn qa-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jodit@4.1.16/es2021/jodit.min.js"></script>
    <script>
        (function() {
            var el = document.getElementById('content_html');
            if (!el || el.dataset.inited === "1") return;
            el.dataset.inited = "1";
            new Jodit(el, {
                height: 360,
                toolbarAdaptive: false
            });
        })();
    </script>
@endpush
