<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;

    protected $fillable = ['key','value','group'];

    public static function get(string $key, $default = null)
    {
        $row = static::query()
            ->whereNull('deleted_at')
            ->where('key', $key)
            ->first();

        return $row?->value ?? $default;
    }

    public static function put(string $key, $value = null, ?string $group = null): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }
}
