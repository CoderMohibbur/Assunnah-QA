<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question_id',
        'answered_by',
        'answer_html',
        'status',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];
    protected $guarded = [];


    public function question()
    {
        return $this->belongsTo(Question::class);
    }


    protected static function booted()
    {
        static::created(function ($answer) {
            if ($answer->status === 'published') {
                event(new \App\Events\AnswerPublished($answer));
            }
        });

        static::updated(function ($answer) {
            if ($answer->wasChanged('status') && $answer->status === 'published') {
                event(new \App\Events\AnswerPublished($answer));
            }
        });
    }


    public function answeredBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'answered_by');
    }
}
