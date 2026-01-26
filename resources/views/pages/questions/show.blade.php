@extends('layouts.app')

@section('title', $question->title ?? 'প্রশ্ন বিস্তারিত')

@section('content')
    @php
        $answer = $question->answer ?? null;
        $isAnswerPublished = $answer && $answer->status === 'published' && empty($answer->deleted_at);
    @endphp

    {{-- Header --}}
    <div class="qa-card">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($question->category)
                        <a href="{{ route('categories.show', $question->category->slug) }}" class="qa-badge">
                            {{ $question->category->name_bn }}
                        </a>
                    @endif

                    <span class="text-xs text-slate-500">• প্রশ্ন #{{ $question->id }}</span>
                    <span class="text-xs text-slate-500">• ভিউ: {{ (int) ($question->view_count ?? 0) }}</span>
                    <span class="text-xs text-slate-500">•
                        {{ optional($question->published_at ?? $question->created_at)->format('Y-m-d') }}</span>
                </div>

                <h1 class="mt-3 text-xl md:text-2xl font-extrabold text-slate-900 leading-snug">
                    {{ $question->title }}
                </h1>

                {{-- Public এ Phone/Email দেখাবেন না (privacy) --}}
                <div class="mt-2 text-sm text-slate-600">
                    প্রশ্নকারী: <span class="font-semibold text-slate-800">{{ $question->asker_name }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 md:justify-end items-center">
                <a href="{{ url()->previous() }}" class="qa-btn qa-btn-outline">← Back</a>

                {{-- ✅ Share Dropdown --}}
                <div x-data="qaShare()" class="relative">
                    <button type="button" @click="open = !open" class="qa-btn qa-btn-outline flex items-center gap-2"
                        aria-label="Share">
                        <span></span>
                        <span class="font-semibold">Share</span>
                        <span class="text-xs">▾</span>
                    </button>

                    <div x-show="open" x-cloak @click.outside="open=false" x-transition
                        class="absolute right-0 mt-2 w-56 rounded-2xl border border-slate-200 bg-white shadow-lg p-2 z-50">

                        <a :href="fb" target="_blank" rel="noopener"
                            class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                             Facebook
                        </a>

                        <a :href="wa" target="_blank" rel="noopener"
                            class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                             WhatsApp
                        </a>

                        <a :href="tg" target="_blank" rel="noopener"
                            class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                             Telegram
                        </a>

                        <button type="button" @click="copy()"
                            class="w-full text-left flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                             Copy link
                        </button>

                        <button type="button" x-show="canShare" x-cloak @click="native()"
                            class="w-full text-left flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                             Share (Mobile)
                        </button>
                    </div>
                </div>

                <a href="{{ route('ask') }}" class="qa-btn qa-btn-primary">প্রশ্ন করুন</a>
            </div>

        </div>
    </div>

    {{-- Question Body --}}
    <div class="mt-6 qa-card">
        <div class="font-extrabold text-slate-900">প্রশ্ন বিস্তারিত</div>
        <div class="mt-4 prose prose-slate max-w-none">
            {!! $question->body_html !!}
        </div>
    </div>

    {{-- Answer --}}
    <div class="mt-6 qa-card">
        <div class="flex items-center justify-between gap-3">
            <div class="font-extrabold text-slate-900">উত্তর</div>
            @if ($isAnswerPublished)
                <span class="qa-badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;">Published</span>
            @else
                <span class="qa-badge" style="background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;">Processing</span>
            @endif
        </div>

        @if ($isAnswerPublished)
            <div class="mt-4 prose prose-slate max-w-none">
                {!! $answer->answer_html !!}
            </div>
        @else
            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                এই প্রশ্নটির উত্তর প্রস্তুত হচ্ছে। যাচাই-বাছাই শেষে ইনশাআল্লাহ প্রকাশ করা হবে।
            </div>
        @endif
    </div>
@endsection
@push('scripts')
    <script>
        window.qaShare = function() {
            const url = window.location.href;
            const title = document.title || 'প্রশ্ন';

            return {
                open: false,
                canShare: !!navigator.share,

                get fb() {
                    return 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
                },
                get wa() {
                    return 'https://wa.me/?text=' + encodeURIComponent(title + ' — ' + url);
                },
                get tg() {
                    return 'https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(
                        title);
                },

                async copy() {
                    try {
                        await navigator.clipboard.writeText(url);
                        this.open = false;
                        if (window.toast) window.toast({
                            title: 'Copied ✅',
                            message: 'Link copy হয়েছে।',
                            link: ''
                        });
                    } catch (e) {
                        // fallback
                        const ta = document.createElement('textarea');
                        ta.value = url;
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        ta.remove();
                        this.open = false;
                        if (window.toast) window.toast({
                            title: 'Copied ✅',
                            message: 'Link copy হয়েছে।',
                            link: ''
                        });
                    }
                },

                async native() {
                    try {
                        await navigator.share({
                            title,
                            url
                        });
                        this.open = false;
                    } catch (e) {}
                }
            }
        }
    </script>
@endpush
