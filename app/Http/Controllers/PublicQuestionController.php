<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\Request;

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

        // ✅ Only select necessary columns (PII only for admin)
        $baseCols = [
            'id',
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

        // ✅ search
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('body_html', 'like', "%{$q}%");
            });
        }

        // ✅ category filter
        if ($categoryId !== '') {
            $query->where('category_id', (int) $categoryId);
        }

        // ✅ answered only
        if ($answered === '1') {
            $query->whereHas('answer', function ($a) {
                $a->whereNull('deleted_at')->where('status', 'published');
            });
        }

        // ✅ sorting
        if ($sort === 'views') {
            $query->orderByDesc('view_count')->orderByDesc('published_at')->orderByDesc('id');
        } elseif ($sort === 'oldest') {
            $query->orderBy('published_at')->orderBy('id');
        } else {
            $query->orderByDesc('published_at')->orderByDesc('id');
        }

        $questions = $query->paginate(12)->withQueryString();

        $categories = Category::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name_bn', 'slug']);

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
            'id','category_id','slug','title','body_html','status',
            'published_at','view_count','created_at','updated_at',
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

        if (preg_match('/^q-(\d+)$/', $slug, $m)) {
            $question = $query->where('id', (int) $m[1])->firstOrFail();
        } else {
            $question = $query->where('slug', $slug)->firstOrFail();
        }

        $question->increment('view_count');

        // view এ চাইলে ব্যবহার করবেন (এখন না হলেও ক্ষতি নাই)
        return view('pages.questions.show', compact('question', 'canSeeAsker'));
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
            'id','category_id','slug','title','body_html','status',
            'published_at','view_count','created_at','updated_at',
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
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('pages.categories.show', compact('category', 'questions', 'canSeeAsker'));
    }
}
