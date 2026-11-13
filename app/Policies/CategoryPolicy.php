<?php

namespace App\Policies;

class CategoryPolicy extends RolePolicy
{
    protected array $roles = ['editor', 'news-writer'];
}
