<?php

namespace App\Policies;

class PagePolicy extends RolePolicy
{
    protected array $roles = ['editor'];
}
