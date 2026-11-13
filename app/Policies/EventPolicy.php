<?php

namespace App\Policies;

class EventPolicy extends RolePolicy
{
    protected array $roles = ['editor', 'news-writer'];
}
