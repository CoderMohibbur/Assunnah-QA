@extends('layouts.app')

@section('title', 'আমাদের সম্পর্কে')

@section('content')
@php
  use App\Models\Question;
  use App\Models\Category;

  $publishedCount = Question::query()
      ->whereNull('deleted_at')
      ->where('status', 'published')
      ->count();

  $pendingCount = Question::query()
      ->whereNull('deleted_at')
      ->where('status', 'pending')
      ->count();

  $categoryCount = class_exists(Category::class)
      ? Category::query()->whereNull('deleted_at')->where('is_active', 1)->count()
      : 0;

  // optional extra numbers (safe)
  $totalCount = Question::query()
      ->whereNull('deleted_at')
      ->count();

  $answeredCount = $publishedCount; // আপনার সিস্টেমে published মানেই পাবলিশড উত্তর/প্রশ্ন
@endphp

  {{-- HERO --}}
  <div class="qa-card qa-card-hover overflow-hidden p-0">
    <div class="relative bg-gradient-to-br from-slate-900 via-blue-800 to-cyan-500">
      <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_30%_30%,white,transparent_55%)]"></div>

      <div class="relative p-6 md:p-10 text-white">
        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/20 px-3 py-1 text-xs">
          <span>As Sunnah</span>
          <span class="opacity-70">•</span>
          <span class="opacity-90">Question &amp; Answer</span>
        </div>

        <h1 class="mt-4 text-2xl md:text-4xl font-extrabold leading-tight">
          সহিহ ইসলামিক জ্ঞানচর্চার জন্য
          <span class="block text-white/90">একটি সহজ প্রশ্ন-উত্তর প্ল্যাটফর্ম</span>
        </h1>

        <p class="mt-4 max-w-3xl text-sm md:text-base text-white/90 leading-relaxed">
          এখানে সাধারণ মানুষ ইসলাম সম্পর্কিত প্রশ্ন করতে পারবেন। প্রশ্নগুলো যাচাই-বাছাই/সম্পাদনার মাধ্যমে
          যোগ্য আলেম/মডারেটর উত্তর প্রস্তুত করে প্রকাশ করবেন ইনশাআল্লাহ।
          আমাদের লক্ষ্য—বিশুদ্ধ তথ্য, সহজ ভাষা এবং দায়িত্বশীল প্রকাশ।
        </p>

        <div class="mt-6 flex flex-col sm:flex-row gap-2">
          <a href="{{ route('ask') }}" class="qa-btn qa-btn-outline bg-white/10 border-white/30 text-white hover:bg-white/15">
            প্রশ্ন করুন
          </a>
          <a href="{{ route('answers.index') }}" class="qa-btn qa-btn-outline bg-white/10 border-white/30 text-white hover:bg-white/15">
            প্রকাশিত উত্তর দেখুন
          </a>
          <a href="{{ route('questions.index') }}" class="qa-btn qa-btn-outline bg-white/10 border-white/30 text-white hover:bg-white/15">
            সকল প্রশ্ন
          </a>
        </div>
      </div>
    </div>

    {{-- STATS STRIP --}}
    <div class="p-6 md:p-8 bg-white">
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="qa-card">
          <div class="text-xs text-slate-500">মোট প্রশ্ন</div>
          <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ $totalCount }}</div>
          <div class="mt-1 text-xs text-slate-500">সকল জমাকৃত প্রশ্ন</div>
        </div>

        <div class="qa-card">
          <div class="text-xs text-slate-500">Published প্রশ্ন</div>
          <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ $publishedCount }}</div>
          <div class="mt-1 text-xs text-slate-500">প্রকাশিত/উত্তরসহ</div>
        </div>

        <div class="qa-card">
          <div class="text-xs text-slate-500">Pending প্রশ্ন</div>
          <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ $pendingCount }}</div>
          <div class="mt-1 text-xs text-slate-500">রিভিউ/প্রসেসিং</div>
        </div>

        <div class="qa-card">
          <div class="text-xs text-slate-500">Active ক্যাটাগরি</div>
          <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ $categoryCount }}</div>
          <div class="mt-1 text-xs text-slate-500">বিষয়ভিত্তিক সংগঠন</div>
        </div>
      </div>
    </div>
  </div>

  {{-- ABOUT TEXT + VALUES --}}
  <div class="mt-6 grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 qa-card">
      <div class="font-extrabold text-slate-900 text-xl">আমাদের উদ্দেশ্য ও কাজ</div>
      <p class="mt-3 text-sm text-slate-700 leading-relaxed">
        এটি একটি প্রশ্ন-উত্তর ভিত্তিক ইসলামিক জ্ঞানচর্চার প্ল্যাটফর্ম। এখানে সাধারণ মানুষ তাদের প্রশ্ন করতে পারবেন,
        এবং যাচাই-বাছাই শেষে উত্তর প্রকাশ করা হবে ইনশাআল্লাহ।
      </p>

      <div class="mt-5 grid sm:grid-cols-2 gap-4">
        <div class="qa-card">
          <div class="font-bold text-slate-900">✅ সহিহতা</div>
          <p class="mt-2 text-sm text-slate-600">
            কুরআন-সুন্নাহ ও নির্ভরযোগ্য ফিকহি উৎসের আলোকে উত্তর প্রস্তুত করা।
          </p>
        </div>
        <div class="qa-card">
          <div class="font-bold text-slate-900">✅ সহজ ভাষা</div>
          <p class="mt-2 text-sm text-slate-600">
            জটিল বিষয়ও সাধারণ মানুষের বোঝার মতো করে উপস্থাপন।
          </p>
        </div>
        <div class="qa-card">
          <div class="font-bold text-slate-900">✅ দায়িত্বশীল প্রকাশ</div>
          <p class="mt-2 text-sm text-slate-600">
            ভুল তথ্য/গুজব এড়াতে প্রকাশের আগে রিভিউ ও সম্পাদনা।
          </p>
        </div>
        <div class="qa-card">
          <div class="font-bold text-slate-900">✅ শৃঙ্খলা</div>
          <p class="mt-2 text-sm text-slate-600">
            ক্যাটাগরি, ট্যাগিং, সার্চ—সবকিছু সহজে খুঁজে পাওয়ার জন্য।
          </p>
        </div>
      </div>

      <div class="mt-5 text-sm text-slate-700 leading-relaxed">
        <div class="font-bold text-slate-900">নোট:</div>
        <ul class="mt-2 list-disc pl-5 space-y-1 text-slate-600">
          <li>প্রশ্ন জমা দিলে সাথে সাথে প্রকাশ নাও হতে পারে—রিভিউ লাগবে।</li>
          <li>একই ধরনের প্রশ্ন আগে থাকলে আমরা সেটার লিংক সাজেস্ট করতে পারি।</li>
          <li>উত্তর প্রকাশ হলে ভবিষ্যতে SMS/Email নোটিফিকেশন যোগ হবে ইনশাআল্লাহ।</li>
        </ul>
      </div>
    </div>

    <div class="qa-card">
      <div class="font-extrabold text-slate-900 text-xl">যোগাযোগ</div>
      <p class="mt-3 text-sm text-slate-600 leading-relaxed">
        আপাতত <b>Ask</b> পেজে প্রশ্ন করুন। প্রয়োজন হলে আপনার মোবাইল নম্বর দিয়ে প্রশ্ন জমা দিন।
        ভবিষ্যতে যোগাযোগ ফর্ম/ইমেইল/হেল্প সেকশন যুক্ত হবে ইনশাআল্লাহ।
      </p>

      <div class="mt-4 qa-card bg-slate-50 border border-slate-100">
        <div class="text-xs text-slate-500">দ্রুত কাজ করতে</div>
        <div class="mt-1 text-sm text-slate-700">
          প্রশ্নে শিরোনাম পরিষ্কার লিখুন, বিস্তারিত ব্যাখ্যা দিন, এবং প্রয়োজনে প্রসঙ্গ (সময়/পরিস্থিতি) উল্লেখ করুন।
        </div>
      </div>

      <div class="mt-4 flex flex-col gap-2">
        <a href="{{ route('ask') }}" class="qa-btn qa-btn-primary">প্রশ্ন করুন</a>
        <a href="{{ route('answers.index') }}" class="qa-btn qa-btn-outline">প্রকাশিত উত্তর</a>
        <a href="{{ route('questions.index') }}" class="qa-btn qa-btn-outline">সকল প্রশ্ন</a>
      </div>
    </div>
  </div>

  {{-- HOW IT WORKS --}}
  <div class="mt-6 qa-card">
    <div class="flex items-center justify-between gap-3">
      <div>
        <div class="font-extrabold text-slate-900 text-xl">কিভাবে কাজ করে?</div>
        <div class="text-sm text-slate-600">প্রশ্ন থেকে উত্তর প্রকাশ—সহজ ৪ ধাপ</div>
      </div>
    </div>

    <div class="mt-5 grid md:grid-cols-4 gap-4">
      <div class="qa-card">
        <div class="text-xs text-slate-500">ধাপ ১</div>
        <div class="mt-1 font-bold text-slate-900">প্রশ্ন জমা</div>
        <p class="mt-2 text-sm text-slate-600">Ask পেজে শিরোনাম ও বিস্তারিত লিখে সাবমিট করুন।</p>
      </div>
      <div class="qa-card">
        <div class="text-xs text-slate-500">ধাপ ২</div>
        <div class="mt-1 font-bold text-slate-900">রিভিউ</div>
        <p class="mt-2 text-sm text-slate-600">স্প্যাম/ডুপ্লিকেট/অপ্রাসঙ্গিক হলে ফিল্টার হবে।</p>
      </div>
      <div class="qa-card">
        <div class="text-xs text-slate-500">ধাপ ৩</div>
        <div class="mt-1 font-bold text-slate-900">উত্তর প্রস্তুত</div>
        <p class="mt-2 text-sm text-slate-600">যোগ্য মডারেটর/আলেম উত্তর প্রস্তুত করবেন।</p>
      </div>
      <div class="qa-card">
        <div class="text-xs text-slate-500">ধাপ ৪</div>
        <div class="mt-1 font-bold text-slate-900">প্রকাশ</div>
        <p class="mt-2 text-sm text-slate-600">ভেরিফাই শেষে উত্তর পাবলিশ হবে ইনশাআল্লাহ।</p>
      </div>
    </div>
  </div>

  {{-- FAQ --}}
  <div class="mt-6 qa-card">
    <div class="font-extrabold text-slate-900 text-xl">সাধারণ প্রশ্ন (FAQ)</div>

    <div class="mt-4 grid md:grid-cols-2 gap-4">
      <div class="qa-card">
        <div class="font-bold text-slate-900">প্রশ্ন করলে সাথে সাথে উত্তর পাবো?</div>
        <p class="mt-2 text-sm text-slate-600">
          না। আগে রিভিউ হবে, এরপর উত্তর প্রস্তুত হয়ে প্রকাশ হবে।
        </p>
      </div>

      <div class="qa-card">
        <div class="font-bold text-slate-900">ডুপ্লিকেট প্রশ্ন করলে কি হবে?</div>
        <p class="mt-2 text-sm text-slate-600">
          টাইটেল টাইপ করার সময় সিমিলার প্রশ্ন সাজেস্ট হতে পারে। প্রয়োজনে নতুনভাবে প্রশ্ন করতে পারবেন।
        </p>
      </div>

      <div class="qa-card">
        <div class="font-bold text-slate-900">ভুল উত্তর থাকলে কি করবেন?</div>
        <p class="mt-2 text-sm text-slate-600">
          রিভিউ/সংশোধনের মাধ্যমে আপডেট করা হবে। ভবিষ্যতে রিপোর্ট/ফিডব্যাক অপশন যোগ হবে ইনশাআল্লাহ।
        </p>
      </div>

      <div class="qa-card">
        <div class="font-bold text-slate-900">কোনো ব্যক্তিগত তথ্য দেয়া লাগবে?</div>
        <p class="mt-2 text-sm text-slate-600">
          না। তবে নোটিফিকেশনের জন্য মোবাইল নম্বর দিলে সুবিধা হবে (আপনার সেটিংস অনুযায়ী)।
        </p>
      </div>
    </div>
  </div>

  {{-- CTA --}}
  <div class="mt-6 qa-card qa-card-hover overflow-hidden p-0">
    <div class="p-6 md:p-8 bg-gradient-to-r from-slate-900 via-blue-800 to-cyan-500 text-white">
      <div class="text-xl md:text-2xl font-extrabold">আপনার প্রশ্ন আছে?</div>
      <div class="mt-2 text-sm text-white/90 max-w-2xl">
        ইসলাম সম্পর্কিত যেকোনো প্রশ্ন সুন্দরভাবে লিখে পাঠান। যাচাই-বাছাই শেষে উত্তর প্রকাশ করা হবে ইনশাআল্লাহ।
      </div>

      <div class="mt-5 flex flex-col sm:flex-row gap-2">
        <a href="{{ route('ask') }}" class="qa-btn qa-btn-outline bg-white/10 border-white/30 text-white hover:bg-white/15">
          প্রশ্ন করুন
        </a>
        <a href="{{ route('answers.index') }}" class="qa-btn qa-btn-outline bg-white/10 border-white/30 text-white hover:bg-white/15">
          উত্তর পড়ুন
        </a>
      </div>
    </div>
  </div>
@endsection
