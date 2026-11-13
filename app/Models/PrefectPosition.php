<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrefectPosition extends Model
{
    protected $fillable = ['title', 'description', 'rank', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('rank')->orderBy('title');
    }

    /** @return HasMany<Prefect, $this> */
    public function prefects(): HasMany
    {
        return $this->hasMany(Prefect::class);
    }
}
