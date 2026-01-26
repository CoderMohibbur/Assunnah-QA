<header class="bg-white/90 backdrop-blur border-b sticky top-0 z-50">
  <div class="qa-container py-3 flex items-center justify-between gap-4">

    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-extrabold text-slate-900">
      <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-white text-sm">A</span>
      <span class="tracking-tight">Admin Panel</span>
    </a>

    <nav class="hidden md:flex items-center gap-4 text-sm font-semibold">
      <a class="text-slate-700 hover:text-slate-900" href="{{ route('admin.questions.index',['status'=>'pending']) }}">Questions</a>
      <a class="text-slate-700 hover:text-slate-900" href="{{ route('admin.categories.index') }}">Categories</a>
      <a class="text-slate-700 hover:text-slate-900" href="{{ route('admin.pages.index') }}">Pages</a>
      <a class="text-slate-700 hover:text-slate-900" href="{{ route('admin.settings.index') }}">Settings</a>
      <a class="text-slate-700 hover:text-slate-900" href="{{ route('home') }}" target="_blank">Public ↗</a>
    </nav>

    <div class="flex items-center gap-2">
      <a href="{{ route('dashboard') }}" class="qa-btn qa-btn-outline">Dashboard</a>
    </div>

  </div>
</header>
