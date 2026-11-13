<?php

namespace App\Models;

use App\Models\Concerns\HasWebpConversions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * One school year's prefectorial board.
 *
 * @property bool $is_current
 * @property-read string $path
 */
class PrefectBoard extends Model implements HasMedia
{
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }
    use InteractsWithMedia, LogsActivity;

    protected $fillable = ['name', 'slug', 'academic_year', 'description', 'is_current', 'is_published'];

    protected function casts(): array
    {
        return ['is_current' => 'boolean', 'is_published' => 'boolean'];
    }

    protected function path(): Attribute
    {
        return Attribute::get(fn () => '/prefects/'.$this->slug);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    /** @return HasMany<Prefect, $this> */
    public function prefects(): HasMany
    {
        return $this->hasMany(Prefect::class)->orderBy('sort_order');
    }

    protected static function booted(): void
    {
        // Only one board is "current" at a time.
        static::saved(function (self $board) {
            if ($board->is_current) {
                static::whereKeyNot($board->getKey())->where('is_current', true)->update(['is_current' => false]);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'academic_year', 'is_current', 'is_published'])->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('group_photo')->singleFile();
        $this->addMediaCollection('gallery');
    }
}
