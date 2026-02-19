<?php

namespace App\Http\Controllers;

use App\Http\Requests\AskQuestionRequest;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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


    public function upload(Request $request): JsonResponse
    {
        // Jodit কখনো files[], কখনো files পাঠাতে পারে
        $files = $request->file('files');

        if (!$files) {
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded.',
            ], 422);
        }

        $files = is_array($files) ? $files : [$files];

        // Limit
        if (count($files) > 3) {
            return response()->json([
                'success' => false,
                'message' => 'Max 3 files allowed.',
            ], 422);
        }

        // Manual validate for array of UploadedFile
        $v = Validator::make(['files' => $files], [
            'files'   => ['required', 'array', 'min:1', 'max:3'],
            'files.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => $v->errors()->first(),
                'errors'  => $v->errors(),
            ], 422);
        }

        $urls = [];

        foreach ($files as $file) {
            $path = $file->store('ask_uploads', 'public');
            $urls[] = Storage::disk('public')->url($path);
        }

        return response()->json([
            'success' => true,
            'files'   => $urls, // ✅ Jodit friendly (string urls)
        ]);
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

        $q = DB::transaction(function () use ($data, $safeTitle, $safeBody, $titleHash) {

            // ✅ create pending question (slug initially null)
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

            // ✅ generate unique slug like: q-7368, q-7368-2, q-7368-3 ...
            $base = 'q-' . $q->id;
            $slug = $base;
            $i    = 2;

            // withTrashed() দিলে soft-deleted row থাকলেও collision ধরা পড়বে
            while (
                Question::withTrashed()
                ->where('slug', $slug)
                ->where('id', '!=', $q->id)
                ->exists()
            ) {
                $slug = $base . '-' . $i;
                $i++;

                // safety guard (never infinite)
                if ($i > 200) {
                    $slug = $base . '-' . Str::lower(Str::random(6));
                    break;
                }
            }

            $q->forceFill(['slug' => $slug])->save();

            return $q;
        });

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
