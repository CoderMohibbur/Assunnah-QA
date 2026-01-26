<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
        @include('partials.nav')
        @include('partials.hero')

        <main class="qa-container py-8">
            <div class="mb-4 lg:hidden">
                <button type="button" @click="$store.drawer.toggleSidebar()"
                    class="qa-btn qa-btn-outline w-full justify-between">
                    <span class="font-extrabold">ক্যাটাগরি</span>
                    <span>☰</span>
                </button>
            </div>

            <div class="grid grid-cols-12 gap-6">
                @include('partials.sidebar')
                <section class="col-span-12 lg:col-span-9">
                    @yield('content')
                </section>
            </div>
        </main>
    @endif

    @include('partials.toast')
    @include('partials.footer')

    @stack('scripts')

    {{-- Share button Script --}}
    <script>
        window.qaShare = async function(url, title) {
            try {
                const shareUrl = url || window.location.href;
                const shareTitle = title || document.title;

                if (navigator.share) {
                    await navigator.share({
                        title: shareTitle,
                        url: shareUrl
                    });
                    return;
                }

                // fallback: copy link
                await navigator.clipboard.writeText(shareUrl);
                alert('লিংক কপি হয়েছে ✅');
            } catch (e) {
                // last fallback
                try {
                    prompt('এই লিংক কপি করুন:', url || window.location.href);
                } catch (e2) {}
            }
        }
    </script>

</body>


</html>
