<header x-data="{ open: false }" class="bg-white/90 backdrop-blur border-b sticky top-0 z-50">
    <div class="qa-container py-3 flex items-center justify-between gap-4">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold text-slate-900">
            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-white text-sm">QA</span>
            <span class="tracking-tight">As Sunnah Q&amp;A</span>
        </a>

        {{-- Desktop Menu --}}
        <nav class="hidden md:flex items-center gap-6 text-sm font-semibold">
            @php
                $link = 'text-slate-700 hover:text-slate-900';
                $active = 'text-slate-900';
            @endphp

            <a class="{{ request()->routeIs('home') ? $active : $link }}" href="{{ route('home') }}">হোম</a>

            {{-- <a class="{{ request()->routeIs('answers.index') ? $active : $link }}" href="{{ route('answers.index') }}">
                প্রশ্নের উত্তর
            </a> --}}

            <a class="{{ request()->routeIs('ask') ? $active : $link }}" href="{{ route('ask') }}">প্রশ্ন করুন</a>

            <a class="{{ request()->routeIs('questions.*') ? $active : $link }}" href="{{ route('questions.index') }}">
                সকল প্রশ্ন
            </a>

            <a class="{{ request()->routeIs('about') ? $active : $link }}" href="{{ route('about') }}">
                আমাদের সম্পর্কে
            </a>


        </nav>

        {{-- Right / Desktop --}}
        <div class="hidden md:flex items-center gap-2">
            @auth
                @can('qa.view_admin')
                    <a href="{{ route('admin.dashboard') }}" class="qa-btn qa-btn-outline">Admin</a>
                @endcan

                <a href="{{ route('dashboard') }}" class="qa-btn qa-btn-outline">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="qa-btn qa-btn-outline">Log In</a>
            @endauth
        </div>

        {{-- Mobile button --}}
        <button @click="open = !open"
            class="md:hidden inline-flex items-center justify-center h-10 w-10 rounded-xl border border-slate-200 bg-white text-slate-800"
            aria-label="Open menu">
            <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Mobile panel --}}
    <div x-show="open" x-cloak class="md:hidden border-t bg-white">
        <div class="qa-container py-4 space-y-2 text-sm font-semibold">

            <a class="block rounded-xl px-3 py-2 {{ request()->routeIs('home') ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-50' }}"
                href="{{ route('home') }}">হোম</a>
            {{-- 
            <a class="block rounded-xl px-3 py-2 {{ request()->routeIs('answers.index') ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-50' }}"
                href="{{ route('answers.index') }}">প্রশ্নের উত্তর</a> --}}

            <a class="block rounded-xl px-3 py-2 {{ request()->routeIs('ask') ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-50' }}"
                href="{{ route('ask') }}">প্রশ্ন করুন</a>

            <a class="block rounded-xl px-3 py-2 {{ request()->routeIs('questions.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-50' }}"
                href="{{ route('questions.index') }}">সকল প্রশ্ন</a>

            <a class="block rounded-xl px-3 py-2 {{ request()->routeIs('about') ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-50' }}"
                href="{{ route('about') }}">আমাদের সম্পর্কে</a>


            <div class="pt-2 space-y-2">
                @auth
                    @can('qa.view_admin')
                        <a href="{{ route('admin.dashboard') }}" class="qa-btn qa-btn-outline w-full">Admin</a>
                    @endcan

                    <a href="{{ route('dashboard') }}" class="qa-btn qa-btn-outline w-full">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="qa-btn qa-btn-outline w-full">Log In</a>
                @endauth
            </div>
        </div>
    </div>
</header>
