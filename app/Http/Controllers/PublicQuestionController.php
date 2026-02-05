<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicQuestionController extends Controller
{
    private function canSeeAsker(): bool
    {
        if (!auth()->check()) return false;

        $u = auth()->user();
        if (method_exists($u, 'isAdmin')) return (bool) $u->isAdmin();

        return false;
    }

    /**
     * ✅ Sidebar categories helper (reuse)
     */
    private function sidebarCategories()
    {
        return Category::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name_bn', 'slug']);
    }

    /**
     * ✅ All published questions list (DB driven)
     */
    public function index(Request $request)
    {
        $q          = trim((string) $request->get('q', ''));
        $categoryId = (string) $request->get('category_id', '');
        $sort       = (string) $request->get('sort', 'newest');
        $answered   = (string) $request->get('answered', '');

        $allowedSort = ['newest', 'oldest', 'views'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'newest';
        }

        $canSeeAsker = $this->canSeeAsker();

        $baseCols = [
            'id',
            'published_serial',
            'category_id',
            'slug',
            'title',
            'body_html',
            'status',
            'asker_name',
            'published_at',
            'view_count',
            'created_at',
            'updated_at',
        ];
        $askerCols = $canSeeAsker ? ['asker_name', 'asker_phone', 'asker_email'] : [];

        $query = Question::query()
            ->select(array_merge($baseCols, $askerCols))
            ->with([
                'category:id,name_bn,slug',
                'answer' => function ($q) {
                    $q->whereNull('deleted_at')->where('status', 'published');
                },
                'answer.answeredBy:id,name',
            ])
            ->whereNull('deleted_at')
            ->where('status', 'published');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('body_html', 'like', "%{$q}%");
            });
        }

        if ($categoryId !== '') {
            $query->where('category_id', (int) $categoryId);
        }

        if ($answered === '1') {
            $query->whereHas('answer', function ($a) {
                $a->whereNull('deleted_at')->where('status', 'published');
            });
        }

        if ($sort === 'views') {
            $query->orderByDesc('view_count')
                ->orderByDesc('published_serial')
                ->orderByDesc('published_at')
                ->orderByDesc('id');
        } elseif ($sort === 'oldest') {
            $query->orderBy('published_serial')
                ->orderBy('published_at')
                ->orderBy('id');
        } else {
            $query->orderByDesc('published_serial')
                ->orderByDesc('published_at')
                ->orderByDesc('id');
        }

        $questions = $query->paginate(12)->withQueryString();

        $categories = $this->sidebarCategories();

        return view('pages.questions.index', compact(
            'questions',
            'categories',
            'q',
            'categoryId',
            'sort',
            'answered',
            'canSeeAsker'
        ));
    }

    /**
     * ✅ Question detail
     */
    public function show(string $slug)
    {
        $canSeeAsker = $this->canSeeAsker();

        $baseCols = [
            'id',
            'published_serial',
            'category_id',
            'slug',
            'title',
            'body_html',
            'status',
            'asker_name',
            'published_at',
            'view_count',
            'created_at',
            'updated_at',
        ];
        $askerCols = $canSeeAsker ? ['asker_name', 'asker_phone', 'asker_email'] : [];

        $query = Question::query()
            ->select(array_merge($baseCols, $askerCols))
            ->with([
                'category:id,name_bn,slug',
                'answer' => function ($q) {
                    $q->whereNull('deleted_at')->where('status', 'published');
                },
                'answer.answeredBy:id,name',
            ])
            ->whereNull('deleted_at')
            ->where('status', 'published');

        $question = null;

        if (preg_match('/^q-(\d+)$/', $slug, $m)) {
            $num = (int) $m[1];

            // ✅ 1) first try exact slug match (important!)
            $question = (clone $query)->where('slug', $slug)->first();

            // ✅ 2) fallback: treat as ID
            if (!$question) {
                $question = (clone $query)->whereKey($num)->first();
            }

            // ✅ 3) fallback: treat as published_serial (only if ID not found in published)
            if (!$question) {
                $question = (clone $query)->where('published_serial', $num)->firstOrFail();
            }
        } else {
            $question = (clone $query)->where('slug', $slug)->firstOrFail();
        }

        $question->increment('view_count');

        $categories = $this->sidebarCategories();

        return view('pages.questions.show', compact('question', 'canSeeAsker', 'categories'));
    }


    /**
     * ✅ Category-wise published questions
     */
    public function category(string $slug)
    {
        $canSeeAsker = $this->canSeeAsker();

        $category = Category::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('slug', $slug)
            ->firstOrFail();

        $baseCols = [
            'id',
            'published_serial',
            'category_id',
            'slug',
            'title',
            'body_html',
            'status',
            'published_at',
            'view_count',
            'created_at',
            'updated_at',
        ];
        $askerCols = $canSeeAsker ? ['asker_name', 'asker_phone', 'asker_email'] : [];

        $questions = Question::query()
            ->select(array_merge($baseCols, $askerCols))
            ->with([
                'category:id,name_bn,slug',
                'answer' => function ($q) {
                    $q->whereNull('deleted_at')->where('status', 'published');
                },
                'answer.answeredBy:id,name',
            ])
            ->whereNull('deleted_at')
            ->where('status', 'published')
            ->where('category_id', $category->id)
            ->orderByDesc('published_serial')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        // ✅ (Recommended) category page-এও sidebar categories pass
        $categories = $this->sidebarCategories();

        return view('pages.categories.show', compact('category', 'questions', 'canSeeAsker', 'categories'));
    }
}
