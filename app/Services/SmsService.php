<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $to, string $message): void
    {
        Log::info('[SMS]', ['to' => $to, 'message' => $message]);
    }
}
