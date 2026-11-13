<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffGroup extends Model
{
    protected $fillable = ['name', 'slug', 'kind', 'page_id', 'sort_order', 'legacy_id'];

    /** @return HasMany<StaffMember, $this> */
    public function members(): HasMany
    {
        return $this->hasMany(StaffMember::class)->orderBy('sort_order');
    }

    /** The page that displays this group's people as profile cards. @return BelongsTo<Page, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
