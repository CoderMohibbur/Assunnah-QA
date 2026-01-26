<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class AdminQuestionController extends Controller
{
    public function index(Request $request)
    {
        // UI param: ?status=pending|published|rejected
        $status = $request->string('status')->toString() ?: 'pending';
        $q      = trim((string) $request->get('q', ''));
        $cat    = $request->get('category_id');

        $allowedStatuses = ['pending', 'published', 'rejected'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'pending';
        }

        $questionsQuery = Question::query()
            ->with(['category', 'answer', 'answer.answeredBy'])
            ->whereNull('deleted_at') // ✅ hide trashed
            ->where('status', $status)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('title', 'like', "%{$q}%")
                        ->orWhere('asker_name', 'like', "%{$q}%")
                        ->orWhere('asker_phone', 'like', "%{$q}%")
                        ->orWhere('asker_email', 'like', "%{$q}%");
                });
            })
            ->when(!empty($cat), fn ($query) => $query->where('category_id', $cat))
            ->orderByDesc('id');

        $questions = $questionsQuery
            ->paginate(20)
            ->withQueryString();

        // ✅ quick counts for tabs (same "not trashed" rule)
        $baseCountQuery = Question::query()->whereNull('deleted_at');

        $counts = [
            'pending'   => (clone $baseCountQuery)->where('status', 'pending')->count(),
            'published' => (clone $baseCountQuery)->where('status', 'published')->count(),
            'rejected'  => (clone $baseCountQuery)->where('status', 'rejected')->count(),
        ];

        return view('admin.questions.index', compact('questions', 'status', 'q', 'cat', 'counts'));
    }

    public function show(Question $question)
    {
        // ✅ safety: prevent viewing trashed in admin list flow
        if ($question->deleted_at) {
            abort(404);
        }

        $question->load(['category', 'answer', 'answer.answeredBy']);

        return view('admin.questions.show', compact('question'));
    }

    public function approve(Question $question)
    {
        if ($question->deleted_at) {
            abort(404);
        }

        // ✅ approve = bring back from rejected to pending
        if ($question->status === 'rejected') {
            $question->forceFill(['status' => 'pending'])->save();
            return back()->with('success', 'Approved (moved back to pending).');
        }

        // If already pending/published, no change
        return back()->with('success', 'Nothing to approve.');
    }

    public function reject(Request $request, Question $question)
    {
        if ($question->deleted_at) {
            abort(404);
        }

        // ✅ don’t reject already published (safer)
        if ($question->status === 'published') {
            return back()->with('error', 'Published question cannot be rejected.');
        }

        // optional: if you want a reason later
        // $data = $request->validate([
        //     'reason' => ['nullable', 'string', 'max:500'],
        // ]);

        $question->forceFill([
            'status' => 'rejected',
            // 'rejection_reason' => $data['reason'] ?? null, // if column exists
        ])->save();

        return redirect()
            ->route('admin.questions.index', ['status' => 'rejected'])
            ->with('success', 'Rejected.');
    }
}
