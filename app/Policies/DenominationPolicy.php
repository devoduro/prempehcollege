<?php

namespace App\Policies;

class DenominationPolicy extends RolePolicy
{
    protected array $roles = ['editor'];
}
