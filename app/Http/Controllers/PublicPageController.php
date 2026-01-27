<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Page;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicPageController extends Controller
{
    private function viewer()
    {
        // ✅ multi-guard support (web + admin)
         return Auth::user();
    }

    private function canSeeAsker(): bool
    {
        $u = $this->viewer();
        if (!$u) return false;

        // 1) If model has isAdmin() use it (best)
        if (method_exists($u, 'isAdmin')) {
            return (bool) $u->isAdmin();
        }

        // 2) Flag columns
        foreach ((array) config('qa.admin_flag_columns', []) as $col) {
            if (isset($u->{$col}) && (bool) $u->{$col}) return true;
        }

        // 3) Role string columns
        foreach ((array) config('qa.admin_role_string_columns', []) as $col) {
            $val = strtolower((string) ($u->{$col} ?? ''));
            if (in_array($val, ['admin', 'super_admin', 'owner'], true)) return true;
        }

        // 4) role_id check via config ids
        $roleId = (int) ($u->role_id ?? 0);
        $adminRoleIds = (array) config('qa.admin_role_ids', []);
        if ($roleId > 0 && in_array($roleId, $adminRoleIds, true)) return true;

        // 5) email fallback
        $emails = array_map('strtolower', (array) config('qa.admin_emails', []));
        if (!empty($u->email) && in_array(strtolower($u->email), $emails, true)) return true;

        return false;
    }


    public function home(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $homeFeatured = Page::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('slug', 'home_featured')
            ->first();

        $categories = Category::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name_bn', 'slug']);

        $canSeeAsker = $this->canSeeAsker();

        $featuredBase = Question::query()
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
            ->limit(3);

        // ✅ Admin হলে column limit দেবেন না (যাতে asker columns যাই থাকুক আসতে পারে)
        $featured = $canSeeAsker
            ? $featuredBase->get()
            : $featuredBase->get([
                'id',
                'category_id',
                'slug',
                'title',
                'body_html',
                'published_at',
                'view_count',
                'created_at',
            ]);

        $cardsBase = Question::query()
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

        if ($q !== '') {
            $cardsBase->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                    ->orWhere('body_html', 'like', "%{$q}%");
            });
        }

        $cardsBase->orderByDesc('published_at')->orderByDesc('id')->limit(12);

        $cards = $canSeeAsker
            ? $cardsBase->get()
            : $cardsBase->get(['id', 'category_id', 'slug', 'title', 'body_html', 'published_at', 'created_at']);

        return view('pages.home.index', compact(
            'homeFeatured',
            'categories',
            'featured',
            'cards',
            'q',
            'canSeeAsker'
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
