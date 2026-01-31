<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Category;
use App\Models\Question;
use App\Models\Answer;
use App\Models\User;

class DummyQaSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ IMPORTANT: Seeder run এর সময় AnswerPublished event trigger OFF
        Event::fake([
            \App\Events\AnswerPublished::class,
        ]);

        // already seeded? (simple guard)
        try {
            if (class_exists(Question::class) && Question::query()->where('title', 'like', '%ডামি:%')->exists()) {
                $this->command?->warn('Dummy QA already exists. Skipping.');
                return;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        DB::beginTransaction();

        try {
            // ✅ categories ensure
            $categories = $this->ensureCategories();
            if (empty($categories)) {
                $this->command?->warn('No categories available. DummyQaSeeder skipped.');
                DB::rollBack();
                return;
            }

            // ✅ admin/user id (optional)
            $adminId = null;
            try {
                if (class_exists(User::class)) {
                    $adminId = User::query()->orderBy('id')->value('id');
                }
            } catch (\Throwable $e) {
                $adminId = null;
            }

            $now = Carbon::now();

            // ✅ Core dummy dataset (mix)
            $dataset = [
                [
                    'title' => 'ডামি: নামাজে সুরা ফাতিহা না পড়লে কি নামাজ হবে?',
                    'body'  => $this->bodyHtml([
                        'নামাজে সুরা ফাতিহা পড়া সম্পর্কে দলিল ও মতামত জানতে চাই।',
                        'আমি নতুন শিখছি—সহজ ভাষায় বুঝিয়ে বলবেন।',
                    ]),
                    'status' => 'published',
                    'answer' => $this->answerHtml([
                        'সংক্ষেপে: অধিকাংশ আলেমের মতে প্রত্যেক রাকাতে সুরা ফাতিহা পড়া ওয়াজিব/রুকন।',
                        'জামাতে ইমামের পেছনে পড়া–না পড়া নিয়ে মতভেদ আছে। স্থানীয় আলেমের নির্দেশনা অনুযায়ী আমল করুন।',
                        'বিশদ জানতে নির্ভরযোগ্য ফিকহ/হাদিস কিতাব দেখুন।',
                    ]),
                ],
                [
                    'title' => 'ডামি: রোজায় ভুলে পানি খেলে কি রোজা ভেঙে যায়?',
                    'body'  => $this->bodyHtml([
                        'রোজা অবস্থায় ভুলে পানি পান হয়ে গেছে। এখন কী করব?',
                    ]),
                    'status' => 'published',
                    'answer' => $this->answerHtml([
                        'ভুলে খাওয়া/পান করা হলে রোজা ভাঙে না—মনে পড়ার সাথে সাথে থেমে যাবেন।',
                        'রোজা পূর্ণ করবেন এবং আল্লাহর কাছে ক্ষমা চাইবেন।',
                    ]),
                ],
                [
                    'title' => 'ডামি: যাকাত কাদের উপর ফরজ হয়?',
                    'body'  => $this->bodyHtml([
                        'আমার কিছু সঞ্চয় আছে—যাকাত কখন ফরজ হবে বুঝতে চাই।',
                        'নিসাব/হাওল সম্পর্কে জানাবেন।',
                    ]),
                    'status' => 'published',
                    'answer' => $this->answerHtml([
                        'নিসাব পরিমাণ সম্পদ হলে এবং এক চন্দ্র বছর পূর্ণ হলে যাকাত ফরজ হয়।',
                        'নগদ/স্বর্ণ/ব্যবসায়িক পণ্য—ধরনভেদে হিসাব ভিন্ন হতে পারে।',
                    ]),
                ],
                [
                    'title' => 'ডামি: দোয়া কবুল হওয়ার উত্তম সময় কোনগুলো?',
                    'body'  => $this->bodyHtml([
                        'কোন কোন সময়ে দোয়া কবুল হয়—একটু তালিকা আকারে বলবেন?',
                    ]),
                    'status' => 'published',
                    'answer' => $this->answerHtml([
                        'তাহাজ্জুদের শেষ রাত, সিজদা, আযান–ইকামতের মাঝে, জুমার দিনের বিশেষ সময় ইত্যাদি।',
                        'দোয়ার আদব: ইখলাস, হালাল রিজিক, তওবা, তাড়াহুড়ো না করা।',
                    ]),
                ],

                // pending samples
                [
                    'title' => 'ডামি: ওযু ভাঙার কারণগুলো কি কি?',
                    'body'  => $this->bodyHtml([
                        'ওযু ভাঙে এমন বিষয়গুলো সহজ তালিকা চাই।',
                    ]),
                    'status' => 'pending',
                    'answer' => null,
                ],
                [
                    'title' => 'ডামি: মোবাইলে কুরআন পড়ার আদব কী?',
                    'body'  => $this->bodyHtml([
                        'মোবাইলে কুরআন পড়লে কি অযু লাগবে? কী কী খেয়াল রাখা উচিত?',
                    ]),
                    'status' => 'pending',
                    'answer' => null,
                ],

                // rejected
                [
                    'title' => 'ডামি: (রিভিউ দরকার) একটি অসম্পূর্ণ/অস্পষ্ট প্রশ্ন',
                    'body'  => $this->bodyHtml([
                        'আমি ঠিকমতো প্রশ্ন লিখিনি—এটা শুধু ডেমো হিসেবে আছে।',
                    ]),
                    'status' => 'rejected',
                    'answer' => null,
                ],
            ];

            // ✅ Add more auto-generated (total ~25)
            $dataset = array_merge($dataset, $this->generateMore(18));

            foreach ($dataset as $i => $row) {
                $category = $categories[$i % count($categories)];

                $askerName  = $this->randomName();
                $askerPhone = $this->randomBdPhone();
                $askerEmail = (rand(0, 1) ? $this->randomEmail($askerName) : null);

                $createdAt = $now->copy()->subDays(rand(1, 120))->subMinutes(rand(0, 600));

                $publishedAt = ($row['status'] === 'published')
                    ? $createdAt->copy()->addHours(rand(2, 72))
                    : null;

                $title = trim((string)($row['title'] ?? 'ডামি: (শিরোনাম নেই)'));
                $titleHash = hash('sha256', Str::of($title)->lower()->squish()->toString());

                // ✅ Create Question
                $q = Question::create([
                    'category_id'  => $category->id,
                    'slug'         => null,
                    'title'        => $title,
                    'body_html'    => (string)($row['body'] ?? ''),
                    'asker_name'   => $askerName,
                    'asker_phone'  => $askerPhone,
                    'asker_email'  => $askerEmail,
                    'status'       => $row['status'],
                    'published_at' => $publishedAt,
                    'view_count'   => ($row['status'] === 'published') ? rand(10, 500) : 0,
                    'title_hash'   => $titleHash,
                    'created_at'   => $createdAt,
                    'updated_at'   => $createdAt,
                ]);

                // ✅ slug set: q-{id}
                $q->forceFill(['slug' => 'q-' . $q->id])->save();

                // ✅ Create Answer for published ones (Seeder should not notify because Event faked)
                if ($row['status'] === 'published' && !empty($row['answer'])) {
                    Answer::create([
                        'question_id' => $q->id,
                        'answer_html' => (string)$row['answer'],
                        'status'      => 'published',
                        'answered_by' => $adminId,
                        'answered_at' => $publishedAt ?? $now,
                        'created_at'  => $publishedAt ?? $now,
                        'updated_at'  => $publishedAt ?? $now,
                    ]);
                }
            }

            DB::commit();
            $this->command?->info('✅ DummyQaSeeder completed! (events faked, no SMS/Email sent)');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function ensureCategories(): array
    {
        $defaults = [
            ['name_bn' => 'আকিদা',      'slug' => 'aqidah',   'sort_order' => 1, 'is_active' => 1],
            ['name_bn' => 'সালাত',      'slug' => 'salah',    'sort_order' => 2, 'is_active' => 1],
            ['name_bn' => 'রোযা',       'slug' => 'sawm',     'sort_order' => 3, 'is_active' => 1],
            ['name_bn' => 'যাকাত',      'slug' => 'zakat',    'sort_order' => 4, 'is_active' => 1],
            ['name_bn' => 'হজ',         'slug' => 'hajj',     'sort_order' => 5, 'is_active' => 1],
            ['name_bn' => 'আদব আখলাক',  'slug' => 'adab',     'sort_order' => 6, 'is_active' => 1],
        ];

        if (!class_exists(Category::class)) return [];

        $hasAny = Category::query()->whereNull('deleted_at')->exists();

        if (!$hasAny) {
            foreach ($defaults as $d) {
                Category::create($d);
            }
        }

        return Category::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->all();
    }

    private function bodyHtml(array $lines): string
    {
        $out = '';
        foreach ($lines as $l) {
            $out .= '<p>' . e($l) . '</p>';
        }
        $out .= '<ul><li>দলিলসহ উত্তর চাই</li><li>সহজ ভাষায় ব্যাখ্যা</li></ul>';
        return $out;
    }

    private function answerHtml(array $lines): string
    {
        $out = '<div class="qa-answer">';
        foreach ($lines as $l) {
            $out .= '<p>' . e($l) . '</p>';
        }
        $out .= '<p><strong>নোট:</strong> প্রয়োজনে স্থানীয় আলেম/মুফতির সাথে পরামর্শ করুন।</p>';
        $out .= '</div>';
        return $out;
    }

    private function generateMore(int $n): array
    {
        $titles = [
            'ডামি: সিজদায়ে সাহু কখন দিতে হয়?',
            'ডামি: ফজরের সুন্নত বাদ দিলে কি কাজা করতে হবে?',
            'ডামি: মিসওয়াকের ফজিলত কী?',
            'ডামি: সফরে নামাজ কসর করার নিয়ম কী?',
            'ডামি: গীবত থেকে বাঁচার উপায় কী?',
            'ডামি: ইসলামি দৃষ্টিতে প্রতিবেশীর হক কী?',
            'ডামি: কুরআন তিলাওয়াতের আদব কী?',
            'ডামি: সদকা গোপনে দিলে কী ফজিলত?',
            'ডামি: রাতে ঘুমানোর আগে কোন দোয়া পড়ব?',
            'ডামি: নারীদের জন্য হিজাবের বিধান সংক্ষেপে?',
        ];

        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $t = $titles[$i % count($titles)];
            $roll = rand(1, 100);
            $status = ($roll <= 65) ? 'published' : (($roll <= 85) ? 'pending' : 'rejected');

            $out[] = [
                'title'  => $t,
                'body'   => $this->bodyHtml(['এই বিষয়ে সংক্ষেপে ব্যাখ্যা চাই।', 'দয়া করে দলিলসহ বলবেন।']),
                'status' => $status,
                'answer' => ($status === 'published')
                    ? $this->answerHtml(['এটি একটি ডামি উত্তর।', 'প্রকৃত উত্তরের জন্য কিতাব/আলেমদের ব্যাখ্যা অনুসরণ করুন।'])
                    : null,
            ];
        }
        return $out;
    }

    private function randomName(): string
    {
        $names = ['আবদুল্লাহ', 'মুহাম্মদ', 'আবু বকর', 'ওমর', 'উসমান', 'আলী', 'হাসান', 'হুসাইন', 'আরিফ', 'সাকিব', 'রাকিব'];
        return $names[array_rand($names)];
    }

    private function randomBdPhone(): string
    {
        $prefixes = ['013', '014', '015', '016', '017', '018', '019'];
        $p = $prefixes[array_rand($prefixes)];
        $rest = str_pad((string)rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        return $p . $rest;
    }

    private function randomEmail(string $name): string
    {
        $slug = Str::slug($name) ?: 'user';
        $domains = ['example.com', 'mail.com', 'demo.test'];
        return $slug . rand(10, 999) . '@' . $domains[array_rand($domains)];
    }
}
