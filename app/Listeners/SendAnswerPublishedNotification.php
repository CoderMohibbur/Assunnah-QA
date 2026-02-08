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

    private function safePublicSlug($q): string
    {
        $slug = trim((string)($q->slug ?? ''));

        // slug empty => id fallback
        if ($slug === '') {
            return 'q-' . $q->id;
        }

        // slug is numeric like q-123 কিন্তু এটা যদি id না হয়, তাহলে id use করবো
        if (preg_match('/^q-(\d+)$/', $slug, $m)) {
            $n = (int)$m[1];
            if ($n !== (int)$q->id) {
                return 'q-' . $q->id;
            }
        }

        return $slug;
    }

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

        // ✅ NEW: checkbox flags (controller থেকে আসবে)
        $sendSms   = (bool) ($event->notifySms ?? true);
        $sendEmail = (bool) ($event->notifyEmail ?? true);

        // ✅ attempts increment (track every job run)
        $q->forceFill([
            'notify_attempts'   => (int)($q->notify_attempts ?? 0) + 1,
            'notify_last_error' => null,
        ])->save();

        // ✅ If admin disabled both => do nothing (but log "skipped")
        if (!$sendSms && !$sendEmail) {
            MessageLog::create([
                'question_id'  => $q->id,
                'channel'      => 'system',
                'to'           => '-',
                'template_key' => 'answer_published',
                'payload'      => [
                    'question_id'   => $q->id,
                    'answer_id'     => $answer->id,
                    'notify_sms'    => $sendSms,
                    'notify_email'  => $sendEmail,
                ],
                'status'       => 'skipped',
                'error'        => 'Notifications disabled by admin (SMS+Email unchecked).',
            ]);

            // keep answered_notified_at NULL because nothing was sent
            $q->forceFill([
                'notify_last_error' => 'Notification skipped (disabled by admin).',
            ])->save();

            return;
        }

        $sentAny   = false;
        $lastError = null;

        // -----------------------
        // 1) SMS (only if admin enabled)
        // -----------------------
        if ($sendSms && !empty($q->asker_phone)) {
            try {
                $msg = $this->buildSmsMessage($q, $answer);
                $this->sms->send($q->asker_phone, $msg);

                MessageLog::create([
                    'question_id'  => $q->id,
                    'channel'      => 'sms',
                    'to'           => $q->asker_phone,
                    'template_key' => 'answer_published',
                    'payload'      => [
                        'question_id'  => $q->id,
                        'answer_id'    => $answer->id,
                        'notify_sms'   => $sendSms,
                        'notify_email' => $sendEmail,
                    ],
                    'status'  => 'sent',
                    'sent_at' => now(),
                ]);

                $sentAny = true;
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();

                MessageLog::create([
                    'question_id'  => $q->id,
                    'channel'      => 'sms',
                    'to'           => (string)$q->asker_phone,
                    'template_key' => 'answer_published',
                    'payload'      => [
                        'question_id'  => $q->id,
                        'answer_id'    => $answer->id,
                        'notify_sms'   => $sendSms,
                        'notify_email' => $sendEmail,
                    ],
                    'status' => 'failed',
                    'error'  => $lastError,
                ]);
            }
        }

        // -----------------------
        // 2) EMAIL (only if admin enabled)
        // -----------------------
        if ($sendEmail && !empty($q->asker_email)) {
            try {
                Mail::to($q->asker_email)->send(new AnswerPublishedMail($q, $answer));

                MessageLog::create([
                    'question_id'  => $q->id,
                    'channel'      => 'email',
                    'to'           => $q->asker_email,
                    'template_key' => 'answer_published',
                    'payload'      => [
                        'question_id'  => $q->id,
                        'answer_id'    => $answer->id,
                        'notify_sms'   => $sendSms,
                        'notify_email' => $sendEmail,
                    ],
                    'status'  => 'sent',
                    'sent_at' => now(),
                ]);

                $sentAny = true;
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();

                MessageLog::create([
                    'question_id'  => $q->id,
                    'channel'      => 'email',
                    'to'           => (string)$q->asker_email,
                    'template_key' => 'answer_published',
                    'payload'      => [
                        'question_id'  => $q->id,
                        'answer_id'    => $answer->id,
                        'notify_sms'   => $sendSms,
                        'notify_email' => $sendEmail,
                    ],
                    'status' => 'failed',
                    'error'  => $lastError,
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
            // Example: admin enabled SMS but phone empty, or enabled Email but email empty, or both failed
            $q->forceFill([
                'notify_last_error' => $lastError ?: 'No channel available (phone/email empty) or sending failed.',
            ])->save();
        }
    }

    private function buildSmsMessage($q, $answer): string
    {
        $slug = $this->safePublicSlug($q);
        $url  = route('questions.show', ['slug' => $slug]);

        return "Assalamu Alaikum, your question to As-Sunnah Trust has been answered. View it here: {$url} JazakAllahu Khair.";
    }
}
