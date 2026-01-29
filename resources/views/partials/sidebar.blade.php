@php
  use Illuminate\Support\Str;

  // =========================
  // Categories Source
  // =========================
  $rawCategories = $categories ?? [
    ['name' => 'আকিদা', 'slug' => 'aqidah'],
    ['name' => 'সালাত', 'slug' => 'salah'],
    ['name' => 'রোযা', 'slug' => 'sawm'],
    ['name' => 'যাকাত', 'slug' => 'zakat'],
    ['name' => 'হজ', 'slug' => 'hajj'],
    ['name' => 'আদব', 'slug' => 'adab'],
  ];

  /**
   * ✅ Normalize categories (DB Model/Collection/Array সব handle)
   */
  $categories = collect($rawCategories)->map(function ($c) {
      // Eloquent Model / object support
      if (is_object($c)) {
          $name = data_get($c, 'name_bn')
               ?? data_get($c, 'name')
               ?? data_get($c, 'title')
               ?? '';
          $slug = data_get($c, 'slug') ?? '';
      }
      // array support
      elseif (is_array($c)) {
          $name = $c['name'] ?? $c['name_bn'] ?? $c['title'] ?? '';
          $slug = $c['slug'] ?? '';
      }
      // string support
      else {
          $name = (string) $c;
          $slug = '';
      }

      $name = trim((string) $name);
      $slug = trim((string) $slug);

      // slug না থাকলে auto বানাবে (Bangla হলে fallback urlencode)
      if ($slug === '') {
          $slug = Str::slug($name, '-');
          if ($slug === '') $slug = rawurlencode($name);
      }

      return [
          'name' => $name,
          'slug' => $slug,
      ];
  })->filter(fn($x) => $x['name'] !== '')->values()->all();

  // active detect
  $activeSlug = (string) (request()->route('slug') ?? request()->segment(2) ?? '');
  $isCategoriesPage = request()->routeIs('categories.show') || request()->is('categories/*');
@endphp

{{-- =========================
   Desktop Sidebar
   ========================= --}}
<aside class="hidden lg:block col-span-12 lg:col-span-3">
  <div class="lg:sticky lg:top-6 space-y-4">
    <div class="qa-card">
      <div class="flex items-center justify-between">
        <div class="font-extrabold text-slate-900">ক্যাটাগরি</div>
        <span class="qa-badge">Filter</span>
      </div>

      <div class="mt-4 space-y-2 max-h-[60vh] overflow-y-auto pr-1">
        @foreach($categories as $c)
          @php
            $url = route('categories.show', $c['slug']);
            $active = $isCategoriesPage && $activeSlug === $c['slug'];
          @endphp

          <a href="{{ $url }}"
             class="flex items-center justify-between rounded-2xl border px-3 py-2 text-sm font-semibold transition
                    {{ $active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-800 border-slate-200 hover:bg-slate-50' }}">
            <span>{{ $c['name'] }}</span>
            <span class="text-xs opacity-80">→</span>
          </a>
        @endforeach
      </div>

      {{-- <div class="mt-4 text-xs text-slate-500">
        মোবাইলে ক্যাটাগরি বাটনে ক্লিক করে drawer খুলুন।
      </div> --}}
    </div>

    <div class="qa-card">
      <div class="font-extrabold text-slate-900">দ্রুত লিংক</div>
      <div class="mt-3 space-y-2 text-sm">
        <a class="block hover:underline" href="{{ route('home') }}">হোম</a>
        <a class="block hover:underline" href="{{ route('questions.index') }}">সকল প্রশ্ন</a>
        <a class="block hover:underline" href="{{ route('answers.index') }}">প্রশ্নোত্তর</a>
        <a class="block hover:underline" href="{{ route('ask') }}">প্রশ্ন করুন</a>
      </div>
    </div>
  </div>
</aside>

{{-- =========================
   Mobile Drawer
   ========================= --}}
<div class="lg:hidden">
  {{-- overlay --}}
  <div x-show="$store.drawer.sidebar"
       x-cloak
       x-transition.opacity
       @click="$store.drawer.closeSidebar()"
       class="fixed inset-0 z-[9998] bg-black/40">
  </div>

  {{-- panel --}}
  <div x-show="$store.drawer.sidebar"
       x-cloak
       x-transition
       class="fixed inset-y-0 left-0 z-[9999] w-[86vw] max-w-sm bg-slate-100 p-4 overflow-y-auto">
    <div class="qa-card p-4">
      <div class="flex items-center justify-between">
        <div class="font-extrabold text-slate-900">ক্যাটাগরি</div>

        <button type="button"
                @click="$store.drawer.closeSidebar()"
                class="qa-btn qa-btn-outline px-3">
          ✕
        </button>
      </div>

      <div class="mt-4 space-y-2">
        @foreach($categories as $c)
          @php
            $url = route('categories.show', $c['slug']);
            $active = $isCategoriesPage && $activeSlug === $c['slug'];
          @endphp

          <a href="{{ $url }}"
             @click="$store.drawer.closeSidebar()"
             class="flex items-center justify-between rounded-2xl border px-3 py-2 text-sm font-semibold transition
                    {{ $active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-800 border-slate-200 hover:bg-slate-50' }}">
            <span>{{ $c['name'] }}</span>
            <span class="text-xs opacity-80">→</span>
          </a>
        @endforeach
      </div>

      <div class="mt-4 border-t pt-4">
        <div class="font-bold text-slate-900 text-sm">দ্রুত লিংক</div>
        <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
          <a class="qa-btn qa-btn-outline" href="{{ route('home') }}" @click="$store.drawer.closeSidebar()">হোম</a>
          <a class="qa-btn qa-btn-outline" href="{{ route('questions.index') }}" @click="$store.drawer.closeSidebar()">প্রশ্ন</a>
          <a class="qa-btn qa-btn-outline" href="{{ route('answers.index') }}" @click="$store.drawer.closeSidebar()">উত্তর</a>
          <a class="qa-btn qa-btn-primary" href="{{ route('ask') }}" @click="$store.drawer.closeSidebar()">প্রশ্ন করুন</a>
        </div>
      </div>
    </div>
  </div>
</div>
