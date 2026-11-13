<?php

namespace App\Policies;

class HeroSlidePolicy extends RolePolicy
{
    protected array $roles = ['editor'];
}
