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

        'answer_html',      // legacy / fallback
        'answer_html_bn',
        'answer_html_en',
        'answer_html_ar',

        'status',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    /**
     * Locale অনুযায়ী উত্তর (HTML) রিটার্ন করবে।
     *
     * Fallback order:
     *  - bn: bn -> legacy answer_html -> en -> ar
     *  - en: en -> bn -> legacy -> ar
     *  - ar: ar -> bn -> legacy -> en
     */
    public function bodyHtmlForLocale(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'en' => $this->answer_html_en
                ?: $this->answer_html_bn
                ?: $this->answer_html
                ?: $this->answer_html_ar,

            'ar' => $this->answer_html_ar
                ?: $this->answer_html_bn
                ?: $this->answer_html
                ?: $this->answer_html_en,

            default => $this->answer_html_bn
                ?: $this->answer_html
                ?: $this->answer_html_en
                ?: $this->answer_html_ar,
        };
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answeredBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'answered_by');
    }
}
