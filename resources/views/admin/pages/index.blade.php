@extends('layouts.app')

@section('title', 'Admin — Pages')

@section('content')
  <div class="qa-card">
    <div class="flex items-end justify-between gap-4">
      <div>
        <h1 class="text-xl md:text-2xl font-extrabold text-slate-900">Pages</h1>
        <p class="text-sm text-slate-600 mt-1">About / Home Featured ইত্যাদি CMS কন্টেন্ট।</p>
      </div>
      <a href="{{ route('admin.pages.create') }}" class="qa-btn qa-btn-primary">+ New Page</a>
    </div>
  </div>

  <div class="mt-4">
    @if(session('success'))
      <div class="qa-card border border-emerald-200 bg-emerald-50 text-emerald-900">
        <div class="font-semibold">{{ session('success') }}</div>
      </div>
    @endif
  </div>

  <div class="mt-4 qa-card overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead>
        <tr class="text-left text-xs text-slate-500">
          <th class="py-2 pr-4">Slug</th>
          <th class="py-2 pr-4">Title</th>
          <th class="py-2 pr-4">Active</th>
          <th class="py-2 pr-4">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($pages as $p)
          <tr>
            <td class="py-3 pr-4"><span class="qa-badge">{{ $p->slug }}</span></td>
            <td class="py-3 pr-4 font-semibold text-slate-800">{{ $p->title ?? '-' }}</td>
            <td class="py-3 pr-4">
              @if($p->is_active)
                <span class="qa-badge" style="background: rgba(16,185,129,.12); color:#065f46;">Active</span>
              @else
                <span class="qa-badge" style="background: rgba(239,68,68,.10); color:#991b1b;">Inactive</span>
              @endif
            </td>
            <td class="py-3 pr-4">
              <div class="flex gap-2">
                <a href="{{ route('admin.pages.edit', $p) }}" class="qa-btn qa-btn-outline">Edit</a>
                <form method="POST" action="{{ route('admin.pages.destroy', $p) }}" onsubmit="return confirm('Delete? (soft delete)')">
                  @csrf @method('DELETE')
                  <button class="qa-btn qa-btn-outline" type="submit">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="py-6 text-center text-slate-500">No pages yet.</td></tr>
        @endforelse
      </tbody>
    </table>

    <div class="mt-4">{{ $pages->links() }}</div>
  </div>
@endsection
