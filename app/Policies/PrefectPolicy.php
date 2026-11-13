<?php

namespace App\Policies;

class PrefectPolicy extends RolePolicy
{
    protected array $roles = ['editor'];
}
