<?php

namespace App\Models\Concerns;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;

trait IsPublishable
{
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', ContentStatus::Published->value)
            ->where(fn ($w) => $w->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
