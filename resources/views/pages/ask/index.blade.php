@extends('layouts.app')

@section('title', 'প্রশ্ন করুন')

@section('content')
    <div x-data="qaAskState({
        endpoint: @js(route('qa.suggest')),
        minChars: @js((int) config('qa_suggest.min_chars', 4)),
        toastCooldown: @js((int) config('qa_suggest.toast_cooldown', 12)),
    })" x-init="init()">
        {{-- x-cloak helper (layout এ না থাকলে এখানেই কাজ করবে) --}}
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        {{-- Header --}}
        <div class="qa-card">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="font-extrabold text-slate-900 text-xl">প্রশ্ন করুন</div>
                    <div class="mt-1 text-sm text-slate-600">
                        প্রশ্ন সাবমিট করলে তা <span class="font-semibold text-slate-800">Pending</span> থাকবে। উত্তর প্রকাশ
                        হলে আপনাকে জানানো হবে।
                    </div>

                    @if (session('status'))
                        <div class="mt-3 text-sm font-semibold text-green-700">
                            ✅ {{ session('status') }}
                        </div>
                    @endif
                </div>

                <a href="{{ route('questions.index') }}" class="qa-btn qa-btn-outline">সব প্রশ্ন দেখুন</a>
            </div>
        </div>
        {{-- Quick Search Bar (Search first) --}}
        <div class="mt-6">
            <div class="qa-card p-3">
                <form method="GET" action="{{ route('questions.index') }}" class="flex gap-3 items-center"
                    @submit="if(!(searchQ||'').trim()){ $event.preventDefault(); }">
                    <input class="qa-input flex-1" name="q" x-model="searchQ"
                        @input.debounce.350ms="fetchSearchSuggestions()" @keydown.escape.window="clearSearchBar()"
                        placeholder="আগে সার্চ করুন... (যেমন: নামাজ, রোজা, আকিদা)" autocomplete="off" />


                    <button type="button" x-show="searchQ?.length" x-cloak @click="clearSearchBar()"
                        class="qa-btn qa-btn-outline px-4">✕</button>

                    <button type="submit" class="qa-btn qa-btn-primary px-5">🔍</button>
                </form>
            </div>

            {{-- Live suggestions for Quick Search --}}
            <div class="mt-3" x-show="searchQ.trim().length >= minChars" x-cloak>
                <div class="qa-card p-3 border border-slate-200 bg-white/80">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-extrabold text-slate-900">সাজেশন</div>

                        <div class="text-xs text-slate-500 flex items-center gap-2" x-show="searchLoading">
                            <span class="inline-block h-2 w-2 rounded-full bg-cyan-500 animate-pulse"></span>
                            লোড হচ্ছে...
                        </div>
                    </div>

                    <div class="mt-3 space-y-2" x-show="searchSuggestions.length">
                        <template x-for="s in searchSuggestions" :key="'sq-' + s.id">
                            <a :href="s.url"
                                class="block rounded-xl border border-slate-200 bg-white p-3 hover:border-slate-300 hover:shadow-sm transition">
                                <div class="font-bold text-slate-900 text-sm" x-html="highlightSearchSafe(s.title)"></div>
                                <div class="mt-1 text-xs text-slate-600">
                                    ক্লিক করলে প্রশ্নটি ওপেন হবে →
                                    <span class="font-semibold text-slate-700"
                                        x-text="Math.round((s.confidence || 0) * 100) + '%'"></span>
                                </div>
                            </a>
                        </template>
                    </div>

                    <div class="mt-3 text-xs text-slate-600"
                        x-show="!searchLoading && !searchSuggestions.length && !searchRateLimited">
                        কোনো মিল পাওয়া যায়নি।
                    </div>

                    <div class="mt-3 text-xs text-amber-700" x-show="searchRateLimited" x-cloak>
                        অনেক দ্রুত টাইপ করলে লিমিট হতে পারে। ২–৩ সেকেন্ড পর আবার চেষ্টা করুন।
                    </div>
                </div>
            </div>

        </div>

        {{-- Important hint / search-first --}}
        <div class="mt-6 qa-card border border-cyan-200 bg-cyan-50/60">
            <div class="flex items-start gap-3">
                <div class="text-xl">💡</div>
                <div class="min-w-0">
                    <div class="font-extrabold text-slate-900">আগে সার্চ করুন</div>
                    <div class="mt-1 text-sm text-slate-700">
                        আপনার প্রশ্নটি আগে করা আছে কিনা দেখুন। শিরোনাম লিখতে থাকলে মিল পাওয়া প্রশ্ন এখানে দেখাবে।
                    </div>
                </div>
            </div>
        </div>

        {{-- Suggestions box --}}
        {{-- Suggestions box --}}
        <div x-show="title.trim().length >= minChars" x-cloak class="mt-4 qa-card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="font-extrabold text-slate-900">মিল পাওয়া প্রশ্ন</div>
                    <div class="mt-1 text-xs text-slate-500">
                        সবচেয়ে কাছাকাছি প্রশ্নগুলো দেখানো হচ্ছে — আগে দেখে নিন।
                    </div>
                </div>

                <div class="text-xs text-slate-500 flex items-center gap-2" x-show="loading">
                    <span class="inline-block h-2 w-2 rounded-full bg-cyan-500 animate-pulse"></span>
                    লোড হচ্ছে...
                </div>
            </div>

            <div class="mt-4 space-y-3" x-show="suggestions.length">
                <template x-for="s in suggestions" :key="s.id">
                    <a :href="s.url"
                        class="group block rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm hover:shadow-md hover:border-slate-300 transition">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-[15px] sm:text-base font-extrabold text-slate-900 leading-snug line-clamp-2"
                                    x-html="highlightSafe(s.title)"></div>

                                <div class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed">
                                    এই প্রশ্নটি ইতিমধ্যে করা হয়েছে — ক্লিক করে বিস্তারিত দেখুন।
                                    <span class="font-semibold text-slate-700">সন্তুষ্ট না হলে নতুন করে প্রশ্ন করুন।</span>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex items-center rounded-full bg-cyan-50 text-cyan-700 border border-cyan-100 px-2.5 py-1 text-[11px] font-bold">
                                        মিল: <span class="ml-1"
                                            x-text="Math.round((s.confidence || 0) * 100) + '%'"></span>
                                    </span>

                                    <span
                                        class="inline-flex items-center rounded-full bg-slate-50 text-slate-600 border border-slate-200 px-2.5 py-1 text-[11px] font-bold">
                                        বিস্তারিত দেখুন →
                                    </span>
                                </div>
                            </div>

                            <div class="shrink-0 text-[11px] text-slate-500 mt-0.5" x-text="s.date || ''"></div>
                        </div>
                    </a>
                </template>
            </div>

            <div class="mt-4 text-sm text-slate-600" x-show="!loading && !suggestions.length && !rateLimited">
                কোনো মিল পাওয়া যায়নি — নিচের ফর্ম দিয়ে প্রশ্ন সাবমিট করুন।
            </div>

            <div class="mt-4 text-sm text-amber-700" x-show="rateLimited" x-cloak>
                অনেক দ্রুত টাইপ করলে লিমিট হতে পারে। ২–৩ সেকেন্ড পর আবার চেষ্টা করুন।
            </div>
        </div>


        {{-- Form errors --}}
        @if ($errors->any())
            <div class="qa-card border border-red-200 bg-red-50/60 mb-6">
                <div class="font-extrabold text-red-700">ফর্মে কিছু সমস্যা আছে:</div>
                <ul class="mt-2 text-sm text-red-700 list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <div class="mt-6 qa-card">
            <form method="POST" action="{{ route('ask.store') }}" class="space-y-4">
                @csrf

                {{-- Honeypot --}}
                <input type="text" name="website" value="" autocomplete="off" tabindex="-1" class="hidden"
                    aria-hidden="true">

                {{-- Time-check token --}}
                <input type="hidden" name="form_started_at" value="{{ now()->timestamp }}">

                {{-- Name / Phone / Email --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-800">নাম <span class="text-red-600">*</span></label>
                        <input type="text" name="name" required class="qa-input mt-2" value="{{ old('name') }}"
                            placeholder="আপনার নাম লিখুন">
                        @error('name')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold text-slate-800">মোবাইল নম্বর <span
                                class="text-red-600">*</span></label>
                        <input type="text" name="phone" required class="qa-input mt-2" value="{{ old('phone') }}"
                            placeholder="01XXXXXXXXX">
                        @error('phone')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-bold text-slate-800">ইমেইল (Optional)</label>
                        <input type="email" name="email" class="qa-input mt-2" value="{{ old('email') }}"
                            placeholder="example@email.com">
                        @error('email')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Category --}}
                <div class="mt-4">
                    <label class="text-sm font-bold text-slate-800">ক্যাটাগরি <span class="text-red-600">*</span></label>

                    <select name="category_id" required class="qa-input mt-2 w-full">
                        <option value="">-- ক্যাটাগরি নির্বাচন করুন --</option>

                        @forelse ($categories ?? [] as $c)
                            <option value="{{ $c->id }}" @selected((string) old('category_id') === (string) $c->id)>
                                {{ $c->name_bn }}
                            </option>
                        @empty
                            <option value="" disabled>কোনো Active category নেই</option>
                        @endforelse
                    </select>

                    @error('category_id')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Title --}}
                <div>
                    <label class="text-sm font-bold text-slate-800">প্রশ্নের শিরোনাম <span
                            class="text-red-600">*</span></label>

                    <div class="mt-2 flex gap-2 items-center">
                        <input type="text" name="title" required class="qa-input mt-2" x-model="title"
                            @input.debounce.700ms="fetchSuggestions()" @keydown.escape.window="clearTitle()"
                            placeholder="সংক্ষেপে আপনার প্রশ্ন লিখুন...">

                        <button type="button" class="qa-btn qa-btn-outline px-4" x-show="title?.length" x-cloak
                            @click="clearTitle()">✕</button>
                    </div>

                    @error('title')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Body --}}
                <div>
                    <label class="text-sm font-bold text-slate-800">প্রশ্ন বিস্তারিত <span
                            class="text-red-600">*</span></label>
                    <textarea name="body" required rows="7" class="qa-input mt-2"
                        placeholder="আপনার প্রশ্ন বিস্তারিত লিখুন...">{{ old('body') }}</textarea>
                    @error('body')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between pt-2">
                    <div class="text-xs text-slate-500">
                        সাবমিট করলে প্রশ্নটি <span class="font-semibold text-slate-700">Pending</span> থাকবে, মডারেটর রিভিউ
                        করবে।
                    </div>

                    <div class="flex gap-2">
                        <button type="reset" class="qa-btn qa-btn-outline" @click="resetLocal()">রিসেট</button>
                        <button type="submit" class="qa-btn qa-btn-primary px-6">প্রশ্ন সাবমিট করুন</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Page Script --}}
    <script>
        window.qaAskState = function(opts = {}) {
            return {
                // config
                endpoint: opts.endpoint || '',
                minChars: Number(opts.minChars || 4),
                toastCooldown: Number(opts.toastCooldown || 12),

                // state
                searchQ: '',
                title: '',
                suggestions: [],
                loading: false,
                rateLimited: false,

                searchSuggestions: [],
                searchLoading: false,
                searchRateLimited: false,
                searchLastQuery: '',
                searchController: null,


                lastQuery: '',
                controller: null,

                lastToastSlug: '',
                lastToastAt: 0,

                init() {
                    this.searchQ = @js(trim((string) request('q', '')));
                    this.title = @js(old('title', ''));
                    if ((this.title || '').trim().length >= this.minChars) {
                        this.fetchSuggestions(); // ✅ validation error হলে suggestions auto দেখাবে
                    }
                },

                clearSearchBar() {
                    this.searchQ = '';
                    this.searchSuggestions = [];
                    this.searchLoading = false;
                    this.searchRateLimited = false;
                    this.searchLastQuery = '';

                    if (this.searchController) this.searchController.abort();
                    this.searchController = null;

                    const url = new URL(window.location.href);
                    url.searchParams.delete('q');
                    window.history.replaceState({}, '', url.toString());
                },


                async fetchSearchSuggestions() {
                    const t = (this.searchQ || '').trim();

                    this.searchRateLimited = false;

                    if (t.length < this.minChars) {
                        this.searchSuggestions = [];
                        this.searchLoading = false;
                        this.searchLastQuery = '';
                        return;
                    }

                    if (t === this.searchLastQuery) return;
                    this.searchLastQuery = t;

                    if (this.searchController) this.searchController.abort();
                    this.searchController = new AbortController();

                    this.searchLoading = true;

                    try {
                        const res = await fetch(this.endpoint + '?title=' + encodeURIComponent(t), {
                            headers: {
                                'Accept': 'application/json'
                            },
                            signal: this.searchController.signal
                        });

                        if (!res.ok) {
                            if (res.status === 429) this.searchRateLimited = true;
                            throw new Error('Suggest API failed: ' + res.status);
                        }

                        const json = await res.json();
                        this.searchSuggestions = json.data || [];
                    } catch (e) {
                        // Abort হলে ignore
                    } finally {
                        this.searchLoading = false;
                    }
                },



                async fetchSuggestions() {
                    const t = (this.title || '').trim();

                    // reset UI
                    this.rateLimited = false;

                    if (t.length < this.minChars) {
                        this.suggestions = [];
                        this.loading = false;
                        this.lastQuery = '';
                        return;
                    }

                    // same query => skip
                    if (t === this.lastQuery) return;
                    this.lastQuery = t;

                    // abort previous
                    if (this.controller) this.controller.abort();
                    this.controller = new AbortController();

                    this.loading = true;

                    try {
                        const res = await fetch(this.endpoint + '?title=' + encodeURIComponent(t), {
                            headers: {
                                'Accept': 'application/json'
                            },
                            signal: this.controller.signal
                        });

                        if (!res.ok) {
                            if (res.status === 429) this.rateLimited = true;
                            throw new Error('Suggest API failed: ' + res.status);
                        }

                        const json = await res.json();
                        this.suggestions = json.data || [];

                        // toast (top confident match)
                        if (this.suggestions.length) {
                            const top = this.suggestions[0];
                            const now = Date.now();

                            const okConfidence = Number(top.confidence || 0) >= 0.60;
                            const cooldownOk = (now - this.lastToastAt) >= (this.toastCooldown * 1000);
                            const notSame = top.slug && top.slug !== this.lastToastSlug;

                            if (okConfidence && cooldownOk && notSame) {
                                this.lastToastSlug = top.slug;
                                this.lastToastAt = now;

                                if (window.toast) {
                                    const conf = Number(top.confidence ?? (Number(top.score || 0) / 100) ??
                                        0); // 0-1
                                    const pct = Math.round(conf * 100);

                                    window.toast({
                                        title: `মিলে গেছে (${pct}%)`,
                                        message: `এই প্রশ্নটি ইতিমধ্যে করা আছে — ক্লিক করে দেখুন। উত্তর পছন্দ না হলে আবার নতুন করে প্রশ্ন করুন। (মিল: ${pct}%)`,
                                        link: top.url
                                    });
                                }

                            }
                        }
                    } catch (e) {
                        // Abort হলে ignore
                    } finally {
                        this.loading = false;
                    }
                },

                clearTitle() {
                    this.title = '';
                    this.suggestions = [];
                    this.loading = false;
                    this.rateLimited = false;
                    this.lastQuery = '';
                    if (this.controller) this.controller.abort();
                    this.controller = null;
                },

                resetLocal() {
                    this.clearTitle();
                },

                escapeHtml(str) {
                    return String(str ?? '')
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;')
                        .replaceAll("'", '&#039;');
                },

                highlightSafe(text) {
                    const q = (this.title || '').trim();
                    const safeText = this.escapeHtml(text);

                    if (q.length < this.minChars) return safeText;

                    const escapedQuery = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const re = new RegExp(escapedQuery, 'gi');

                    return safeText.replace(re, (m) =>
                        `<mark class="rounded px-1 bg-yellow-200/80">${this.escapeHtml(m)}</mark>`
                    );
                },

                highlightSearchSafe(text) {
                    const q = (this.searchQ || '').trim();
                    const safeText = this.escapeHtml(text);

                    if (q.length < this.minChars) return safeText;

                    const escapedQuery = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const re = new RegExp(escapedQuery, 'gi');

                    return safeText.replace(re, (m) =>
                        `<mark class="rounded px-1 bg-yellow-200/80">${this.escapeHtml(m)}</mark>`
                    );
                },

            }
        }
    </script>
@endsection
