@extends('layouts.app')

@section('title', 'Admin — Create Page')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jodit@4.1.16/es2021/jodit.min.css">
@endpush

@section('content')
    <div class="qa-card">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pages.index') }}" class="qa-btn qa-btn-outline">← Back</a>
            <span class="qa-badge">Create</span>
        </div>
        <h1 class="mt-3 text-xl font-extrabold text-slate-900">New Page</h1>
        <p class="text-sm text-slate-600 mt-1">Slug উদাহরণ: <span class="font-semibold">about</span>, <span
                class="font-semibold">home_featured</span></p>
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
        <form method="POST" action="{{ route('admin.pages.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="text-sm font-bold text-slate-800">Slug *</label>
                <input name="slug" class="qa-input mt-2 w-full" value="{{ old('slug') }}" placeholder="about">
            </div>

            <div>
                <label class="text-sm font-bold text-slate-800">Title</label>
                <input name="title" class="qa-input mt-2 w-full" value="{{ old('title') }}">
            </div>

            <div>
                <label class="text-sm font-bold text-slate-800">Content (HTML)</label>
                <textarea id="content_html" name="content_html" class="qa-input mt-2 w-full min-h-[280px]">{{ old('content_html') }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded" @checked(old('is_active', 1))>
                <span class="text-sm font-semibold text-slate-700">Active</span>
            </div>

            <div class="flex gap-2">
                <button class="qa-btn qa-btn-primary" type="submit">Create</button>
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
