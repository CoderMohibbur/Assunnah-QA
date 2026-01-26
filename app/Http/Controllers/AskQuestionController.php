<?php

namespace App\Http\Controllers;

use App\Http\Requests\AskQuestionRequest;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class AskQuestionController extends Controller
{
    /**
     * ✅ Ask page with categories
     * Route: GET /ask
     */
    public function create()
    {
        $categories = Category::query()
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name_bn', 'slug']);

        return view('pages.ask.index', compact('categories'));
    }

    /**
     * ✅ Store question (pending)
     * Route: POST /ask
     */
    public function store(AskQuestionRequest $request)
    {
        $data = $request->validated();

        // ✅ sanitize title
        $safeTitle = trim(strip_tags((string) $data['title']));

        // ✅ sanitize body (purifier)
        $rawBody  = (string) $data['body'];
        $safeBody = $this->sanitizeBody($rawBody);

        // ✅ helpful hash for duplicates
        $titleHash = hash('sha256', Str::of($safeTitle)->lower()->squish()->toString());

        // ✅ create pending question
        $q = Question::create([
            'category_id'  => (int) $data['category_id'],
            'slug'         => null,
            'title'        => $safeTitle,
            'body_html'    => $safeBody,
            'asker_name'   => $data['name'],
            'asker_phone'  => $data['phone'],
            'asker_email'  => $data['email'] ?? null,
            'status'       => 'pending',
            'published_at' => null,
            'view_count'   => 0,
            'title_hash'   => $titleHash,
        ]);

        // ✅ slug set: q-{id}
        $q->forceFill(['slug' => 'q-' . $q->id])->save();

        return redirect()->route('ask.thanks', ['id' => $q->id]);
    }

    private function sanitizeBody(string $html): string
    {
        try {
            // যদি qa_body profile থাকে, সেটাই use করবে
            return (string) Purifier::clean($html, 'qa_body');
        } catch (\Throwable $e) {
            try {
                return (string) Purifier::clean($html);
            } catch (\Throwable $e2) {
                return strip_tags($html, '<p><br><b><strong><i><em><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><a>');
            }
        }
    }
}
