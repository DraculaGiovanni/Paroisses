<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Permission;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'paroisse_id',
    'role',
    'actif',
    'must_change_password',
    'permissions',
    'last_login_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'actif' => 'boolean',
            'must_change_password' => 'boolean',
            'permissions' => 'array',
            'last_login_at' => 'datetime',
        ];
    }

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasPermission(Permission|string $permission): bool
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        if ($this->role !== 'secretariat') {
            return false;
        }

        $value = $permission instanceof Permission ? $permission->value : $permission;

        return in_array($value, $this->permissions ?? [], true);
    }

    /**
     * @return list<string>
     */
    public function effectivePermissions(): array
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return Permission::values();
        }

        return $this->role === 'secretariat' ? ($this->permissions ?? []) : [];
    }
}
