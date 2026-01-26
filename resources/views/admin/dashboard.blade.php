@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="qa-card">
        <div class="text-xl font-extrabold text-slate-900">Admin Dashboard</div>
        <div class="mt-1 text-sm text-slate-600">
            এখান থেকে Pending প্রশ্ন, ক্যাটাগরি, এবং অন্যান্য কন্ট্রোল পাবেন।
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <a class="qa-btn qa-btn-primary" href="{{ route('admin.questions.index', ['status' => 'pending']) }}">Pending
                Questions</a>
            <a class="qa-btn qa-btn-outline" href="{{ route('admin.categories.index') }}">Categories</a>
        </div>
    </div>
@endsection
