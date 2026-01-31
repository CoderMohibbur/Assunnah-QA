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
        'answer_html_bn',
        'answer_html_en',
        'answer_html_ar',

        'status',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    // ✅ NOTE: guarded remove করুন, fillable থাকলেই যথেষ্ট
    // protected $guarded = [];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answeredBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'answered_by');
    }
}
