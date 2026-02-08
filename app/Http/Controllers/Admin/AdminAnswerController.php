<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\AnswerPublished;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Database\QueryException;

class AdminAnswerController extends Controller
{
    /**
     * Save Answer Draft
     * Route: POST /admin/questions/{question}/answer/draft
     */
    public function saveDraft(Question $question, Request $request)
    {
        if ($question->deleted_at) abort(404);

        // ✅ Answer required, Question fields optional (if sent, we update)
        $data = $request->validate([
            'answer_html'  => ['required', 'string', 'min:10'],

            // optional question edits (only works if these fields come with the request)
            'title'        => ['sometimes', 'required', 'string', 'max:255'],
            'body_html'    => ['nullable', 'string', 'max:200000'],
            'category_id'  => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
            ],
        ]);

        $cleanAnswer = $this->sanitizeHtml((string) $data['answer_html']);

        // optional question payload
        $qPayload = $this->questionPayloadFromValidated($data);

        DB::transaction(function () use ($question, $cleanAnswer, $qPayload) {

            // ✅ lock question row for consistent update
            $q = Question::whereKey($question->id)->lockForUpdate()->firstOrFail();

            // ✅ Update question (if any edits sent)
            if (!empty($qPayload)) {
                $q->forceFill($qPayload)->save();
            }

            // ✅ Upsert answer (draft)
            Answer::updateOrCreate(
                ['question_id' => $q->id],
                [
                    'answered_by' => auth()->id(),
                    'answer_html' => $cleanAnswer,

                    // future i18n default bn copy
                    'answer_html_bn' => $cleanAnswer,
                    'answer_html_en' => null,
                    'answer_html_ar' => null,

                    'status'     => 'draft',
                    'answered_at' => null,
                ]
            );

            // ✅ If question was rejected, bring it back to pending for review
            if (($q->status ?? '') === 'rejected') {
                $q->forceFill([
                    'status'       => 'pending',
                    'published_at' => null,
                ])->save();
            }
        });

        return back()->with('success', 'Draft saved ✅');
    }

    /**
     * Allocate unique published_serial for a Question
     * (used in publish process with retries)
     */

    private function allocatePublishedSerial(Question $q, int $maxAttempts = 50): int
    {
        if (!empty($q->published_serial)) {
            return (int) $q->published_serial;
        }

        // start from current max+1
        $candidate = (Question::whereNotNull('published_serial')->lockForUpdate()->max('published_serial') ?? 0) + 1;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                // persist immediately so UNIQUE constraint protects us
                $q->forceFill(['published_serial' => $candidate])->save();
                return $candidate;
            } catch (QueryException $e) {

                $sqlState = (string) ($e->errorInfo[0] ?? '');
                $errNo    = (int)    ($e->errorInfo[1] ?? 0);

                // MySQL duplicate key: SQLSTATE 23000 / error 1062
                if ($sqlState === '23000' && $errNo === 1062) {
                    $q->refresh();

                    // maybe set by another request meanwhile
                    if (!empty($q->published_serial)) {
                        return (int) $q->published_serial;
                    }

                    $candidate++;       // try next number
                    usleep(20_000);     // 20ms backoff
                    continue;
                }

                throw $e; // other DB errors
            }
        }

        throw new \RuntimeException('Unique published_serial allocate failed after retries.');
    }


    /**
     * Publish Answer
     * Route: POST /admin/questions/{question}/answer/publish
     */
    public function publish(Question $question, Request $request)
    {
        if ($question->deleted_at) abort(404);

        if (($question->status ?? '') === 'rejected') {
            return back()->withErrors([
                'answer_html' => 'Rejected প্রশ্ন publish করা যাবে না। আগে approve/restore করুন।',
            ]);
        }

        // ✅ Answer required, Question fields optional (if sent, we update)
        $data = $request->validate([
            'answer_html'  => ['required', 'string', 'min:10'],

            // optional question edits (only works if these fields come with the request)
            'title'        => ['sometimes', 'required', 'string', 'max:255'],
            'body_html'    => ['nullable', 'string', 'max:200000'],
            'category_id'  => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
            ],

            // notify toggles
            'notify_sms'   => ['sometimes', 'boolean'],
            'notify_email' => ['sometimes', 'boolean'],
        ]);

        $cleanAnswer = $this->sanitizeHtml((string) $data['answer_html']);
        $qPayload    = $this->questionPayloadFromValidated($data);

        $notifySms   = $request->boolean('notify_sms', true);
        $notifyEmail = $request->boolean('notify_email', true);

        $answerId            = null;
        $wasPublishedBefore  = false;
        $didSave             = false;

        DB::transaction(function () use (
            $question,
            $cleanAnswer,
            $qPayload,
            &$answerId,
            &$wasPublishedBefore,
            &$didSave
        ) {
            // ✅ Lock question row (race-safe)
            $q = Question::whereKey($question->id)->lockForUpdate()->firstOrFail();

            $wasPublishedBefore = (($q->status ?? '') === 'published');

            // ✅ Update question edits FIRST (title/body/category) if provided
            if (!empty($qPayload)) {
                $q->forceFill($qPayload)->save();
            }

            // ✅ Allocate published serial only once
            if (empty($q->published_serial)) {
                $this->allocatePublishedSerial($q);
            }

            // ✅ Update existing answer OR create new (and keep answered_at if already set)
            $answer = Answer::where('question_id', $q->id)->lockForUpdate()->first();

            if ($answer) {
                $answer->forceFill([
                    'answered_by'    => auth()->id(),
                    'answer_html'    => $cleanAnswer,

                    'answer_html_bn' => $cleanAnswer,
                    'answer_html_en' => null,
                    'answer_html_ar' => null,

                    'status'         => 'published',
                ]);

                // keep old answered_at if already exists
                if (empty($answer->answered_at)) {
                    $answer->answered_at = now();
                }

                $answer->save();
            } else {
                $answer = Answer::create([
                    'question_id'    => $q->id,
                    'answered_by'    => auth()->id(),
                    'answer_html'    => $cleanAnswer,

                    'answer_html_bn' => $cleanAnswer,
                    'answer_html_en' => null,
                    'answer_html_ar' => null,

                    'status'         => 'published',
                    'answered_at'    => now(),
                ]);
            }

            $answerId = $answer->id;

            // ✅ Only set published_at when it was not published before
            if (!$wasPublishedBefore) {
                $q->forceFill([
                    'status'       => 'published',
                    'published_at' => now(),
                ])->save();
            }

            $didSave = true;
        });

        // ✅ Fire event after DB commit (always dispatch; listener will decide SMS/Email)
        DB::afterCommit(function () use ($answerId, $didSave, $notifySms, $notifyEmail) {
            if (!$didSave || !$answerId) return;

            $answer = Answer::with(['question'])->find($answerId);
            if ($answer) {
                event(new AnswerPublished($answer, $notifySms, $notifyEmail));
            }
        });

        $msg = $wasPublishedBefore
            ? 'Answer updated ✅'
            : 'Answer published ✅';

        return redirect()
            ->route('admin.questions.index', ['status' => 'published'])
            ->with('success', $msg);
    }





    /**
     * Build Question update payload from validated data
     * (only fields that exist in request will be applied)
     */
    private function questionPayloadFromValidated(array $data): array
    {
        $payload = [];

        if (array_key_exists('title', $data)) {
            $payload['title'] = trim((string) $data['title']);
        }

        // body_html may come as empty string => keep as null to avoid junk
        if (array_key_exists('body_html', $data)) {
            $raw = (string) ($data['body_html'] ?? '');
            $raw = trim($raw);
            $payload['body_html'] = $raw === '' ? null : $this->sanitizeHtml($raw);
        }

        if (array_key_exists('category_id', $data)) {
            $payload['category_id'] = (int) $data['category_id'];
        }

        return $payload;
    }

    /**
     * Sanitize WYSIWYG HTML
     * ✅ target="_blank" preserve + rel enforce
     */
    private function sanitizeHtml(string $html): string
    {
        $clean = $html;

        try {
            if (class_exists(Purifier::class)) {
                // ✅ Allow target="_blank" and common rel values
                $clean = (string) Purifier::clean($html, [
                    'Attr.AllowedFrameTargets' => ['_blank', '_self', '_parent', '_top'],
                    'Attr.AllowedRel'          => ['noopener', 'noreferrer', 'nofollow', 'ugc', 'sponsored'],
                ]);
            }
        } catch (\Throwable $e) {
            // ignore, fallback below
        }

        // Fallback basic allowlist (Purifier recommended in production)
        if ($clean === $html) {
            $clean = strip_tags(
                $html,
                '<p><br><b><strong><i><em><u><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><a><span><div>'
            );
        }

        // ✅ Ensure security rel for target blank links
        return $this->enforceRelForTargetBlank($clean);
    }

    /**
     * Ensure rel="noopener noreferrer" for target="_blank"
     */
    private function enforceRelForTargetBlank(string $html): string
    {
        if (stripos($html, '<a') === false) return $html;

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');

        // Wrap to avoid DOMDocument adding extra tags
        $wrapped = '<div>' . $html . '</div>';
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $links = $dom->getElementsByTagName('a');

        foreach ($links as $a) {
            $target = (string) $a->getAttribute('target');
            if ($target !== '_blank') continue;

            $rel = trim((string) $a->getAttribute('rel'));
            $parts = $rel !== '' ? preg_split('/\s+/', $rel) : [];
            $parts = array_filter(array_map('strtolower', $parts ?: []));

            foreach (['noopener', 'noreferrer'] as $need) {
                if (!in_array($need, $parts, true)) $parts[] = $need;
            }

            $a->setAttribute('rel', trim(implode(' ', $parts)));
        }

        $out = $dom->saveHTML();

        // unwrap <div>...</div>
        if (preg_match('/^<div>(.*)<\/div>$/s', $out, $m)) {
            return $m[1];
        }
        return $out;
    }
}
