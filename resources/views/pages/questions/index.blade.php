@extends('layouts.app')

@section('title', 'সকল প্রশ্ন')

@section('content')
    @php
        use Carbon\Carbon;
        use Illuminate\Support\Str;

        $qText = trim((string) ($q ?? request('q', '')));
        $catId = (string) ($categoryId ?? request('category_id', ''));
        $sortVal = (string) ($sort ?? request('sort', 'newest'));
        $answeredVal = (string) ($answered ?? request('answered', ''));

        // Bengali digit helper
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

        // Date label helper (safe)
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
                    return $bn($c->format('Y-m-d'));
                } catch (\Throwable $e2) {
                    return '—';
                }
            }
        };
    @endphp

    <div x-data="qaQuestionsPage(@js($qText))" x-init="init()">

        {{-- Top Search --}}
        <div class="qa-card p-3">
            <form method="GET" action="{{ route('questions.index') }}" class="flex gap-3 items-center">
                <input class="qa-input flex-1" name="q" x-model="q" placeholder="প্রশ্ন খুঁজুন..."
                    autocomplete="off" />

                <button type="button" x-show="q?.length" x-cloak
                    @click="q=''; $nextTick(()=> $el.closest('form').submit())"
                    class="qa-btn qa-btn-outline px-4">✕</button>

                <button type="submit" class="qa-btn qa-btn-primary px-5">🔍</button>

                {{-- keep filters --}}
                <input type="hidden" name="category_id" value="{{ $catId }}">
                <input type="hidden" name="sort" value="{{ $sortVal }}">
                @if ($answeredVal === '1')
                    <input type="hidden" name="answered" value="1">
                @endif
            </form>
        </div>

        {{-- Filters + Toggle --}}
        <div class="mt-6 qa-card">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4 lg:justify-between">
                <div>
                    <div class="font-extrabold text-slate-900 text-lg">প্রশ্ন তালিকা</div>
                    <div class="text-sm text-slate-600">ক্যাটাগরি/সোর্ট ব্যবহার করে সহজে খুঁজুন</div>
                </div>

                <form method="GET" action="{{ route('questions.index') }}" class="flex flex-col sm:flex-row gap-3">
                    <input type="hidden" name="q" value="{{ $qText }}" />

                    {{-- Category --}}
                    <select class="qa-input sm:w-[220px]" name="category_id" onchange="this.form.submit()">
                        <option value="">সকল ক্যাটাগরি</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected((string) $catId === (string) $c->id)>{{ $c->name_bn }}</option>
                        @endforeach
                    </select>

                    {{-- Sort --}}
                    <select class="qa-input sm:w-[220px]" name="sort" onchange="this.form.submit()">
                        <option value="newest" @selected($sortVal === 'newest')>সর্বশেষ (Newest)</option>
                        <option value="oldest" @selected($sortVal === 'oldest')>পুরাতন (Oldest)</option>
                        <option value="views" @selected($sortVal === 'views')>সবচেয়ে দেখা (Most Viewed)</option>
                    </select>

                    {{-- Answered only --}}
                    <label
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl border border-slate-200 bg-white text-sm text-slate-700">
                        <input type="checkbox" class="rounded" name="answered" value="1" onchange="this.form.submit()"
                            @checked($answeredVal === '1') />
                        শুধু উত্তর আছে
                    </label>

                    {{-- Grid/List toggle --}}
                    <div class="flex items-center gap-2">
                        <button type="button" @click="set('grid')"
                            :class="view === 'grid' ? 'qa-btn qa-btn-primary px-3' : 'qa-btn qa-btn-outline px-3'">⬛⬛</button>

                        <button type="button" @click="set('list')"
                            :class="view === 'list' ? 'qa-btn qa-btn-primary px-3' : 'qa-btn qa-btn-outline px-3'">☰</button>
                    </div>
                </form>
            </div>

            {{-- Empty State --}}
            @if ($questions->count() === 0)
                <div class="mt-6 qa-card text-center">
                    <div class="text-lg font-extrabold text-slate-900">কোন প্রশ্ন পাওয়া যায়নি</div>
                    <div class="mt-1 text-sm text-slate-600">অন্য শব্দ দিয়ে সার্চ করুন অথবা নতুন প্রশ্ন করুন।</div>
                    <div class="mt-4">
                        <a href="{{ route('ask') }}" class="qa-btn qa-btn-primary">প্রশ্ন করুন</a>
                    </div>
                </div>
            @endif

            {{-- List/Grid --}}
            <div class="mt-6" :class="view === 'grid' ? 'grid sm:grid-cols-2 lg:grid-cols-3 gap-4' : 'space-y-4'">

                @foreach ($questions as $row)
                    @php
                        // ✅ MUST: slug + shareUrl
                        $slug = $row->slug ?: 'q-' . $row->id;
                        $shareUrl = route('questions.show', ['slug' => $slug]);

                        $ans = $row->answer;
                        $answeredBy = $ans?->answeredBy?->name ?? 'মডারেটর';

                        $dateValue =
                            $ans?->answered_at ?? ($ans?->updated_at ?? ($row->published_at ?? $row->created_at));
                        $dateLabel = $bnDateLabel($dateValue);

                        $excerpt = Str::limit(strip_tags((string) ($ans?->answer_html ?? $row->body_html)), 120);
                        $snippet = Str::limit(strip_tags((string) $row->body_html), 130);
                    @endphp

                    <a href="{{ $shareUrl }}" class="block qa-card qa-card-hover"
                        :class="view === 'list' ? 'p-4' : ''">

                        {{-- GRID VIEW --}}
                        <template x-if="view==='grid'">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <span class="qa-badge">{{ $row->category?->name_bn ?? 'Uncategorized' }}</span>
                                    <span class="text-xs text-slate-500">{{ $dateLabel }}</span>
                                </div>

                                <div class="mt-3 text-center">
                                    <div class="text-sm text-slate-600">প্রশ্ন:</div>
                                    <div class="text-3xl font-extrabold text-slate-900">{{ $bn($row->id) }}</div>
                                </div>

                                <div class="mt-3 text-sm font-semibold text-slate-800"
                                    x-html="highlight(@js($row->title))"></div>
                                <div class="mt-2 text-sm text-slate-600" x-html="highlight(@js($excerpt))">
                                </div>

                                <div class="mt-4 border-t pt-3 text-xs text-slate-500">
                                    <div>উত্তর দিয়েছেন: <span
                                            class="font-semibold text-slate-700">{{ $answeredBy }}</span></div>
                                    <div class="mt-1">তারিখ: {{ $dateLabel }}</div>
                                </div>

                                <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                    <span class="qa-btn qa-btn-outline px-3 py-1">বিস্তারিত পড়ুন →</span>

                                    <button type="button" class="qa-btn qa-btn-outline qa-share-btn" title="Share"
                                        @click.prevent.stop="qaShare(@js($shareUrl), @js($row->title))"><svg
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
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
                                <div class="sm:w-52 shrink-0">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xs text-slate-500">প্রশ্ন</span>
                                        <span class="text-2xl font-extrabold text-slate-900">{{ $bn($row->id) }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $dateLabel }}</div>
                                    <div class="mt-2">
                                        <span class="qa-badge">{{ $row->category?->name_bn ?? 'Uncategorized' }}</span>
                                    </div>
                                </div>

                                <div class="flex-1">
                                    <div class="text-sm sm:text-base font-extrabold text-slate-900 leading-snug"
                                        x-html="highlight(@js($row->title))"></div>

                                    <div class="mt-1 text-sm text-slate-600 line-clamp-2"
                                        x-html="highlight(@js($snippet))"></div>

                                    <div class="mt-2 text-xs text-slate-500">
                                        উত্তর দিয়েছেন: <span
                                            class="font-semibold text-slate-700">{{ $answeredBy }}</span> •
                                        {{ $dateLabel }}
                                    </div>
                                </div>

                                <div class="sm:w-40 shrink-0 sm:text-right flex items-center justify-end gap-2">
                                    <span class="qa-btn qa-btn-outline px-4">বিস্তারিত →</span>

                                    <button type="button" class="qa-btn qa-btn-outline qa-share-btn" title="Share"
                                        @click.prevent.stop="qaShare(@js($shareUrl), @js($row->title))"><svg
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $questions->links() }}
            </div>
        </div>
    </div>

    <script>
        window.qaQuestionsPage = function(initialQ) {
            return {
                view: 'grid',
                q: (initialQ || '').trim(),

                init() {
                    const saved = localStorage.getItem('qa_view_mode');
                    this.view = saved === 'list' ? 'list' : 'grid';
                },

                set(mode) {
                    this.view = mode === 'list' ? 'list' : 'grid';
                    localStorage.setItem('qa_view_mode', this.view);
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
@endsection
