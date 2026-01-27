<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\Request;

class PublicAnswerController extends Controller
{
    private function canSeeAsker(): bool
    {
        // আপনার প্রজেক্টে admin guard নেই, তাই web auth user-ই যথেষ্ট
        if (!auth()->check()) return false;

        $u = auth()->user();

        // isAdmin() থাকলে সেটাই source of truth
        if (method_exists($u, 'isAdmin')) {
            return (bool) $u->isAdmin();
        }

        return false;
    }

    public function index(Request $request)
    {
        $q    = trim((string) $request->get('q', ''));
        $cat  = (string) $request->get('cat', '');   // slug বা id
        $sort = (string) $request->get('sort', 'newest');

        $canSeeAsker = $this->canSeeAsker();

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

        // ✅ non-admin হলে PII select না করা (best practice)
        $baseCols = [
            'id',
            'category_id',
            'slug',
            'title',
            'body_html',
            'published_at',
            'view_count',
            'created_at',
            'updated_at',
        ];

        $askerCols = $canSeeAsker ? ['asker_name', 'asker_phone', 'asker_email'] : [];

        $rows->select(array_merge($baseCols, $askerCols));

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
            $rows->orderByRaw('COALESCE((select answered_at from answers where answers.question_id = questions.id limit 1), questions.updated_at) asc')
                ->orderBy('id', 'asc');
        } else {
            $rows->orderByRaw('COALESCE((select answered_at from answers where answers.question_id = questions.id limit 1), questions.updated_at) desc')
                ->orderBy('id', 'desc');
        }

        $answers = $rows->paginate(12)->withQueryString();

        return view('pages.answers.index', compact(
            'answers',
            'categories',
            'q',
            'cat',
            'sort',
            'canSeeAsker'
        ));
    }
}
