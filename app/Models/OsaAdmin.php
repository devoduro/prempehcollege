<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** Old Students Association portal admin. Kept apart from the school website admins (guard "osa"). */
class OsaAdmin extends Authenticatable implements FilamentUser
{
    protected $fillable = ['name', 'email', 'password', 'is_active'];

    protected $attributes = ['is_active' => true];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'password' => 'hashed'];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'osa' && $this->is_active;
    }
}
