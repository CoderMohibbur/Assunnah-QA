<?php

namespace App\Listeners;

use App\Events\AnswerPublished;
use App\Mail\AnswerPublishedMail;
use App\Models\MessageLog;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;

class SendAnswerPublishedNotification
{
    public function __construct(protected SmsService $sms) {}

    public function handle(AnswerPublished $event): void
    {
        // ✅ Load question safely
        $answer = $event->answer->loadMissing(['question']);
        $q = $answer->question;

        if (!$q) return;

        // ✅ idempotent guard (একবার notify হয়ে গেলে আর না)
        if (!empty($q->answered_notified_at)) {
            return;
        }

        // ✅ attempts increment (track every job run)
        $q->forceFill([
            'notify_attempts' => (int)($q->notify_attempts ?? 0) + 1,
            'notify_last_error' => null,
        ])->save();

        $sentAny = false;
        $lastError = null;

        // -----------------------
        // 1) SMS
        // -----------------------
        if (!empty($q->asker_phone)) {
            try {
                $msg = $this->buildSmsMessage($q, $answer);
                $this->sms->send($q->asker_phone, $msg);

                MessageLog::create([
                    'question_id'   => $q->id,
                    'channel'       => 'sms',
                    'to'            => $q->asker_phone,
                    'template_key'  => 'answer_published',
                    'payload'       => ['question_id' => $q->id, 'answer_id' => $answer->id],
                    'status'        => 'sent',
                    'sent_at'       => now(),
                ]);

                $sentAny = true;
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();

                MessageLog::create([
                    'question_id'   => $q->id,
                    'channel'       => 'sms',
                    'to'            => (string)$q->asker_phone,
                    'template_key'  => 'answer_published',
                    'payload'       => ['question_id' => $q->id, 'answer_id' => $answer->id],
                    'status'        => 'failed',
                    'error'         => $lastError,
                ]);
            }
        }

        // -----------------------
        // 2) EMAIL
        // -----------------------
        if (!empty($q->asker_email)) {
            try {
                Mail::to($q->asker_email)->send(new AnswerPublishedMail($q, $answer));

                MessageLog::create([
                    'question_id'   => $q->id,
                    'channel'       => 'email',
                    'to'            => $q->asker_email,
                    'template_key'  => 'answer_published',
                    'payload'       => ['question_id' => $q->id, 'answer_id' => $answer->id],
                    'status'        => 'sent',
                    'sent_at'       => now(),
                ]);

                $sentAny = true;
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();

                MessageLog::create([
                    'question_id'   => $q->id,
                    'channel'       => 'email',
                    'to'            => (string)$q->asker_email,
                    'template_key'  => 'answer_published',
                    'payload'       => ['question_id' => $q->id, 'answer_id' => $answer->id],
                    'status'        => 'failed',
                    'error'         => $lastError,
                ]);
            }
        }

        // -----------------------
        // 3) Final status update on question
        // -----------------------
        if ($sentAny) {
            $q->forceFill([
                'answered_notified_at' => now(),
                'notify_last_error'    => null,
            ])->save();
        } else {
            $q->forceFill([
                'notify_last_error' => $lastError ?: 'No channel available (phone/email empty) or sending failed.',
            ])->save();
        }
    }

    private function buildSmsMessage($q, $answer): string
    {
        // আপনার slug format যেটাই হোক - এখানে safe link build করা হলো
        $slug = $q->slug ?: ('q-' . $q->id);
        $url  = url('/questions/' . $slug);

        return "আপনার প্রশ্নের উত্তর প্রকাশ হয়েছে। দেখুন: {$url}";
    }
}
