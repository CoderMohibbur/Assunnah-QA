@extends('layouts.app')

@section('title', $category->name_bn ?? 'ক্যাটাগরি')

@section('content')
    <div class="qa-card">
        @include('partials.breadcrumbs', [
            'items' => [
                ['label' => 'হোম', 'url' => url('/')],
                ['label' => 'ক্যাটাগরি', 'url' => route('categories.show', $category->slug)],
                ['label' => $category->name_bn, 'url' => route('categories.show', $category->slug)],
            ],
        ])

        <div class="mt-2 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">{{ $category->name_bn }}</h1>
                <p class="mt-1 text-sm text-slate-600">এই ক্যাটাগরির Published প্রশ্নগুলো।</p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('questions.index') }}" class="qa-btn qa-btn-outline">সব প্রশ্ন</a>
                <a href="{{ route('ask') }}" class="qa-btn qa-btn-primary">প্রশ্ন করুন</a>
            </div>
        </div>
    </div>

    <div class="mt-6 space-y-4">
        @forelse($questions as $q)
            <a href="{{ route('questions.show', ['slug' => $q->slug ?? 'q-' . $q->id]) }}"
                class="block qa-card qa-card-hover p-4">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="sm:w-40 shrink-0">
                        <div class="flex items-baseline gap-2">
                            <span class="text-xs text-slate-500">প্রশ্ন</span>
                            <span class="text-2xl font-extrabold text-slate-900">{{ $q->id }}</span>
                        </div>
                        <div class="mt-1 text-xs text-slate-500">
                            {{ optional($q->published_at)->format('Y-m-d') ?? optional($q->created_at)->format('Y-m-d') }}
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="text-base font-extrabold text-slate-900">{{ $q->title }}</div>
                        <div class="mt-1 text-sm text-slate-600 line-clamp-2">
                            {{ \Illuminate\Support\Str::limit(strip_tags($q->body_html ?? ''), 110) }}

                        </div>
                    </div>

                    <div class="sm:w-32 shrink-0 sm:text-right">
                        <span class="qa-btn qa-btn-outline px-4">বিস্তারিত →</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="qa-card text-center">
                <div class="text-lg font-extrabold text-slate-900">এই ক্যাটাগরিতে কোনো প্রশ্ন নেই</div>
                <div class="mt-1 text-sm text-slate-600">প্রথম প্রশ্নটি আপনি করতে পারেন।</div>
                <div class="mt-4">
                    <a href="{{ route('ask') }}" class="qa-btn qa-btn-primary">প্রশ্ন করুন</a>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $questions->links() }}
    </div>
@endsection
