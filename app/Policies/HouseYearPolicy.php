<?php

namespace App\Policies;

class HouseYearPolicy extends RolePolicy
{
    protected array $roles = ['editor'];
}
