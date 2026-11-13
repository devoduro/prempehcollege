<?php

namespace App\Policies;

class StaffMemberPolicy extends RolePolicy
{
    protected array $roles = ['editor'];
}
