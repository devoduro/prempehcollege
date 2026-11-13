<?php

namespace App\Policies;

class ProgrammePolicy extends RolePolicy
{
    protected array $roles = ['editor'];
}
