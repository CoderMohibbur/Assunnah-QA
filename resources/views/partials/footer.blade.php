<footer class="border-t bg-white">
    <div class="qa-container py-8 md:py-10">
        <div class="grid gap-8 md:grid-cols-12">

            {{-- Brand --}}
            <div class="md:col-span-5 text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start gap-3">
                    <div
                        class="h-10 w-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-extrabold">
                        QA
                    </div>
                    <div>
                        <div class="font-extrabold text-slate-900 text-lg leading-tight">As Sunnah Q&amp;A</div>
                        <div class="text-xs text-slate-500">Islamic Knowledge Platform</div>
                    </div>
                </div>

                <p class="mt-4 text-sm text-slate-600 leading-relaxed max-w-md mx-auto md:mx-0">
                    প্রশ্ন-উত্তর ভিত্তিক ইসলামিক জ্ঞানচর্চার প্ল্যাটফর্ম। সাধারণ মানুষ প্রশ্ন করতে পারবেন,
                    এবং যাচাই-বাছাই শেষে উত্তর প্রকাশ করা হবে ইনশাআল্লাহ।
                </p>

                <div class="mt-5 flex flex-wrap gap-2 justify-center md:justify-start">
                    <span class="qa-badge">ভেরিফাইড উত্তর</span>
                    <span class="qa-badge">ক্যাটাগরি ব্রাউজ</span>
                    <span class="qa-badge">সার্চ + ফিল্টার</span>
                </div>
            </div>

            {{-- Quick links --}}
            <div class="md:col-span-3">
                <div class="text-sm font-extrabold text-slate-900 text-center md:text-left">Quick Links</div>

                <div class="mt-4 space-y-2 text-sm">
                    <a class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50 transition"
                        href="{{ route('ask') }}">
                        <span class="text-slate-700 font-semibold">প্রশ্ন করুন</span>
                        <span class="text-slate-400 group-hover:text-slate-700">→</span>
                    </a>

                    <a class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50 transition"
                        href="{{ route('questions.index') }}">
                        <span class="text-slate-700 font-semibold">সকল প্রশ্ন</span>
                        <span class="text-slate-400 group-hover:text-slate-700">→</span>
                    </a>

                    <a class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 hover:bg-slate-50 transition"
                        href="{{ route('answers.index') }}">
                        <span class="text-slate-700 font-semibold">প্রশ্নের উত্তর</span>
                        <span class="text-slate-400 group-hover:text-slate-700">→</span>
                    </a>
                </div>
            </div>

            {{-- Support --}}
            <div class="md:col-span-4">
                <div class="text-sm font-extrabold text-slate-900 text-center md:text-left">Support</div>

                <div class="mt-4 qa-card bg-slate-50 border border-slate-200">
                    <div class="text-sm font-semibold text-slate-900">সহযোগিতা / যোগাযোগ</div>
                    <p class="mt-1 text-sm text-slate-600 leading-relaxed">
                        পরবর্তীতে ইমেইল/হেল্প সেকশন যুক্ত হবে ইনশাআল্লাহ। এখন আপডেটের জন্য নিয়মিত ভিজিট করুন।
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2 justify-center md:justify-start">
                        <a href="{{ route('about') }}" class="qa-btn qa-btn-outline px-4">
                            আমাদের সম্পর্কে
                        </a>
                        <a href="{{ route('ask') }}" class="qa-btn qa-btn-primary px-4">
                            প্রশ্ন করুন
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-10 pt-6 border-t border-slate-200 pb-[env(safe-area-inset-bottom)]">
            <div class="flex flex-col gap-2 text-center md:flex-row md:items-center md:justify-between md:text-left">
                <p class="text-xs text-slate-500">
                    © {{ date('Y') }} As Sunnah Q&amp;A — All rights reserved.
                </p>

                <p class="text-xs text-slate-500">
                    Developed by
                    <a href="https://japanbangladeshit.com" target="_blank" rel="noopener"
                        class="font-semibold text-blue-600 hover:text-blue-900 hover:underline underline-offset-4">
                        Japan Bangladesh IT
                    </a>
                </p>
            </div>
        </div>

    </div>
</footer>
