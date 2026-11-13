<?php

namespace App\Models;

use App\Models\Concerns\HasWebpConversions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class StaffMember extends Model implements HasMedia
{
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }
    use InteractsWithMedia;

    protected $fillable = ['staff_group_id', 'name', 'role', 'bio', 'sort_order', 'is_published', 'legacy_id'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(StaffGroup::class, 'staff_group_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }
}
