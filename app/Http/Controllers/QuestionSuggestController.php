<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionSuggestController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 3) {
            return response()->json(['data' => []]);
        }

        // শুধু published প্রশ্ন + published উত্তর থাকলে suggest (আপনার requirement অনুযায়ী)
        $rows = Question::query()
            ->select(['id','slug','title','published_at'])
            ->where('status', 'published')
            ->where('title', 'like', '%' . $q . '%')
            ->whereHas('answer', function ($a) {
                $a->where('status', 'published');
            })
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();

        // confidence (optional but useful for toast trigger)
        $data = $rows->map(function ($row) use ($q) {
            $percent = 0.0;
            similar_text(mb_strtolower($q), mb_strtolower($row->title), $percent);

            return [
                'id' => $row->id,
                'title' => $row->title,
                'slug' => $row->slug,
                'date' => optional($row->published_at)->format('d M, Y'),
                'url' => route('questions.show', ['slug' => $row->slug]),
                'confidence' => round($percent / 100, 2),
            ];
        })->values();

        return response()->json(['data' => $data]);
    }
}
