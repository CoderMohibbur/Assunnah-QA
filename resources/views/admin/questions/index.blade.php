@extends('layouts.app')

@section('title', 'Admin — Questions')

@section('content')
  <div class="qa-card">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
      <div>
        <h1 class="text-xl md:text-2xl font-extrabold text-slate-900">Questions</h1>
        <p class="text-sm text-slate-600 mt-1">Pending / Published / Rejected প্রশ্নগুলো ম্যানেজ করুন।</p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.questions.index', ['status'=>'pending']) }}"
           class="qa-btn {{ $status==='pending' ? 'qa-btn-primary' : 'qa-btn-outline' }}">
          Pending ({{ $counts['pending'] ?? 0 }})
        </a>

        <a href="{{ route('admin.questions.index', ['status'=>'published']) }}"
           class="qa-btn {{ $status==='published' ? 'qa-btn-primary' : 'qa-btn-outline' }}">
          Published ({{ $counts['published'] ?? 0 }})
        </a>

        <a href="{{ route('admin.questions.index', ['status'=>'rejected']) }}"
           class="qa-btn {{ $status==='rejected' ? 'qa-btn-primary' : 'qa-btn-outline' }}">
          Rejected ({{ $counts['rejected'] ?? 0 }})
        </a>
      </div>
    </div>
  </div>

  {{-- Filter Bar --}}
  <div class="mt-4 qa-card">
    <form method="GET" action="{{ route('admin.questions.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3">
      <input type="hidden" name="status" value="{{ $status }}"/>

      <div class="md:col-span-6">
        <label class="text-xs text-slate-500">Search</label>
        <input name="q" value="{{ $q ?? '' }}" class="qa-input w-full"
               placeholder="শিরোনাম / নাম / ফোন দিয়ে সার্চ করুন"/>
      </div>

      <div class="md:col-span-3">
        <label class="text-xs text-slate-500">Category ID (optional)</label>
        <input name="category_id" value="{{ $cat ?? '' }}" class="qa-input w-full" placeholder="e.g. 1"/>
      </div>

      <div class="md:col-span-3 flex items-end gap-2">
        <button class="qa-btn qa-btn-primary w-full" type="submit">Filter</button>
        <a class="qa-btn qa-btn-outline w-full"
           href="{{ route('admin.questions.index', ['status'=>$status]) }}">
          Reset
        </a>
      </div>
    </form>
  </div>

  {{-- Table --}}
  <div class="mt-4 qa-card">
    <div class="flex items-center justify-between">
      <div class="font-extrabold text-slate-900">
        তালিকা ({{ $questions->total() }})
      </div>
      <div class="text-xs text-slate-500">
        Status: <span class="font-semibold">{{ ucfirst($status) }}</span>
      </div>
    </div>

    <div class="mt-4 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-xs text-slate-500">
            <th class="py-2 pr-4">#</th>
            <th class="py-2 pr-4">Title</th>
            <th class="py-2 pr-4">Asker</th>
            <th class="py-2 pr-4">Phone</th>
            <th class="py-2 pr-4">Category</th>
            <th class="py-2 pr-4">Created</th>
            <th class="py-2 pr-4">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($questions as $row)
            <tr class="align-top">
              <td class="py-3 pr-4 font-semibold text-slate-700">
                {{ $row->id }}
              </td>

              <td class="py-3 pr-4">
                <div class="font-extrabold text-slate-900 line-clamp-2">
                  {{ $row->title }}
                </div>
                <div class="mt-1 text-xs text-slate-500">
                  <span class="qa-badge">{{ $row->status }}</span>
                  @if($row->published_at)
                    • Published: {{ optional($row->published_at)->format('Y-m-d') }}
                  @endif
                </div>
              </td>

              <td class="py-3 pr-4 text-slate-700">
                {{ $row->asker_name ?? '-' }}
              </td>

              <td class="py-3 pr-4 text-slate-700">
                {{ $row->asker_phone ?? '-' }}
              </td>

              <td class="py-3 pr-4 text-slate-700">
                {{ $row->category_id ?? '-' }}
              </td>

              <td class="py-3 pr-4 text-slate-700">
                {{ optional($row->created_at)->format('Y-m-d') }}
              </td>

              <td class="py-3 pr-4">
                <a class="qa-btn qa-btn-outline"
                   href="{{ route('admin.questions.show', $row) }}">
                  Review →
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="py-6 text-center text-slate-500">
                এই স্ট্যাটাসে কোনো প্রশ্ন নেই।
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $questions->links() }}
    </div>
  </div>
@endsection
