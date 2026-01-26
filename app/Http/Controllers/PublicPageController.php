<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Question;
use App\Models\Category;
use Illuminate\Http\Request;

class PublicPageController extends Controller
{
    public function home(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        // ✅ optional: home page dynamic content (আপনার আগেরটা)
        $homeFeatured = Page::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('slug', 'home_featured')
            ->first();

        // ✅ Categories (optional যদি home এ filter UI দিতে চান)
        $categories = Category::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name_bn', 'slug']);

        // ✅ Featured answered প্রশ্ন (সবচেয়ে বেশি ভিউ + লেটেস্ট)
        $featured = Question::query()
            ->with([
                'category:id,name_bn,slug',
                'answer' => function ($q) {
                    $q->whereNull('deleted_at')->where('status', 'published');
                },
                'answer.answeredBy:id,name',
            ])
            ->whereNull('deleted_at')
            ->where('status', 'published')
            ->whereHas('answer', function ($q) {
                $q->whereNull('deleted_at')->where('status', 'published');
            })
            ->orderByDesc('view_count')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get([
                'id',
                'category_id',
                'slug',
                'title',
                'body_html',
                'published_at',
                'view_count',
                'created_at',
            ]);

        // ✅ Home Cards (answered + published)
        $cardsQuery = Question::query()
            ->with([
                'category:id,name_bn,slug',
                'answer' => function ($q) {
                    $q->whereNull('deleted_at')->where('status', 'published');
                },
                'answer.answeredBy:id,name',
            ])
            ->whereNull('deleted_at')
            ->where('status', 'published')
            ->whereHas('answer', function ($q) {
                $q->whereNull('deleted_at')->where('status', 'published');
            });

        // ✅ Search
        if ($q !== '') {
            $cardsQuery->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                    ->orWhere('body_html', 'like', "%{$q}%");
            });
        }

        $cards = $cardsQuery
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get([
                'id',
                'category_id',
                'slug',
                'title',
                'body_html',
                'published_at',
                'created_at',
            ]);

        return view('pages.home.index', compact(
            'homeFeatured',
            'categories',
            'featured',
            'cards',
            'q'
        ));
    }

    public function about()
    {
        $about = Page::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('slug', 'about')
            ->first();

        return view('pages.about.index', compact('about'));
    }
}
