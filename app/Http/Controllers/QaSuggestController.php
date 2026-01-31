<?php

namespace App\Http\Controllers;

use App\Models\Question;
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

        $cacheKey = 'qa_suggest:' . hash('sha1', $needle);
        $ttl = 30;

        $data = Cache::remember($cacheKey, $ttl, function () use ($needle) {

            $threshold = (float) config('qa_suggest.threshold', 45);
            $boost     = (float) config('qa_suggest.substring_boost_score', 72);
            $limit     = (int) config('qa_suggest.limit', 5);

            /**
             * ✅ Any-word matching:
             * "রোজা ভুলে পানি" => ["রোজা","ভুলে","পানি"]
             * 2+ length word only
             */
            $words = preg_split('/\s+/u', trim($needle)) ?: [];
            $words = array_values(array_filter($words, fn ($w) => mb_strlen($w) >= 2));

            // Safety fallback (shouldn't happen usually)
            if (empty($words)) {
                $words = [$needle];
            }

            // ✅ Candidates (DB): title OR title_bn match by ANY word
            $candidates = Question::query()
                ->select(['id', 'slug', 'title', 'title_bn', 'original_lang', 'created_at'])
                ->whereNotNull('slug')
                ->where(function ($q) use ($words) {
                    foreach ($words as $w) {
                        $escapedW = str_replace(['%', '_'], ['\%', '\_'], $w);

                        $q->orWhere('title', 'like', "%{$escapedW}%")
                          ->orWhere('title_bn', 'like', "%{$escapedW}%");
                    }
                })
                ->latest()
                ->limit(80)
                ->get();

            // ✅ DB fallback (dummy না): latest কিছু নিয়ে scoring
            if ($candidates->isEmpty()) {
                $candidates = Question::query()
                    ->select(['id', 'slug', 'title', 'title_bn', 'original_lang', 'created_at'])
                    ->whereNotNull('slug')
                    ->latest()
                    ->limit(80)
                    ->get();
            }

            $scored = [];

            foreach ($candidates as $q) {
                // ✅ Display title preference
                $displayTitle = $q->title_bn ?: $q->title;

                $hay = mb_strtolower((string) $displayTitle);

                // Similarity score (needle vs full title)
                similar_text($needle, $hay, $pct);

                // ✅ substring bonus (any-word match / contains)
                foreach ($words as $w) {
                    if (mb_strlen($w) >= 2 && str_contains($hay, $w)) {
                        $pct = max($pct, $boost);
                        break;
                    }
                }

                if ($pct >= $threshold) {
                    $scored[] = [
                        'id'         => (int) $q->id,
                        'title'      => (string) $displayTitle,
                        'slug'       => (string) $q->slug,
                        'url'        => route('questions.show', ['slug' => $q->slug]),
                        'score'      => round($pct, 2),
                        'confidence' => round($pct / 100, 2),
                        'date'       => optional($q->created_at)->toDateString(),
                    ];
                }
            }

            usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

            return array_slice($scored, 0, $limit);
        });

        return response()->json(['data' => $data]);
    }
}
