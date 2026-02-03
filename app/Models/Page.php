<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',

        // i18n
        'original_lang',

        // legacy / fallback
        'title',
        'title_bn',
        'title_en',
        'title_ar',

        'content_html',
        'content_html_bn',
        'content_html_en',
        'content_html_ar',

        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Locale helpers
    |--------------------------------------------------------------------------
    */

    /**
     * বর্তমান locale অনুযায়ী পেজের title রিটার্ন করবে।
     *
     * Fallback order:
     *  - bn: title_bn -> title -> title_en -> title_ar
     *  - en: title_en -> title_bn -> title -> title_ar
     *  - ar: title_ar -> title_bn -> title -> title_en
     */
    public function titleForLocale(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'en' => $this->title_en
                ?: $this->title_bn
                ?: $this->title
                ?: $this->title_ar,
            'ar' => $this->title_ar
                ?: $this->title_bn
                ?: $this->title
                ?: $this->title_en,
            default => $this->title_bn
                ?: $this->title
                ?: $this->title_en
                ?: $this->title_ar,
        };
    }

    /**
     * বর্তমান locale অনুযায়ী পেজের content_html রিটার্ন করবে।
     *
     * Fallback order:
     *  - bn: content_html_bn -> content_html -> content_html_en -> content_html_ar
     *  - en: content_html_en -> content_html_bn -> content_html -> content_html_ar
     *  - ar: content_html_ar -> content_html_bn -> content_html -> content_html_en
     */
    public function contentHtmlForLocale(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'en' => $this->content_html_en
                ?: $this->content_html_bn
                ?: $this->content_html
                ?: $this->content_html_ar,
            'ar' => $this->content_html_ar
                ?: $this->content_html_bn
                ?: $this->content_html
                ?: $this->content_html_en,
            default => $this->content_html_bn
                ?: $this->content_html
                ?: $this->content_html_en
                ?: $this->content_html_ar,
        };
    }

    /**
     * Plain text কনটেন্ট (search/snippet এর জন্য)
     */
    public function contentTextForLocale(?string $locale = null): string
    {
        $html = $this->contentHtmlForLocale($locale);

        return $html ? trim(strip_tags($html)) : '';
    }
}
