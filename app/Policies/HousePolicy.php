<?php

namespace App\Policies;

class HousePolicy extends RolePolicy
{
    protected array $roles = ['editor'];
}
