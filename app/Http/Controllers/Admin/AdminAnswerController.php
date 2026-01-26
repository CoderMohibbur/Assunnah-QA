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

            // 1) Upsert answer as published
            $answer = Answer::updateOrCreate(
                ['question_id' => $question->id],
                [
                    'answered_by' => auth()->id(),
                    'answer_html' => $cleanAnswer,
                    'status'      => 'published',
                    'answered_at'  => now(),
                ]
            );

            $answerId = $answer->id;

            // 2) Publish question
            $question->forceFill([
                'status'       => 'published',
                'published_at' => now(),
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
