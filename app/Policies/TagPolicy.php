<?php

namespace App\Policies;

class TagPolicy extends RolePolicy
{
    protected array $roles = ['editor', 'news-writer'];
}
