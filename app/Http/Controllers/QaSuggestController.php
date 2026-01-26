<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class QaSuggestController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $raw = (string) $request->query('title', '');
        $title = trim(strip_tags($raw));
        $title = Str::of($title)->squish()->toString();

        $min = (int) config('qa_suggest.min_chars', 4);
        if (mb_strlen($title) < $min) {
            return response()->json(['data' => []]);
        }

        $needle = mb_strtolower($title);

        // ✅ cache (same query বারবার আসলে দ্রুত রেসপন্স)
        $cacheKey = 'qa_suggest:' . hash('sha1', $needle);
        $ttl = 30; // seconds

        $data = Cache::remember($cacheKey, $ttl, function () use ($needle) {

            // ✅ Phase-1 fallback dataset (Phase-2: DB query দিয়ে replace হবে)
            $seed = [
                ['id'=>122,'title'=>'ইচ্ছা করে কেউ স্ত্রী সহবাস করে তার রোযা ভেঙে যায়...'],
                ['id'=>121,'title'=>'মাথা ধোয়া না করে কুল্লি হয়, ...'],
                ['id'=>120,'title'=>'আল্লাহর কালামের সাথে আদব ...'],
                ['id'=>119,'title'=>'যাকাত কাদের উপর ফরজ...'],
                ['id'=>118,'title'=>'সালাতের সময়সীমা...'],
                ['id'=>117,'title'=>'রোযা অবস্থায় ইনজেকশন...'],
                ['id'=>116,'title'=>'জানাজা নামাজের নিয়ম...'],
                ['id'=>115,'title'=>'কুরআন তিলাওয়াতের আদব...'],
                ['id'=>114,'title'=>'সফরে রোজা রাখা...'],
            ];

            $threshold = (float) config('qa_suggest.threshold', 45);
            $boost = (float) config('qa_suggest.substring_boost_score', 72);
            $limit = (int) config('qa_suggest.limit', 5);

            $scored = [];

            foreach ($seed as $q) {
                $hay = mb_strtolower($q['title']);

                // Similarity score
                similar_text($needle, $hay, $pct);

                // substring bonus
                if (str_contains($hay, $needle) || str_contains($needle, $hay)) {
                    $pct = max($pct, $boost);
                }

                if ($pct >= $threshold) {
                    $slug = 'q-' . $q['id'];

                    $scored[] = [
                        'id'         => (int) $q['id'],
                        'title'      => (string) $q['title'],
                        'slug'       => $slug,
                        'url'        => route('questions.show', ['slug' => $slug]),
                        'score'      => round($pct, 2),              // 0-100
                        'confidence' => round($pct / 100, 2),        // 0-1 ✅ (front এ এটা লাগে)
                        'date'       => null,                        // later DB-driven হলে fill করবেন
                    ];
                }
            }

            usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

            return array_slice($scored, 0, $limit);
        });

        return response()->json(['data' => $data]);
    }
}
