<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Check if User is Admin
    public function isAdmin(): bool
    {
        // 1) Spatie Permission থাকলে
        if (method_exists($this, 'hasAnyRole')) {
            if ($this->hasAnyRole(['Admin', 'Super Admin', 'Owner', 'admin', 'super_admin', 'owner'])) return true;
        }
        if (method_exists($this, 'hasRole')) {
            if ($this->hasRole('admin') || $this->hasRole('super_admin') || $this->hasRole('owner')) return true;
            if ($this->hasRole('Admin') || $this->hasRole('Super Admin') || $this->hasRole('Owner')) return true;
        }

        // 2) boolean flags (যদি থাকে)
        foreach (['is_admin', 'is_super_admin', 'admin'] as $col) {
            if (array_key_exists($col, $this->attributes) && (bool) $this->attributes[$col]) return true;
        }

        // 3) role string column (যদি users table এ role/type থাকে)
        $roleStr = strtolower((string)($this->attributes['role'] ?? $this->attributes['type'] ?? $this->attributes['user_role'] ?? ''));
        if (in_array($roleStr, ['admin', 'super_admin', 'owner'], true)) return true;

        // 4) role_id based (roles table থাকলে slug/name পড়ে চেক)
        $roleId = (int) ($this->attributes['role_id'] ?? 0);
        if ($roleId > 0) {
            $ids = (array) config('qa.admin_role_ids', [1]);
            if (in_array($roleId, $ids, true)) return true;

            if (Schema::hasTable('roles')) {
                $row = DB::table('roles')->where('id', $roleId)->first(['slug', 'name']);
                $slug = strtolower((string)($row->slug ?? ''));
                $name = strtolower((string)($row->name ?? ''));

                $slugs = (array) config('qa.admin_role_slugs', ['admin', 'super_admin', 'owner']);
                if ($slug && in_array($slug, $slugs, true)) return true;

                if ($name && in_array($name, ['admin', 'super admin', 'owner'], true)) return true;
            }
        }

        // 5) fallback: admin emails
        $emails = array_map('strtolower', (array) config('qa.admin_emails', []));
        if (!empty($this->email) && in_array(strtolower($this->email), $emails, true)) return true;

        return false;
    }
}
