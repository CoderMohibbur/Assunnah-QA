<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\Request;

class PublicAnswerController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim((string) $request->get('q', ''));
        $cat  = (string) $request->get('cat', '');   // slug বা id
        $sort = (string) $request->get('sort', 'newest');

        // ✅ category list (active only)
        $categories = Category::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name_bn', 'slug']);

        // ✅ Only answered + published answers
        $rows = Question::query()
            ->with([
                'category:id,name_bn,slug',
                'answer' => function ($q) {
                    $q->whereNull('deleted_at')
                      ->where('status', 'published');
                },
                'answer.answeredBy:id,name',
            ])
            ->whereNull('deleted_at')
            ->whereHas('answer', function ($q) {
                $q->whereNull('deleted_at')
                  ->where('status', 'published');
            });

        // ✅ category filter (cat=slug OR cat=id)
        if ($cat !== '') {
            if (ctype_digit($cat)) {
                $rows->where('category_id', (int) $cat);
            } else {
                $rows->whereHas('category', fn($cq) => $cq->where('slug', $cat));
            }
        }

        // ✅ search filter
        if ($q !== '') {
            $rows->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('body_html', 'like', "%{$q}%");
            });
        }

        // ✅ sorting
        if ($sort === 'views') {
            $rows->orderByDesc('view_count')->orderByDesc('id');
        } elseif ($sort === 'oldest') {
            // answered_at না থাকলে updated_at
            $rows->orderByRaw('COALESCE((select answered_at from answers where answers.question_id = questions.id limit 1), questions.updated_at) asc')
                 ->orderBy('id', 'asc');
        } else {
            $rows->orderByRaw('COALESCE((select answered_at from answers where answers.question_id = questions.id limit 1), questions.updated_at) desc')
                 ->orderBy('id', 'desc');
        }

        $answers = $rows->paginate(12)->withQueryString();

        return view('pages.answers.index', compact('answers', 'categories', 'q', 'cat', 'sort'));
    }
}
