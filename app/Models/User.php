<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_ACCOUNTANT = 'accountant';

    public const ROLES = [
        self::ROLE_SUPERADMIN,
        self::ROLE_ADMIN,
        self::ROLE_ACCOUNTANT,
    ];

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    /**
     * Peut créer / modifier / supprimer des opérations.
     */
    public function canManageOperations(): bool
    {
        return $this->hasRole(self::ROLE_SUPERADMIN, self::ROLE_ADMIN);
    }

    /**
     * Rôles que cet utilisateur a le droit d'attribuer (et donc de gérer).
     * superadmin -> admin, comptable ; admin -> comptable ; comptable -> rien.
     */
    public function assignableRoles(): array
    {
        return match ($this->role) {
            self::ROLE_SUPERADMIN => [self::ROLE_ADMIN, self::ROLE_ACCOUNTANT],
            self::ROLE_ADMIN => [self::ROLE_ACCOUNTANT],
            default => [],
        };
    }

    public function canManageUser(User $target): bool
    {
        return $target->id !== $this->id
            && in_array($target->role, $this->assignableRoles(), true);
    }
}
