<?php

namespace App\Policies;

class StaffGroupPolicy extends RolePolicy
{
    protected array $roles = ['editor'];
}
