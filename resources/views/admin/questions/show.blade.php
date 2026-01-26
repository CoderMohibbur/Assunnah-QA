@extends('layouts.app')

@section('title', 'Admin — Review Question')

@push('styles')
  {{-- Jodit Editor (CDN) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jodit@4.1.16/es2021/jodit.min.css">
@endpush

@section('content')
  {{-- Header --}}
  <div class="qa-card">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <a href="{{ route('admin.questions.index', ['status' => $question->status]) }}"
             class="qa-btn qa-btn-outline">
            ← Back
          </a>
          <span class="qa-badge">{{ $question->status }}</span>
          <span class="text-xs text-slate-500">#{{ $question->id }}</span>
        </div>

        <h1 class="mt-3 text-xl md:text-2xl font-extrabold text-slate-900">
          {{ $question->title }}
        </h1>

        <div class="mt-2 text-sm text-slate-600 flex flex-wrap gap-x-4 gap-y-1">
          <div><span class="text-slate-500">Created:</span> {{ optional($question->created_at)->format('Y-m-d H:i') }}</div>
          <div><span class="text-slate-500">Category:</span>
            {{ $question->category?->name_bn ?? ('#'.$question->category_id) }}
          </div>
          @if($question->published_at)
            <div><span class="text-slate-500">Published:</span> {{ optional($question->published_at)->format('Y-m-d H:i') }}</div>
          @endif
        </div>
      </div>

      {{-- Quick Actions --}}
      <div class="flex flex-wrap gap-2">
        @if($question->status === 'rejected')
          <form method="POST" action="{{ route('admin.questions.approve', $question) }}">
            @csrf
            <button type="submit" class="qa-btn qa-btn-outline">
              Approve (Back to Pending)
            </button>
          </form>
        @endif

        <a class="qa-btn qa-btn-outline"
           href="{{ route('questions.show', $question->slug ?? ('q-'.$question->id) ) }}"
           target="_blank" rel="noopener">
          Public View ↗
        </a>
      </div>
    </div>
  </div>

  {{-- Flash Messages --}}
  <div class="mt-4">
    @if(session('success'))
      <div class="qa-card border border-emerald-200 bg-emerald-50 text-emerald-900">
        <div class="font-semibold">{{ session('success') }}</div>
      </div>
    @endif

    @if(session('error'))
      <div class="qa-card border border-rose-200 bg-rose-50 text-rose-900 mt-3">
        <div class="font-semibold">{{ session('error') }}</div>
      </div>
    @endif

    @if($errors->any())
      <div class="qa-card border border-rose-200 bg-rose-50 text-rose-900 mt-3">
        <div class="font-extrabold">Fix these:</div>
        <ul class="mt-2 list-disc pl-5 text-sm">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>

  @php
    // ✅ Notification logs snapshot (latest 10)
    $logs = \App\Models\MessageLog::query()
      ->where('question_id', $question->id)
      ->latest()
      ->limit(10)
      ->get();

    $notified = !empty($question->answered_notified_at);
    $attempts = (int)($question->notify_attempts ?? 0);
    $lastErr  = (string)($question->notify_last_error ?? '');
  @endphp

  {{-- ✅ Notification Status Panel --}}
  <div class="mt-4 qa-card">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
      <div>
        <h2 class="font-extrabold text-slate-900">Notification Status</h2>
        <p class="text-sm text-slate-600 mt-1">
          Answer publish হওয়ার পরে SMS/Email notify হয়েছে কিনা, attempts, error ইত্যাদি এখানে দেখবেন।
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        @if($notified)
          <span class="qa-badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;">
            Notified ✅
          </span>
        @else
          <span class="qa-badge" style="background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;">
            Not Notified ⚠️
          </span>
        @endif

        <span class="qa-badge">
          Attempts: {{ $attempts }}
        </span>
      </div>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
      <div class="qa-card bg-slate-50">
        <div class="text-xs text-slate-500">answered_notified_at</div>
        <div class="font-semibold text-slate-900">
          {{ $question->answered_notified_at ? $question->answered_notified_at->format('Y-m-d H:i') : '—' }}
        </div>
      </div>

      <div class="qa-card bg-slate-50">
        <div class="text-xs text-slate-500">notify_attempts</div>
        <div class="font-semibold text-slate-900">{{ $attempts }}</div>
      </div>

      <div class="qa-card bg-slate-50">
        <div class="text-xs text-slate-500">notify_last_error</div>
        <div class="font-semibold {{ $lastErr ? 'text-rose-700' : 'text-slate-900' }}">
          {{ $lastErr ?: '—' }}
        </div>
      </div>
    </div>

    <div class="mt-4">
      <div class="flex items-center justify-between">
        <div class="font-extrabold text-slate-900">Message Logs (Latest)</div>
        <div class="text-xs text-slate-500">Last 10 entries</div>
      </div>

      <div class="mt-3 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-slate-500">
              <th class="py-2 pr-4">Time</th>
              <th class="py-2 pr-4">Channel</th>
              <th class="py-2 pr-4">To</th>
              <th class="py-2 pr-4">Status</th>
              <th class="py-2 pr-4">Error</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($logs as $log)
              <tr class="align-top">
                <td class="py-3 pr-4 text-slate-700">
                  {{ optional($log->created_at)->format('Y-m-d H:i') }}
                </td>
                <td class="py-3 pr-4">
                  <span class="qa-badge">{{ $log->channel }}</span>
                </td>
                <td class="py-3 pr-4 text-slate-700">
                  {{ $log->to }}
                </td>
                <td class="py-3 pr-4">
                  @if($log->status === 'sent')
                    <span class="qa-badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;">sent</span>
                  @elseif($log->status === 'failed')
                    <span class="qa-badge" style="background:#fff1f2;color:#9f1239;border:1px solid #fecdd3;">failed</span>
                  @else
                    <span class="qa-badge">{{ $log->status }}</span>
                  @endif
                </td>
                <td class="py-3 pr-4 text-xs text-slate-600">
                  {{ \Illuminate\Support\Str::limit($log->error ?? '', 120) ?: '—' }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="py-6 text-center text-slate-500">
                  এখনো কোনো message log নেই।
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3 text-xs text-slate-500">
        টিপ: Mail test এর জন্য <span class="font-semibold">MAIL_MAILER=log</span> দিলে <span class="font-semibold">storage/logs/laravel.log</span> এ দেখা যাবে।
      </div>
    </div>
  </div>

  <div class="mt-4 grid grid-cols-1 lg:grid-cols-12 gap-4">
    {{-- Left: Question Details --}}
    <div class="lg:col-span-5">
      <div class="qa-card">
        <h2 class="font-extrabold text-slate-900">Question Details</h2>

        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div class="qa-card bg-slate-50">
            <div class="text-xs text-slate-500">Asker Name</div>
            <div class="font-semibold text-slate-900">{{ $question->asker_name ?? '-' }}</div>
          </div>
          <div class="qa-card bg-slate-50">
            <div class="text-xs text-slate-500">Phone</div>
            <div class="font-semibold text-slate-900">{{ $question->asker_phone ?? '-' }}</div>
          </div>
          <div class="qa-card bg-slate-50 md:col-span-2">
            <div class="text-xs text-slate-500">Email</div>
            <div class="font-semibold text-slate-900">{{ $question->asker_email ?? '-' }}</div>
          </div>
        </div>

        <div class="mt-4">
          <div class="text-xs text-slate-500">Question Body</div>
          <div class="mt-2 prose max-w-none qa-card bg-white">
            {{-- NOTE: body_html should be sanitized at save time (Phase-2). --}}
            {!! $question->body_html !!}
          </div>
        </div>
      </div>
    </div>

    {{-- Right: Answer Editor --}}
    <div class="lg:col-span-7">
      <div class="qa-card">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="font-extrabold text-slate-900">Write Answer</h2>
            <p class="text-sm text-slate-600 mt-1">
              Draft করে রাখতে পারবেন, পরে Publish করলে user কে SMS/Email যাবে।
            </p>
          </div>

          @if($question->answer)
            <div class="text-right">
              <div class="text-xs text-slate-500">Current Answer Status</div>
              <div class="qa-badge">{{ $question->answer->status }}</div>
              @if($question->answer->answeredBy)
                <div class="text-xs text-slate-500 mt-1">
                  By: <span class="font-semibold text-slate-700">{{ $question->answer->answeredBy->name }}</span>
                </div>
              @endif
            </div>
          @endif
        </div>

        <form method="POST" action="{{ route('admin.answers.draft', $question) }}" class="mt-4">
          @csrf

          <label class="text-xs text-slate-500">Answer (HTML)</label>
          <textarea
            id="answer_html"
            name="answer_html"
            class="qa-input w-full min-h-[260px]"
            placeholder="এখানে উত্তর লিখুন..."
          >{{ old('answer_html', $question->answer?->answer_html ?? '') }}</textarea>

          <div class="mt-4 flex flex-col sm:flex-row gap-2">
            <button type="submit" class="qa-btn qa-btn-outline w-full sm:w-auto">
              Save Draft
            </button>

            <button type="submit"
                    class="qa-btn qa-btn-primary w-full sm:w-auto"
                    formaction="{{ route('admin.answers.publish', $question) }}"
                    @if($question->status === 'rejected') disabled @endif
            >
              Publish Answer
            </button>

            <button type="submit"
                    class="qa-btn qa-btn-outline w-full sm:w-auto"
                    formaction="{{ route('admin.questions.reject', $question) }}"
                    onclick="return confirm('Reject করতে চান?')"
                    @if($question->status === 'published') disabled @endif
            >
              Reject
            </button>
          </div>

          @if($question->status === 'rejected')
            <div class="mt-3 text-sm text-rose-700">
              এই প্রশ্নটি rejected অবস্থায় আছে—Publish করতে হলে আগে Approve (Back to Pending) করুন।
            </div>
          @endif

          @if($question->status === 'published')
            <div class="mt-3 text-sm text-emerald-700">
              এই প্রশ্নটি published। আপনি চাইলে answer edit করে আবার Publish দিতে পারেন (আপনার policy অনুযায়ী)।
            </div>
          @endif
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  {{-- Jodit Editor (CDN) --}}
  <script src="https://cdn.jsdelivr.net/npm/jodit@4.1.16/es2021/jodit.min.js"></script>
  <script>
    (function () {
      var el = document.getElementById('answer_html');
      if (!el) return;

      // Prevent double init
      if (el.dataset.inited === "1") return;
      el.dataset.inited = "1";

      new Jodit(el, {
        height: 320,
        toolbarAdaptive: false,
        spellcheck: true,
        buttons: [
          'bold','italic','underline','|',
          'ul','ol','|',
          'paragraph','fontsize','|',
          'link','|',
          'hr','|',
          'undo','redo','|',
          'source'
        ]
      });
    })();
  </script>
@endpush
