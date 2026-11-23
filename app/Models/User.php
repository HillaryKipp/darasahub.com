<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_EDITOR = 'editor';
    public const ROLE_REVIEWER = 'reviewer';

    public const ROLES = [
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_EDITOR => 'Editor',
        self::ROLE_REVIEWER => 'Reviewer',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && in_array($this->role, array_keys(self::ROLES), true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isReviewer(): bool
    {
        return $this->role === self::ROLE_REVIEWER;
    }

    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }

    public function canReviewAndPublish(): bool
    {
        return $this->is_active && in_array($this->role, [self::ROLE_ADMIN, self::ROLE_REVIEWER], true);
    }
}
