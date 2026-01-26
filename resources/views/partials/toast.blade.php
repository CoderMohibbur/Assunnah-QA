<div
  x-data
  x-cloak
  x-show="$store.toast.open"
  x-transition
  class="fixed top-4 right-4 z-[9999] w-[92vw] max-w-md"
>
  <div class="qa-card border border-slate-200 shadow-lg">
    <div class="flex items-start gap-3">
      <div class="mt-1 text-xl">✅</div>

      <div class="flex-1">
        <div class="text-sm font-bold text-slate-900" x-text="$store.toast.title"></div>
        <div class="mt-1 text-sm text-slate-700" x-html="$store.toast.message"></div>

        <template x-if="$store.toast.link">
          <a :href="$store.toast.link"
             class="mt-3 inline-flex text-sm font-semibold text-slate-900 hover:underline">
            দেখুন →
          </a>
        </template>
      </div>

      <button @click="$store.toast.hide()" class="h-9 w-9 rounded-xl border border-slate-200 bg-white">
        ✕
      </button>
    </div>
  </div>
</div>
