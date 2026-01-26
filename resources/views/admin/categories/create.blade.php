@extends('layouts.app')

@section('title', 'Admin — Create Category')

@section('content')
  <div class="qa-card">
    <div class="flex items-start justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <a href="{{ route('admin.categories.index') }}" class="qa-btn qa-btn-outline">← Back</a>
          <span class="qa-badge">Create</span>
        </div>

        <h1 class="mt-3 text-xl md:text-2xl font-extrabold text-slate-900">New Category</h1>
        <p class="text-sm text-slate-600 mt-1">Ask page + sidebar + filters এ এই category দেখাবে।</p>
      </div>
    </div>
  </div>

  {{-- Errors --}}
  @if($errors->any())
    <div class="mt-4 qa-card border border-rose-200 bg-rose-50 text-rose-900">
      <div class="font-extrabold">Fix these:</div>
      <ul class="mt-2 list-disc pl-5 text-sm">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="mt-4 qa-card">
    <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
      @csrf

      <div>
        <label class="text-sm font-bold text-slate-800">ক্যাটাগরি নাম (বাংলা) <span class="text-red-600">*</span></label>
        <input type="text" name="name_bn" required class="qa-input mt-2 w-full"
               value="{{ old('name_bn') }}" placeholder="যেমন: আকিদা / ফিকহ / আদব-আখলাক">
        @error('name_bn')
          <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
        @enderror
      </div>

      <div>
        <label class="text-sm font-bold text-slate-800">Slug (optional)</label>
        <input type="text" name="slug" class="qa-input mt-2 w-full"
               value="{{ old('slug') }}" placeholder="যেমন: aqidah (খালি রাখলে auto)">

        <div class="mt-1 text-xs text-slate-500">
          খালি রাখলে auto slug হবে। বাংলা হলে auto slug empty হলে fallback হিসেবে cat-XXXXX হবে।
        </div>

        @error('slug')
          <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
        @enderror
      </div>

      <div>
        <label class="text-sm font-bold text-slate-800">Description (optional)</label>
        <textarea name="description" rows="4" class="qa-input mt-2 w-full"
                  placeholder="Short description...">{{ old('description') }}</textarea>
        @error('description')
          <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
        @enderror
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm font-bold text-slate-800">Sort Order</label>
          <input type="number" name="sort_order" class="qa-input mt-2 w-full"
                 value="{{ old('sort_order', 0) }}" min="0" max="9999">
          @error('sort_order')
            <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
          @enderror
        </div>

        <div class="md:col-span-2 flex items-center gap-2 pt-6">
            <input type="hidden" name="is_active" value="0">

          <input id="is_active" type="checkbox" name="is_active" value="1"
                 class="rounded"
                 @checked(old('is_active', 1))>
          <label for="is_active" class="text-sm text-slate-700 font-semibold">Active রাখুন</label>
        </div>
      </div>

      <div class="pt-2 flex flex-col sm:flex-row gap-2">
        <button type="submit" class="qa-btn qa-btn-primary w-full sm:w-auto">Create</button>
        <a href="{{ route('admin.categories.index') }}" class="qa-btn qa-btn-outline w-full sm:w-auto">Cancel</a>
      </div>
    </form>
  </div>
@endsection
