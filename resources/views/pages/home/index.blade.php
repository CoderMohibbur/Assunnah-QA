@extends('layouts.app')

@section('title', 'প্রশ্নোত্তর')

@section('content')
    @php
        use Carbon\Carbon;
        use Illuminate\Support\Str;

        $q = trim((string) ($q ?? request('q', '')));

        $bn = fn($s) => strtr((string) $s, [
            '0' => '০',
            '1' => '১',
            '2' => '২',
            '3' => '৩',
            '4' => '৪',
            '5' => '৫',
            '6' => '৬',
            '7' => '৭',
            '8' => '৮',
            '9' => '৯',
        ]);

        $bnDateLabel = function ($dt) use ($bn) {
            if (!$dt) {
                return '—';
            }
            try {
                $c = $dt instanceof \DateTimeInterface ? Carbon::instance($dt) : Carbon::parse($dt);
                $c = $c->timezone(config('app.timezone'))->locale('bn');
                return $bn($c->translatedFormat('d F, Y'));
            } catch (\Throwable $e) {
                try {
                    $c = $dt instanceof \DateTimeInterface ? Carbon::instance($dt) : Carbon::parse($dt);
                    return $bn($c->format('d-m-Y'));
                } catch (\Throwable $e2) {
                    return '—';
                }
            }
        };

        $canSeeAsker = (bool) ($canSeeAsker ?? false);

    @endphp

    <div x-data="qaHomeState()" x-init="init()">

        {{-- Top Row: Slider + Featured Card --}}
        {{-- ✅ Shared CSS (paste once with this block) --}}
        <style>
            /* ✅ make swiper take full height of parent */
            .qa-swiper,
            .qa-swiper .swiper-wrapper,
            .qa-swiper .swiper-slide {
                height: 100%;
            }

            /* ✅ Featured buttons layout */
            .qa-featured-actions {
                position: relative;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .qa-featured-actions .qa-btn {
                border-radius: 0 !important;
                padding: 8px 14px !important;
                font-size: 13px !important;
                min-height: 34px !important;
                margin-right: -4px;
            }

            @media (min-width: 640px) {
                .qa-featured-actions {
                    min-height: 74px;
                }

                .qa-featured-btn-center {
                    width: 8rem !important;
                    margin-left: 0 !important;
                    margin-right: auto !important;
                    justify-content: center;
                }

                .qa-featured-btn-right {
                    position: absolute;
                    right: 0;
                    bottom: 0;
                    width: 8rem !important;
                    justify-content: center;
                }
            }

            @media (max-width: 639.98px) {

                .qa-featured-btn-center,
                .qa-featured-btn-right {
                    width: 100%;
                }
            }
        </style>

        {{-- Top Row: Slider + Featured Card --}}
        <div class="grid grid-cols-12 gap-6 items-stretch">

            {{-- Slider --}}
            <div class="col-span-12 lg:col-span-8 h-full">
                <div class="qa-card qa-card-hover p-0 overflow-hidden h-full">
                    <div class="relative h-full">
                        <div class="swiper qa-swiper h-full">
                            <div class="swiper-wrapper">

                                @forelse (($featured ?? collect()) as $f)
                                    @php
                                        $slug = $f->slug ?: 'q-' . $f->id;
                                        $ans = $f->answer;
                                        $answeredAt =
                                            $ans?->answered_at ??
                                            ($ans?->updated_at ?? ($f->published_at ?? $f->created_at));
                                        $date = $bnDateLabel($answeredAt);

                                        $sliderText = (string) ($ans?->answer_html ?? $f->body_html);
                                        $excerpt = Str::limit(strip_tags($sliderText), 140);
                                    @endphp

                                    <div class="swiper-slide">
                                        {{-- ✅ add min-h + h-full so slide can stretch to match right card --}}
                                        <div class="p-6 bg-[#0b4c7a] text-white flex items-center min-h-[280px] h-full">
                                            <div class="w-full">
                                                <div class="text-center text-sm opacity-90 mb-4">
                                                    প্রশ্ন: {{ $bn($f->id) }}
                                                    @if (!is_null($f->view_count))
                                                        <span class="opacity-80"> • দেখা: {{ $bn($f->view_count) }}</span>
                                                    @endif
                                                </div>

                                                <h3 class="text-xl md:text-2xl font-bold leading-snug text-center">
                                                    {{ $f->title }}
                                                </h3>

                                                <p class="mt-4 text-white/90 text-center max-w-2xl mx-auto">
                                                    {{ $excerpt }}
                                                </p>

                                                <div class="mt-5 text-center text-xs text-white/80">{{ $date }}
                                                </div>

                                                <div class="mt-6 mb-2 flex justify-center">
                                                    <a href="{{ route('questions.show', ['slug' => $slug]) }}"
                                                        class="qa-btn qa-btn-outline bg-white/10 border-white/30 text-white hover:bg-white/15">
                                                        বিস্তারিত দেখুন
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="swiper-slide">
                                        <div class="p-6 bg-[#0b4c7a] text-white min-h-[280px] h-full flex items-center">
                                            <div class="w-full text-center">
                                                <div class="text-xl font-extrabold">এখনো কোনো Published প্রশ্নোত্তর নেই
                                                </div>
                                                <div class="mt-2 text-white/80 text-sm">প্রথম প্রশ্নটি আপনি করতে পারেন।
                                                </div>
                                                <div class="mt-6">
                                                    <a href="{{ route('ask') }}"
                                                        class="qa-btn qa-btn-outline bg-white/10 border-white/30 text-white hover:bg-white/15">
                                                        প্রশ্ন করুন
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforelse

                            </div>

                            <div class="swiper-pagination"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Featured Card --}}
            <div class="col-span-12 lg:col-span-4 h-full">
                <div class="qa-card qa-card-hover overflow-hidden p-0 h-full flex flex-col">
                    @php
                        // ✅ Page/Settings থেকে যেখান থেকে লোগো আনবেন সেটার সাথে মিলিয়ে নিন
                        // উদাহরণ: $homeFeatured->logo / $homeFeatured->image / config('qa.logo')
                        $cardLogo = $homeFeatured?->logo ?? ($homeFeatured?->image ?? null);

                        // যদি স্টোরেজ পাথ হয়
                        $cardLogoUrl = null;
                        if ($cardLogo) {
                            $cardLogoUrl = \Illuminate\Support\Str::startsWith($cardLogo, ['http://', 'https://'])
                                ? $cardLogo
                                : \Illuminate\Support\Facades\Storage::url($cardLogo);
                        }
                    @endphp

                    <div class="h-40 bg-gradient-to-br from-slate-900 via-blue-700 to-red-900 relative shrink-0">
                        <div
                            class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_30%_30%,white,transparent_55%)]">
                        </div>

                        <div class="absolute inset-0 flex items-center justify-center px-4 text-white">
                            @if ($cardLogoUrl)
                                {{-- ✅ Logo Option --}}
                                <img src="{{ $cardLogoUrl }}" alt="Logo"
                                    class="h-10 sm:h-12 w-auto object-contain drop-shadow" loading="lazy">
                            @else
                                {{-- ✅ Fallback Title --}}
                                <div class="text-xl font-extrabold leading-tight text-center">
                                    এক নজরে<br>সকল প্রশ্নোত্তর
                                </div>
                            @endif
                        </div>

                    </div>

                    <div class="p-5 flex-1">
                        @php
                            $latestQ = $latest ?? null;

                            $latestSlug = $latestQ?->slug ?: ($latestQ ? 'q-' . $latestQ->id : null);
                            $latestUrl = $latestSlug ? route('questions.show', ['slug' => $latestSlug]) : '#';

                            $latestAns = $latestQ?->answer;
                            $latestText = (string) ($latestAns?->answer_html ?? $latestQ?->body_html);
                            $latestExcerpt = \Illuminate\Support\Str::limit(strip_tags($latestText), 110);

                            $latestDate = $bnDateLabel(
                                $latestAns?->answered_at ??
                                    ($latestAns?->updated_at ?? ($latestQ?->published_at ?? $latestQ?->created_at)),
                            );
                        @endphp

                        @if ($latestQ)
                            <div class="text-xs text-slate-500 mb-2">
                                সর্বশেষ প্রশ্ন • {{ $latestDate }}
                            </div>

                            <div class="text-sm font-extrabold text-slate-900 leading-snug">
                                {{ $latestQ->title }}
                            </div>

                            <div class="mt-2 text-sm text-slate-600 leading-relaxed">
                                {{ $latestExcerpt }}
                            </div>
                        @else
                            <div class="text-sm text-slate-600 leading-relaxed">
                                এখনো কোনো Published প্রশ্নোত্তর নেই।
                            </div>
                        @endif

                        <div class="mt-4 qa-featured-actions">
                            <a href="{{ $latestQ ? $latestUrl : route('questions.index') }}"
                                class="qa-btn qa-btn-primary qa-featured-btn-center">
                                বিস্তারিত...
                            </a>

                            <a href="{{ route('questions.index') }}" class="qa-btn qa-btn-primary qa-featured-btn-right">
                                প্রশ্নোত্তর
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        {{-- Search Bar --}}
        <div class="mt-6">
            <div class="qa-card p-3">
                <form method="GET" action="{{ route('home') }}" class="flex gap-3 items-center">
                    <input class="qa-input flex-1" name="q" x-model="q" placeholder="প্রশ্নোত্তর খুঁজুন..."
                        autocomplete="off" />

                    <button type="button" x-show="q?.length" x-cloak @click="clearSearch()"
                        class="qa-btn qa-btn-outline px-4">✕</button>

                    <button type="submit" class="qa-btn qa-btn-primary px-5">🔍</button>
                </form>
            </div>
        </div>

        {{-- Questions Grid --}}
        <div class="mt-6 qa-card">
            <div class="flex items-center justify-between gap-3">
                <div class="font-extrabold text-slate-900 text-lg">
                    প্রশ্নোত্তর
                    @if ($q !== '')
                        <span class="text-sm text-slate-500 font-semibold">— “{{ $q }}”</span>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="set('grid')"
                        :class="view === 'grid' ? 'qa-btn qa-btn-primary px-3' : 'qa-btn qa-btn-outline px-3'">⬛⬛</button>

                    <button type="button" @click="set('list')"
                        :class="view === 'list' ? 'qa-btn qa-btn-primary px-3' : 'qa-btn qa-btn-outline px-3'">☰</button>
                </div>
            </div>

            @if (($cards ?? collect())->count() === 0)
                <div class="mt-5 qa-card text-center">
                    <div class="text-lg font-extrabold text-slate-900">কোন প্রশ্ন পাওয়া যায়নি</div>
                    <div class="mt-1 text-sm text-slate-600">অন্য শব্দ দিয়ে সার্চ করুন অথবা “প্রশ্ন করুন” পেজে নতুন প্রশ্ন
                        করুন।</div>
                    <div class="mt-4">
                        <a href="{{ route('ask') }}" class="qa-btn qa-btn-primary">প্রশ্ন করুন</a>
                    </div>
                </div>
            @endif

            <div class="mt-5" :class="view === 'grid' ? 'grid sm:grid-cols-2 lg:grid-cols-3 gap-4' : 'space-y-4'">
                @foreach ($cards ?? collect() as $row)
                    @php
                        $slug = $row->slug ?: 'q-' . $row->id;
                        $shareUrl = route('questions.show', ['slug' => $slug]);

                        // ✅ Published answer only (controller already filters)
                        $ans = $row->answer;

                        // ✅ Answer ID (আপনি যেটা দেখাতে চাচ্ছেন)
                        $answerId = $ans?->id;

                        // ✅ Published answer exists?
                        $isAnswerPublished = !empty($answerId);

                        $answeredBy = $ans?->answeredBy?->name ?? 'মডারেটর';
                        $answeredAt =
                            $ans?->answered_at ?? ($ans?->updated_at ?? ($row->published_at ?? $row->created_at));
                        $dateLabel = $bnDateLabel($answeredAt);

                        $excerpt = Str::limit(strip_tags((string) ($ans?->answer_html ?? $row->body_html)), 110);
                        $snippet = $excerpt;

                        $askerName = $row->asker_name ?? ($row->name ?? null);
                        $askerMobile = $row->asker_phone ?? null;
                        $askerEmail = $row->asker_email ?? ($row->email ?? null);
                        $askedAtLabel = $bnDateLabel($row->created_at);
                    @endphp

                    {{-- ✅ Clickable Card (NO overlay link) --}}
                    <div class="qa-card qa-card-hover cursor-pointer" :class="view === 'list' ? 'p-4' : ''"
                        role="link" tabindex="0" @click="window.location.href = @js($shareUrl)"
                        @keydown.enter="window.location.href = @js($shareUrl)"
                        @keydown.space.prevent="window.location.href = @js($shareUrl)">

                        {{-- GRID VIEW --}}
                        <template x-if="view==='grid'">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <span class="qa-badge">{{ $row->category?->name_bn ?? 'Uncategorized' }}</span>
                                    <span class="text-xs text-slate-500">{{ $dateLabel }}</span>
                                </div>

                                <div class="mt-3 text-center">
                                    <div class="text-sm text-slate-600">প্রশ্ন:</div>

                                    {{-- ✅ Serial prefer, fallback id --}}
                                    <div class="text-3xl font-extrabold text-slate-900">
                                        {{ $bn($row->published_serial ?? $row->id) }}
                                    </div>

                                    {{-- ✅ Answer ID / Processing --}}
                                    {{-- @if ($isAnswerPublished)
                                        <div class="mt-1 text-xs font-semibold text-emerald-700">
                                            Answer ID: {{ $bn($answerId) }}
                                        </div>
                                    @else
                                        <div class="mt-1 text-xs font-semibold text-amber-700">
                                            Answer: Processing
                                        </div>
                                    @endif --}}
                                </div>

                                <div class="mt-3 text-sm font-semibold text-slate-800"
                                    x-html="highlight(@js($row->title))"></div>
                                <div class="mt-2 text-sm text-slate-600" x-html="highlight(@js($excerpt))">
                                </div>

                                <div class="mt-4 border-t pt-3 text-xs text-slate-500">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            উত্তর দিয়েছেন:
                                            <span class="font-semibold text-slate-700">{{ $answeredBy }}</span>
                                        </div>

                                        {{-- ✅ Published/Processing badge --}}
                                        @if ($isAnswerPublished)
                                            <span class="qa-badge"
                                                style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;">Published</span>
                                        @else
                                            <span class="qa-badge"
                                                style="background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;">Processing</span>
                                        @endif
                                    </div>
                                    <div class="mt-1">তারিখ: {{ $dateLabel }}</div>
                                </div>

                                @if ($canSeeAsker)
                                    <div
                                        class="mt-3 rounded-lg border border-gray-300 bg-white p-3 text-xs text-slate-700">
                                        <div class="font-extrabold text-amber-800 mb-1">🔒 Admin Only — প্রশ্নকারী তথ্য
                                        </div>

                                        <div>নাম: <span class="font-semibold">{{ $askerName ?? '—' }}</span></div>
                                        <div class="mt-1">মোবাইল: <span
                                                class="font-semibold">{{ $askerMobile ?? '—' }}</span></div>

                                        @if (!empty($askerEmail))
                                            <div class="mt-1">ইমেইল: <span
                                                    class="font-semibold">{{ $askerEmail }}</span></div>
                                        @endif

                                        <div class="mt-1">প্রশ্নের তারিখ: <span
                                                class="font-semibold">{{ $askedAtLabel }}</span></div>
                                    </div>
                                @endif

                                <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                    <span class="qa-btn qa-btn-outline px-3 py-1">বিস্তারিত পড়ুন →</span>

                                    {{-- ✅ Share button (stops navigation) --}}
                                    <button type="button" class="qa-btn qa-btn-outline qa-share-btn" title="Share"
                                        @click.stop.prevent="qaShare($event, @js($shareUrl), @js($row->title))">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                        {{-- LIST VIEW --}}
                        <template x-if="view==='list'">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                <div class="sm:w-44 shrink-0">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xs text-slate-500">প্রশ্ন</span>
                                        <span class="text-2xl font-extrabold text-slate-900">
                                            {{ $bn($row->published_serial ?? $row->id) }}
                                        </span>
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">{{ $dateLabel }}</div>

                                    {{-- ✅ Answer ID / Processing --}}
                                    {{-- <div class="mt-1 text-xs">
                                        @if ($isAnswerPublished)
                                            <span class="font-semibold text-emerald-700">Answer ID:
                                                {{ $bn($answerId) }}</span>
                                        @else
                                            <span class="font-semibold text-amber-700">Answer: Processing</span>
                                        @endif
                                    </div> --}}
                                </div>

                                <div class="flex-1">
                                    <div class="text-sm sm:text-base font-extrabold text-slate-900 leading-snug"
                                        x-html="highlight(@js($row->title))"></div>
                                    <div class="mt-1 text-sm text-slate-600 line-clamp-2"
                                        x-html="highlight(@js($snippet))"></div>

                                    <div class="mt-2 text-xs text-slate-500 flex items-center gap-2">
                                        <span>উত্তর দিয়েছেন: <span
                                                class="font-semibold text-slate-700">{{ $answeredBy }}</span></span>

                                        @if ($isAnswerPublished)
                                            <span class="qa-badge"
                                                style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;">Published</span>
                                        @else
                                            <span class="qa-badge"
                                                style="background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;">Processing</span>
                                        @endif
                                    </div>
                                </div>

                                @if ($canSeeAsker)
                                    <div
                                        class="mt-3 sm:mt-0 rounded-lg border border-gray-300 bg-white p-3 text-xs text-slate-700">
                                        <div class="font-extrabold text-amber-800 mb-1">🔒 Admin Only — প্রশ্নকারী তথ্য
                                        </div>

                                        <div>নাম: <span class="font-semibold">{{ $askerName ?? '—' }}</span></div>
                                        <div class="mt-1">মোবাইল: <span
                                                class="font-semibold">{{ $askerMobile ?? '—' }}</span></div>

                                        @if (!empty($askerEmail))
                                            <div class="mt-1">ইমেইল: <span
                                                    class="font-semibold">{{ $askerEmail }}</span></div>
                                        @endif

                                        <div class="mt-1">প্রশ্নের তারিখ: <span
                                                class="font-semibold">{{ $askedAtLabel }}</span></div>
                                    </div>
                                @endif

                                <div class="sm:w-40 shrink-0 sm:text-right flex items-center justify-end gap-2">
                                    <span class="qa-btn qa-btn-outline px-4">বিস্তারিত →</span>

                                    <button type="button" class="qa-btn qa-btn-outline qa-share-btn" title="Share"
                                        @click.stop.prevent="qaShare($event, @js($shareUrl), @js($row->title))">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                    </div>
                @endforeach
            </div>


            <div class="mt-6 text-xs text-slate-500">
                আরও প্রশ্ন দেখতে “সকল প্রশ্ন” পেজে যান।
                <a class="underline font-semibold" href="{{ route('questions.index') }}">এখানে ক্লিক করুন</a>
            </div>
        </div>

    </div>

    <script>
        window.qaHomeState = function() {
            return {
                view: 'grid',
                q: @js($q),

                init() {
                    const saved = localStorage.getItem('qa_view_mode');
                    this.view = saved === 'list' ? 'list' : 'grid';
                },

                set(mode) {
                    this.view = mode === 'list' ? 'list' : 'grid';
                    localStorage.setItem('qa_view_mode', this.view);
                },

                clearSearch() {
                    this.q = '';
                    const url = new URL(window.location.href);
                    url.searchParams.delete('q');
                    window.history.replaceState({}, '', url.toString());
                    window.location.reload();
                },

                highlight(text) {
                    const raw = String(text ?? '');
                    const escapedText = raw
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;')
                        .replaceAll("'", '&#039;');

                    const query = (this.q || '').trim();
                    if (!query) return escapedText;

                    const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const re = new RegExp(escapedQuery, 'gi');
                    return escapedText.replace(re, (m) => `<mark class="rounded px-1">${m}</mark>`);
                }
            }
        }
    </script>


    {{-- Share button Script --}}
    <script>
        (function() {
            function toast(msg, type = 'success') {
                if (window.toast) {
                    window.toast({
                        title: type === 'success' ? '✅ Done' : '⚠️ Notice',
                        message: msg
                    });
                    return;
                }
                console.log(msg);
            }

            function stop(ev) {
                if (!ev) return;
                ev.preventDefault();
                ev.stopPropagation();
                if (typeof ev.stopImmediatePropagation === 'function') ev.stopImmediatePropagation();
            }

            // ✅ First try execCommand (no permission prompt usually)
            function copyByExecCommand(text) {
                try {
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    ta.setAttribute('readonly', '');
                    ta.style.position = 'fixed';
                    ta.style.top = '-9999px';
                    ta.style.left = '-9999px';
                    document.body.appendChild(ta);
                    ta.select();
                    ta.setSelectionRange(0, 999999);
                    const ok = document.execCommand('copy');
                    document.body.removeChild(ta);
                    return !!ok;
                } catch (e) {
                    return false;
                }
            }

            async function copyText(text) {
                // sync copy first
                if (copyByExecCommand(text)) return true;

                // fallback modern clipboard
                try {
                    if (navigator.clipboard) {
                        await navigator.clipboard.writeText(text);
                        return true;
                    }
                } catch (e) {}
                return false;
            }

            // ✅ Only runs on click (because you pass $event)
            window.qaShare = async function(ev, url, title) {
                stop(ev);

                // extra safety: if somehow called without user gesture, do nothing
                if (navigator.userActivation && !navigator.userActivation.isActive) return;

                const shareUrl = (url && String(url).trim()) ? String(url).trim() : window.location.href;
                const shareTitle = (title && String(title).trim()) ? String(title).trim() : document.title;

                // native share (mobile)
                try {
                    if (navigator.share) {
                        await navigator.share({
                            title: shareTitle,
                            url: shareUrl
                        });
                        return;
                    }
                } catch (e) {
                    // ignore and fallback to copy
                }

                const ok = await copyText(shareUrl);
                toast(ok ? 'লিংক কপি হয়েছে ✅' : 'কপি করা যায়নি (ব্রাউজার ব্লক করছে)।', ok ? 'success' : 'warn');
            };

            // ✅ social share dropdown helpers (optional)
            window.qaShareTo = function(platform, url, title, ev) {
                stop(ev);
                const u = encodeURIComponent(url || window.location.href);
                const t = encodeURIComponent(title || document.title);

                let share = '';
                if (platform === 'facebook') share = `https://www.facebook.com/sharer/sharer.php?u=${u}`;
                if (platform === 'whatsapp') share = `https://wa.me/?text=${t}%20${u}`;
                if (platform === 'telegram') share = `https://t.me/share/url?url=${u}&text=${t}`;
                if (!share) return;

                window.open(share, '_blank', 'noopener,noreferrer');
            };
        })();
    </script>
@endsection
