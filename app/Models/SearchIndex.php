<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchIndex extends Model
{
    protected $fillable = [
        'searchable_type',
        'searchable_id',
        'lang',
        'field',
        'text',
        'text_normalized',
        'text_phonetic',
    ];

    /**
     * সাধারণত এখানে timestamp দরকার, তাই ডিফল্ট true থাকতেই দিন।
     * দরকার হলে false করে দিতে পারেন।
     */
    public $timestamps = true;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * যেকোনো মডেল: Question, Answer, Category, Page ইত্যাদি
     */
    public function searchable()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * নির্দিষ্ট মডেল + ID অনুযায়ী স্কোপ।
     */
    public function scopeFor(Model|string $query, Model|string $model, ?int $id = null)
    {
        if ($model instanceof Model) {
            $id    = $model->getKey();
            $class = $model::class;
        } else {
            $class = $model;
        }

        return $this->where('searchable_type', $class)
            ->when($id, fn ($q) => $q->where('searchable_id', $id));
    }

    /**
     * নির্দিষ্ট ভাষার জন্য স্কোপ।
     */
    public function scopeLang($query, string $lang)
    {
        return $query->where('lang', $lang);
    }

    /**
     * নির্দিষ্ট ফিল্ডের জন্য স্কোপ (যেমন: question/body/category ইত্যাদি)।
     */
    public function scopeField($query, string $field)
    {
        return $query->where('field', $field);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * একটি helper যা থেকে ফুল টেক্সট সার্চ করার জন্য basic query বানাতে পারবেন।
     *
     * usage উদাহরণ:
     * SearchIndex::searchText('roja', 'bn-latin')->get();
     */
    public function scopeSearchText($query, string $term, ?string $lang = null)
    {
        // খুব simple normalize; advanced normalize/search logic
        // আপনি পরে সার্ভিস লেভেলে করবেন।
        $term = trim(mb_strtolower($term));

        if ($lang) {
            $query->where('lang', $lang);
        }

        return $query
            ->where(function ($q) use ($term) {
                $q->where('text_normalized', 'LIKE', '%' . $term . '%')
                  ->orWhere('text', 'LIKE', '%' . $term . '%')
                  ->orWhere('text_phonetic', 'LIKE', '%' . $term . '%');
            });
    }
}
