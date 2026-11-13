<?php

namespace App\Policies;

class ContactMessagePolicy extends RolePolicy
{
    protected array $roles = ['editor'];

    /** Messages only arrive from the public form. */
    protected bool $canCreate = false;
}
