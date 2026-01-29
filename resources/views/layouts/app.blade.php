<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <title>@yield('title', 'As Sunnah Question & Answer')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Hind Siliguri Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap"
        rel="stylesheet">


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- Styles -->
    @livewireStyles
    @stack('styles')

</head>

<body x-data class="bg-slate-100 text-slate-900 font-sans">

    @if (request()->routeIs('admin.*'))
        @include('partials.admin_nav')

        <main class="qa-container py-8">
            @yield('content')
        </main>
    @else
        @php
            // /ask বা /ask/* হলে sidebar hide
            $showSidebar = !request()->is('ask') && !request()->is('ask/*');

            // আপনি যদি named route use করেন, চাইলে এভাবে আরও strong করতে পারেন:
            // $showSidebar = !request()->routeIs('ask') && !request()->routeIs('ask.*') && !request()->is('ask') && !request()->is('ask/*');

        @endphp

        @include('partials.nav')
        @include('partials.hero')

        <main class="qa-container py-8">

            {{-- Mobile category button only when sidebar is visible --}}
            @if ($showSidebar)
                <div class="mb-4 lg:hidden">
                    <button type="button" @click="$store.drawer.toggleSidebar()"
                        class="qa-btn qa-btn-outline w-full justify-between">
                        <span class="font-extrabold">ক্যাটাগরি</span>
                        <span>☰</span>
                    </button>
                </div>
            @endif

            <div class="grid grid-cols-12 gap-6">
                @if ($showSidebar)
                    @include('partials.sidebar')
                    <section class="col-span-12 lg:col-span-9">
                        @yield('content')
                    </section>
                @else
                    {{-- /ask page: full width content --}}
                    <section class="col-span-12">
                        @yield('content')
                    </section>
                @endif
            </div>
        </main>

    @endif

    @include('partials.toast')
    @include('partials.footer')

    @stack('scripts')


</body>


</html>
