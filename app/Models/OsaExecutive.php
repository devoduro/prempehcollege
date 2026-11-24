<?php

namespace App\Models;

use App\Models\Concerns\HasWebpConversions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class OsaExecutive extends Model implements HasMedia
{
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }
    use InteractsWithMedia;

    protected $fillable = ['name', 'position', 'year_completed', 'house_id', 'bio', 'chapter', 'sort_order', 'is_active'];

    protected $attributes = ['is_active' => true, 'sort_order' => 0];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'year_completed' => 'integer'];
    }

    /** @return BelongsTo<House, $this> */
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }
}
