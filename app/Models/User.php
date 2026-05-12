<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'branch_id', 'role_id', 'phone', 'address', 'is_active',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function permissionNames(): Collection
    {
        if (! $this->relationLoaded('role')) {
            $this->load('role.permissions:id,name');
        } elseif ($this->role && ! $this->role->relationLoaded('permissions')) {
            $this->role->load('permissions:id,name');
        }

        return collect($this->role?->permissions ?? [])->pluck('name')->filter()->values();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role?->name === 'admin') {
            return true;
        }

        return $this->permissionNames()->contains($permission);
    }

    public function hasAnyPermission(iterable $permissions): bool
    {
        if ($this->role?->name === 'admin') {
            return true;
        }

        $permissionNames = $this->permissionNames();

        foreach ($permissions as $permission) {
            if ($permissionNames->contains($permission)) {
                return true;
            }
        }

        return false;
    }
}
