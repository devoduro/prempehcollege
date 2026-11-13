<?php

namespace App\Policies;

class FacilityPolicy extends RolePolicy
{
    protected array $roles = ['editor'];
}
