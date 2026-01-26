@extends('layouts.app')

@section('title', 'প্রশ্ন সাবমিট হয়েছে')

@section('content')
    <div class="qa-card text-center">
        <div class="text-3xl">✅</div>
        <div class="mt-3 text-xl font-extrabold text-slate-900">প্রশ্ন সাবমিট হয়েছে</div>

        <div class="mt-2 text-sm text-slate-600">
            আপনার প্রশ্নটি এখন <span class="font-semibold text-slate-800">Pending</span> অবস্থায় আছে।
            মডারেটর রিভিউ করে উত্তর প্রকাশ করলে SMS/Email নোটিফিকেশন যাবে (Phase-3)।
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-2 justify-center">
            <a href="{{ url('/ask') }}" class="qa-btn qa-btn-primary">আরেকটি প্রশ্ন করুন</a>
            <a href="{{ url('/questions') }}" class="qa-btn qa-btn-outline">সব প্রশ্ন দেখুন</a>
        </div>

        <div class="mt-4 text-xs text-slate-500">
            রেফারেন্স আইডি: Q-{{ $id }}
        </div>
    </div>
@endsection
