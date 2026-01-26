<?php

namespace App\Events;

use App\Models\Answer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnswerPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public Answer $answer) {}
}
