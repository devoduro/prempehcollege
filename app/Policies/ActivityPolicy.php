<?php

namespace App\Policies;

/** The audit log is read-only and visible to super admins only (via Gate::before). */
class ActivityPolicy extends RolePolicy
{
    protected array $roles = [];

    protected bool $canCreate = false;

    protected bool $canDelete = false;
}
