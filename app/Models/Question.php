<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'slug',

        // existing single-language (legacy / fallback)
        'title',
        'body_html',

        // publish-order serial
        'published_serial',

        // i18n fields
        'original_lang',
        'title_bn', 'title_en', 'title_ar',
        'body_html_bn', 'body_html_en', 'body_html_ar',

        // asker info
        'asker_name',
        'asker_phone',
        'asker_email',

        // status & meta
        'status',
        'published_at',
        'view_count',
        'is_featured',
        'title_hash',
        'answered_notified_at',
        'notify_attempts',
        'notify_last_error',
    ];

    protected $casts = [
        'is_featured'          => 'boolean',
        'published_at'         => 'datetime',
        'published_serial'     => 'integer',
        'view_count'           => 'integer',
        'answered_notified_at' => 'datetime',
        'notify_attempts'      => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function answer()
    {
        return $this->hasOne(Answer::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Locale helpers (multi-language support)
    |--------------------------------------------------------------------------
    */

    /**
     * বর্তমান locale অনুযায়ী প্রশ্নের title রিটার্ন করবে।
     * fallback order:
     *   bn: title_bn -> title -> title_en -> title_ar
     *   en: title_en -> title_bn -> title -> title_ar
     *   ar: title_ar -> title_bn -> title -> title_en
     */
    public function titleForLocale(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'en' => $this->title_en
                ?: $this->title_bn
                ?: $this->title
                ?: $this->title_ar
                ?: '',
            'ar' => $this->title_ar
                ?: $this->title_bn
                ?: $this->title
                ?: $this->title_en
                ?: '',
            default => $this->title_bn
                ?: $this->title
                ?: $this->title_en
                ?: $this->title_ar
                ?: '',
        };
    }

    /**
     * বর্তমান locale অনুযায়ী প্রশ্নের body_html রিটার্ন করবে।
     * fallback order:
     *   bn: body_html_bn -> body_html -> body_html_en -> body_html_ar
     *   en: body_html_en -> body_html_bn -> body_html -> body_html_ar
     *   ar: body_html_ar -> body_html_bn -> body_html -> body_html_en
     */
    public function bodyHtmlForLocale(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'en' => $this->body_html_en
                ?: $this->body_html_bn
                ?: $this->body_html
                ?: $this->body_html_ar,
            'ar' => $this->body_html_ar
                ?: $this->body_html_bn
                ?: $this->body_html
                ?: $this->body_html_en,
            default => $this->body_html_bn
                ?: $this->body_html
                ?: $this->body_html_en
                ?: $this->body_html_ar,
        };
    }

    /**
     * body এর plain text (search/snippet এর জন্য দরকার হলে)
     */
    public function bodyTextForLocale(?string $locale = null): string
    {
        $html = $this->bodyHtmlForLocale($locale);

        return $html ? trim(strip_tags($html)) : '';
    }
}
