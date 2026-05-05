<?php

namespace App\Models;

use App\Enums\UserRoles;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'email',
        'phone',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'role',
        'remember_token',
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
            'role' => UserRoles::class,
        ];
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(
            Bonus::class
        );
    }

    public function orders (): HasMany
    {
        return $this->hasMany(
            Order::class,
        );
    }

    public function profile (): HasOne
    {
        return $this->hasOne(
            Profile::class,
        );
    }

    public function canAccessNova(): bool
    {
        return in_array($this->role, [
            UserRoles::Admin,
            UserRoles::Developer,
            UserRoles::ContentManager,
            UserRoles::ShopManager,
        ]);
    }

    public function canAccessNovaShopSettings(): bool
    {
        return in_array($this->role, [
            UserRoles::Admin,
            UserRoles::Developer,
            UserRoles::ShopManager,
        ]);
    }

    public function canAccessNovaPageSettings(): bool
    {
        return in_array($this->role, [
            UserRoles::Admin,
            UserRoles::Developer,
            UserRoles::ContentManager,
        ]);
    }

    public function canAccessNovaGeneralSettings(): bool
    {
        return in_array($this->role, [
            UserRoles::Admin,
            UserRoles::Developer,
        ]);
    }
}
