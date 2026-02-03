<?php

namespace App\Models;

use App\Models\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name_bn',
        'name_en',
        'name_ar',
        'slug',
        'description',
        'description_bn',
        'description_en',
        'description_ar',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Locale অনুযায়ী ক্যাটাগরির নাম রিটার্ন করবে।
     * fallback: bn -> en -> ar
     */
    public function nameForLocale(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'en' => $this->name_en ?: $this->name_bn ?: $this->name_ar ?: '',
            'ar' => $this->name_ar ?: $this->name_bn ?: $this->name_en ?: '',
            default => $this->name_bn ?: $this->name_en ?: $this->name_ar ?: '',
        };
    }

    /**
     * Locale অনুযায়ী description রিটার্ন করবে।
     * fallback: description_bn -> description (legacy) -> en/ar
     */
    public function descriptionForLocale(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        return match ($locale) {
            'en' => $this->description_en
                ?: $this->description_bn
                ?: $this->description,
            'ar' => $this->description_ar
                ?: $this->description_bn
                ?: $this->description,
            default => $this->description_bn
                ?: $this->description
                ?: $this->description_en
                ?: $this->description_ar,
        };
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
