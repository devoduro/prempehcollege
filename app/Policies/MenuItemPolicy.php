<?php

namespace App\Policies;

class MenuItemPolicy extends RolePolicy
{
    protected array $roles = ['editor'];
}
