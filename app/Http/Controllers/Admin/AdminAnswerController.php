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
        ]);

        $cleanAnswer = $this->sanitizeHtml((string) $data['answer_html']);
        $qPayload    = $this->questionPayloadFromValidated($data);

        $answerId = null;

        DB::transaction(function () use ($question, $cleanAnswer, $qPayload, &$answerId) {

            // ✅ Lock question row (race-condition safe)
            $q = Question::whereKey($question->id)->lockForUpdate()->firstOrFail();

            // ✅ Update question edits FIRST (title/body/category) if provided
            if (!empty($qPayload)) {
                $q->forceFill($qPayload)->save();
            }

            // ✅ Assign publish serial only once (publish order)
            if (empty($q->published_serial)) {
                $nextSerial = (Question::whereNotNull('published_serial')
                    ->lockForUpdate()
                    ->max('published_serial') ?? 0) + 1;

                $q->published_serial = $nextSerial;
            }

            // ✅ Upsert answer as published
            $answer = Answer::updateOrCreate(
                ['question_id' => $q->id],
                [
                    'answered_by' => auth()->id(),
                    'answer_html' => $cleanAnswer,

                    'answer_html_bn' => $cleanAnswer,
                    'answer_html_en' => null,
                    'answer_html_ar' => null,

                    'status'      => 'published',
                    'answered_at' => now(),
                ]
            );

            $answerId = $answer->id;

            // ✅ Publish question (keep title/body/category edits already saved)
            $q->forceFill([
                'status'           => 'published',
                'published_at'     => now(),
                'published_serial' => $q->published_serial,
            ])->save();
        });

        // ✅ Fire event after DB commit (stable)
        DB::afterCommit(function () use ($answerId) {
            $answer = Answer::with(['question'])->find($answerId);
            if ($answer) {
                event(new AnswerPublished($answer));
            }
        });

        return redirect()
            ->route('admin.questions.index', ['status' => 'published'])
            ->with('success', 'Answer published ✅ Notification queued.');
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
