<?php

namespace App\Policies;

class PrefectBoardPolicy extends RolePolicy
{
    protected array $roles = ['editor'];
}
