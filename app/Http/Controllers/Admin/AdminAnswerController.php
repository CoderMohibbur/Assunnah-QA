<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\AnswerPublished;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;

class AdminAnswerController extends Controller
{
    /**
     * Save Answer Draft
     * Route: POST /admin/questions/{question}/answer/draft
     */
    public function saveDraft(Question $question, Request $request)
    {
        // ✅ Guard: trashed hide
        if ($question->deleted_at) {
            abort(404);
        }

        // ✅ Draft allow for pending/rejected (rejected হলে pending এ ফেরত আনতে পারেন)
        $request->validate([
            'answer_html' => ['required', 'string', 'min:10'],
        ]);

        $rawAnswer   = (string) $request->input('answer_html');
        $cleanAnswer = $this->sanitizeHtml($rawAnswer);

        DB::transaction(function () use ($question, $cleanAnswer) {
            // 1) Upsert answer (1 question = 1 answer)
            Answer::updateOrCreate(
                ['question_id' => $question->id],
                [
                    'answered_by' => auth()->id(),
                    'answer_html' => $cleanAnswer,
                    'status'      => 'draft',
                    'answered_at'  => null,
                ]
            );

            // 2) If question was rejected, bring it back to pending for review
            if (($question->status ?? '') === 'rejected') {
                $question->forceFill([
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
    // ✅ Guard: trashed hide
    if ($question->deleted_at) {
        abort(404);
    }

    // ✅ Guard: rejected হলে publish allow করবেন কি না?
    if (($question->status ?? '') === 'rejected') {
        return back()->withErrors([
            'answer_html' => 'Rejected প্রশ্ন publish করা যাবে না। আগে approve/restore করুন।'
        ]);
    }

    $request->validate([
        'answer_html' => ['required', 'string', 'min:10'],
    ]);

    // ✅ Sanitize
    $rawAnswer   = (string) $request->input('answer_html');
    $cleanAnswer = $this->sanitizeHtml($rawAnswer);

    $answerId = null;

    DB::transaction(function () use ($question, $cleanAnswer, &$answerId) {

        // ✅ Lock question row (race-condition safe)
        $question = Question::whereKey($question->id)->lockForUpdate()->firstOrFail();

        // ✅ Assign publish serial only once (publish order)
        if (empty($question->published_serial)) {
            $nextSerial = (Question::whereNotNull('published_serial')
                ->lockForUpdate()
                ->max('published_serial') ?? 0) + 1;

            $question->published_serial = $nextSerial;
        }

        // 1) Upsert answer as published
        $answer = Answer::updateOrCreate(
            ['question_id' => $question->id],
            [
                'answered_by'    => auth()->id(),
                'answer_html'    => $cleanAnswer,

                // ✅ future i18n: default bn copy
                'answer_html_bn' => $cleanAnswer,
                'answer_html_en' => null,
                'answer_html_ar' => null,

                'status'         => 'published',
                'answered_at'    => now(),
            ]
        );

        $answerId = $answer->id;

        // 2) Publish question (with serial)
        $question->forceFill([
            'status'           => 'published',
            'published_at'     => now(),
            'published_serial' => $question->published_serial,
        ])->save();
    });

    // ✅ Fire event after DB commit (stable) — only once
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
     * Sanitize WYSIWYG HTML
     */
    private function sanitizeHtml(string $html): string
    {
        try {
            if (class_exists(Purifier::class)) {
                return (string) Purifier::clean($html);
            }
        } catch (\Throwable $e) {
            // ignore, fallback below
        }

        // Fallback basic allowlist (Purifier recommended in production)
        return strip_tags(
            $html,
            '<p><br><b><strong><i><em><u><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><a>'
        );
    }
}
