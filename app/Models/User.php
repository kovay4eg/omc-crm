<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * Масове заповнення
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Приховані поля
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Касти
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * РОЛІ
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    public function isContent(): bool
    {
        return $this->role === 'content';
    }

    /**
     * 🔥 АКТИВНА РОЛЬ (з урахуванням preview)
     */
    public function getActiveRole(): string
    {
        // якщо адмін і включений режим перегляду
        if ($this->isAdmin() && session()->has('preview_role')) {
            return session('preview_role');
        }

        return $this->role ?? 'user';
    }

    /**
     * ДОСТУП ДО FILAMENT
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'editor']);
    }
}