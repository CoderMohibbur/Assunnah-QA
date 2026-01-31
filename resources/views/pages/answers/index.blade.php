@extends('layouts.app')

@section('title', 'প্রশ্নোত্তর')

@section('content')
    @php
        use Carbon\Carbon;
        use Illuminate\Support\Str;

        $qText = trim((string) ($q ?? request('q', '')));
        $catVal = (string) ($cat ?? request('cat', ''));
        $sortVal = (string) ($sort ?? request('sort', 'newest'));

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

        // Bengali date label helper
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

    <div x-data="qaAnswersPage(@js($qText))" x-init="init()">

        {{-- Top Search --}}
        <div class="qa-card p-3">
            <form method="GET" action="{{ route('answers.index') }}" class="flex gap-3 items-center">
                <input class="qa-input flex-1" name="q" x-model="q" placeholder="উত্তর খুঁজুন..." autocomplete="off" />

                <button type="button" x-show="q?.length" x-cloak
                    @click="q=''; $nextTick(()=> $el.closest('form').submit())"
                    class="qa-btn qa-btn-outline px-4">✕</button>

                <button type="submit" class="qa-btn qa-btn-primary px-5">🔍</button>

                <input type="hidden" name="cat" value="{{ $catVal }}">
                <input type="hidden" name="sort" value="{{ $sortVal }}">
            </form>
        </div>

        {{-- Filters + Toggle --}}
        <div class="mt-6 qa-card">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4 lg:justify-between">
                <div>
                    <div class="font-extrabold text-slate-900 text-lg">প্রশ্নোত্তর</div>
                    <div class="text-sm text-slate-600">
                        প্রকাশিত উত্তরগুলো এখানে পাবেন
                        <span class="ml-2 text-xs text-slate-500">({{ $answers->total() }} টি)</span>
                    </div>
                </div>

                <form method="GET" action="{{ route('answers.index') }}" class="flex flex-col sm:flex-row gap-3">
                    <input type="hidden" name="q" value="{{ $qText }}" />

                    {{-- Category (slug) --}}
                    <select class="qa-input sm:w-[220px]" name="cat" onchange="this.form.submit()">
                        <option value="">সকল ক্যাটাগরি</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->slug }}" @selected((string) $catVal === (string) $c->slug)>{{ $c->name_bn }}</option>
                        @endforeach
                    </select>

                    {{-- Sort --}}
                    <select class="qa-input sm:w-[220px]" name="sort" onchange="this.form.submit()">
                        <option value="newest" @selected($sortVal === 'newest')>সর্বশেষ (Newest)</option>
                        <option value="oldest" @selected($sortVal === 'oldest')>পুরাতন (Oldest)</option>
                        <option value="views" @selected($sortVal === 'views')>জনপ্রিয় (Most Viewed)</option>
                    </select>

                    {{-- Grid/List --}}
                    <div class="flex items-center gap-2">
                        <button type="button" @click="set('grid')"
                            :class="view === 'grid' ? 'qa-btn qa-btn-primary px-3' : 'qa-btn qa-btn-outline px-3'">⬛⬛</button>

                        <button type="button" @click="set('list')"
                            :class="view === 'list' ? 'qa-btn qa-btn-primary px-3' : 'qa-btn qa-btn-outline px-3'">☰</button>
                    </div>
                </form>
            </div>

            {{-- Empty --}}
            @if ($answers->count() === 0)
                <div class="mt-6 qa-card text-center">
                    <div class="text-lg font-extrabold text-slate-900">কোন উত্তর পাওয়া যায়নি</div>
                    <div class="mt-1 text-sm text-slate-600">অন্য শব্দ দিয়ে সার্চ করুন অথবা নতুন প্রশ্ন করুন।</div>
                    <div class="mt-4 flex justify-center gap-2">
                        <a href="{{ route('questions.index') }}" class="qa-btn qa-btn-outline">সব প্রশ্ন</a>
                        <a href="{{ route('ask') }}" class="qa-btn qa-btn-primary">প্রশ্ন করুন</a>
                    </div>
                </div>
            @endif

            {{-- List/Grid --}}
            <div class="mt-6" :class="view === 'grid' ? 'grid sm:grid-cols-2 lg:grid-cols-3 gap-4' : 'space-y-4'">
                @foreach ($answers as $row)
                    @php
                        $ans = $row->answer;

                        $slug = $row->slug ?: 'q-' . $row->id;
                        $shareUrl = route('questions.show', ['slug' => $slug]);

                        $answeredBy = $ans?->answeredBy?->name ?? 'মডারেটর';
                        $answeredAt =
                            $ans?->answered_at ?? ($ans?->updated_at ?? ($row->published_at ?? $row->created_at));
                        $dateLabel = $bnDateLabel($answeredAt);

                        $excerpt = Str::limit(strip_tags((string) ($ans?->answer_html ?? $row->body_html)), 120);
                        $snippet = $excerpt; // ✅ list view uses it

                        $askerName = $row->asker_name ?? null;
                        $askerPhone = $row->asker_phone ?? null; // ✅ correct column
                        $askerEmail = $row->asker_email ?? null;
                        $askedAtLabel = $bnDateLabel($row->created_at);

                    @endphp

                    {{-- ✅ Clickable Card --}}
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
                                    <div class="text-3xl font-extrabold text-slate-900">
                                        {{ $bn($row->published_serial ?? $row->id) }}</div>
                                </div>

                                <div class="mt-3 text-sm font-semibold text-slate-800"
                                    x-html="highlight(@js($row->title))"></div>

                                <div class="mt-2 text-sm text-slate-600" x-html="highlight(@js($excerpt))">
                                </div>

                                <div class="mt-4 border-t pt-3 text-xs text-slate-500">
                                    <div>উত্তর দিয়েছেন:
                                        <span class="font-semibold text-slate-700">{{ $answeredBy }}</span>
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
                                                class="font-semibold">{{ $askerPhone ?? '—' }}</span></div>

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
                                <div class="sm:w-40 shrink-0">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xs text-slate-500">প্রশ্ন</span>
                                        <span
                                            class="text-2xl font-extrabold text-slate-900">{{ $bn($row->published_serial ?? $row->id) }}</span>
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $dateLabel }}</div>
                                </div>

                                <div class="flex-1">
                                    <div class="text-sm sm:text-base font-extrabold text-slate-900 leading-snug"
                                        x-html="highlight(@js($row->title))"></div>

                                    <div class="mt-1 text-sm text-slate-600 line-clamp-2"
                                        x-html="highlight(@js($snippet))"></div>
                                </div>


                                @if ($canSeeAsker)
                                    <div
                                        class="mt-3 sm:mt-0 rounded-lg border border-gray-300 bg-white p-3 text-xs text-slate-700">
                                        <div class="font-extrabold text-amber-800 mb-1">🔒 Admin Only — প্রশ্নকারী তথ্য
                                        </div>

                                        <div>নাম: <span class="font-semibold">{{ $askerName ?? '—' }}</span></div>
                                        <div class="mt-1">মোবাইল: <span
                                                class="font-semibold">{{ $askerPhone ?? '—' }}</span></div>

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

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $answers->links() }}
            </div>
        </div>
    </div>

    {{-- Page Script --}}
    <script>
        window.qaAnswersPage = function(initialQ) {
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
