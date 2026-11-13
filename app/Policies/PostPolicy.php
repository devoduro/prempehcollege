<?php

namespace App\Policies;

class PostPolicy extends RolePolicy
{
    protected array $roles = ['editor', 'news-writer'];
}
