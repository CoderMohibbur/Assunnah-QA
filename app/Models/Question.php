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

        // existing
        'title',
        'body_html',

        // ✅ publish-order serial
        'published_serial',

        // ✅ i18n future-ready
        'original_lang',
        'title_bn', 'title_en', 'title_ar',
        'body_html_bn', 'body_html_en', 'body_html_ar',

        // asker info
        'asker_name',
        'asker_phone',
        'asker_email',

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
        'is_featured'         => 'boolean',
        'published_at'        => 'datetime',
        'published_serial'    => 'integer',
        'view_count'          => 'integer',
        'answered_notified_at'=> 'datetime',
        'notify_attempts'     => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function answer()
    {
        return $this->hasOne(Answer::class);
    }
}
