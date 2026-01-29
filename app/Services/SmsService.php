<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $to, string $message): void
    {
        // Normalize number: digits only
        $to = preg_replace('/\D+/', '', $to);

        // BD normalize
        if (str_starts_with($to, '0'))  $to = '88' . $to;     // 017.. -> 88017..
        if (str_starts_with($to, '1'))  $to = '880' . $to;    // 17.. -> 88017..
        if (str_starts_with($to, '+'))  $to = ltrim($to, '+'); // +880.. -> 880..

        // Off switch => log only
        if (!config('services.rapid_sms.enabled')) {
            Log::info('[SMS-LOG]', ['to' => $to, 'message' => $message]);
            return;
        }

        $base = rtrim((string) config('services.rapid_sms.base_url'), '/');
        $user = (string) config('services.rapid_sms.user_id');
        $pass = (string) config('services.rapid_sms.password');

        try {
            $res = Http::timeout(20)->get($base . '/request.php', [
                'user_id'  => $user,
                'password' => $pass,
                'number'   => $to,       // 88017XXXXXXXX
                'message'  => $message,  // Laravel নিজে URL encode করে পাঠায়
            ]);

            $data = $res->json();

            if (!$res->successful() || !is_array($data) || ($data['status'] ?? '') !== 'success') {
                Log::warning('[SMS-FAILED]', [
                    'http' => $res->status(),
                    'to'   => $to,
                    'body' => $res->body(),
                ]);
                return;
            }

            Log::info('[SMS-SENT]', [
                'to'     => $to,
                'sms_id' => $data['sms_id'] ?? null,
                'msg'    => $data['message'] ?? null,
            ]);

        } catch (\Throwable $e) {
            Log::error('[SMS-ERROR]', ['error' => $e->getMessage(), 'to' => $to]);
        }
    }

    // (Optional) Delivery report check
    public function dlr(string $smsId): ?array
    {
        $base = rtrim((string) config('services.rapid_sms.base_url'), '/');
        $user = (string) config('services.rapid_sms.user_id');
        $pass = (string) config('services.rapid_sms.password');

        try {
            $res = Http::timeout(20)->get($base . '/dlr.php', [
                'user_id'  => $user,
                'password' => $pass,
                'sms_id'   => $smsId,
            ]);

            return $res->json();
        } catch (\Throwable $e) {
            Log::error('[SMS-DLR-ERROR]', ['error' => $e->getMessage(), 'sms_id' => $smsId]);
            return null;
        }
    }
}
