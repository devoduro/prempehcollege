<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Role-based policy: a user may manage a model when they hold one of the listed roles.
 * Super admins pass every check through the Gate::before hook in AppServiceProvider.
 */
abstract class RolePolicy
{
    /** @var list<string> Roles allowed to view and edit. */
    protected array $roles = [];

    /** @var bool Whether records may be created from the admin. */
    protected bool $canCreate = true;

    /** @var bool Whether records may be deleted from the admin. */
    protected bool $canDelete = true;

    protected function allowed(User $user): bool
    {
        return $user->is_active && $user->hasAnyRole($this->roles);
    }

    public function viewAny(User $user): bool
    {
        return $this->allowed($user);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->allowed($user);
    }

    public function create(User $user): bool
    {
        return $this->canCreate && $this->allowed($user);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->allowed($user);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->canDelete && $this->allowed($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canDelete && $this->allowed($user);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->allowed($user);
    }

    public function restoreAny(User $user): bool
    {
        return $this->allowed($user);
    }

    /** Permanent deletion is reserved for super admins (Gate::before). */
    public function forceDelete(User $user, Model $model): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
