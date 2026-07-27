<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'telegram_id',
        'bale_id',
        'locale',
        'is_active',
        'signup_university_id',
        'signup_platform',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'telegram_id' => 'integer',
            'bale_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function signupUniversity(): BelongsTo
    {
        return $this->belongsTo(University::class, 'signup_university_id');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin', 'owner']);
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }
}
