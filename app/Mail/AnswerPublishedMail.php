<?php

namespace App\Mail;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AnswerPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Question $question, public Answer $answer) {}

    public function build()
    {
        return $this->subject('আপনার প্রশ্নের উত্তর প্রকাশ হয়েছে')
            ->view('emails.answer_published')
            ->with([
                'question' => $this->question,
                'answer'   => $this->answer,
            ]);
    }
}
