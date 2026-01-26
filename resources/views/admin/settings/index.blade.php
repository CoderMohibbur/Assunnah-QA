@extends('layouts.app')

@section('title', 'Admin — Settings')

@section('content')
  <div class="qa-card">
    <h1 class="text-xl md:text-2xl font-extrabold text-slate-900">Settings</h1>
    <p class="text-sm text-slate-600 mt-1">Notification / UI settings.</p>
  </div>

  <div class="mt-4">
    @if(session('success'))
      <div class="qa-card border border-emerald-200 bg-emerald-50 text-emerald-900">
        <div class="font-semibold">{{ session('success') }}</div>
      </div>
    @endif
  </div>

  <div class="mt-4 qa-card">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
      @csrf @method('PUT')

      <div>
        <label class="text-sm font-bold text-slate-800">Site Title *</label>
        <input name="site_title" class="qa-input mt-2 w-full" value="{{ old('site_title', $data['site_title']) }}">
        @error('site_title') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="text-sm font-bold text-slate-800">Footer Text</label>
        <textarea name="footer_text" class="qa-input mt-2 w-full" rows="3">{{ old('footer_text', $data['footer_text']) }}</textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="flex items-center gap-2 pt-2">
          <input type="checkbox" name="notify_sms" value="1" class="rounded" @checked(old('notify_sms', $data['notify_sms'])=='1')>
          <span class="text-sm font-semibold text-slate-700">SMS Notify</span>
        </div>

        <div class="flex items-center gap-2 pt-2">
          <input type="checkbox" name="notify_email" value="1" class="rounded" @checked(old('notify_email', $data['notify_email'])=='1')>
          <span class="text-sm font-semibold text-slate-700">Email Notify</span>
        </div>

        <div>
          <label class="text-sm font-bold text-slate-800">SMS Provider</label>
          <input name="sms_provider" class="qa-input mt-2 w-full" value="{{ old('sms_provider', $data['sms_provider']) }}" placeholder="modem / httpapi / etc">
        </div>
      </div>

      <div class="pt-2">
        <button class="qa-btn qa-btn-primary" type="submit">Save Settings</button>
      </div>
    </form>
  </div>
@endsection
