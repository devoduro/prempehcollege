<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffGroup extends Model
{
    protected $fillable = ['name', 'slug', 'kind', 'sort_order', 'legacy_id'];

    public function members(): HasMany
    {
        return $this->hasMany(StaffMember::class)->orderBy('sort_order');
    }
}
