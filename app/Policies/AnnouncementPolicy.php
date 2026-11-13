<?php

namespace App\Policies;

class AnnouncementPolicy extends RolePolicy
{
    protected array $roles = ['editor', 'news-writer'];
}
