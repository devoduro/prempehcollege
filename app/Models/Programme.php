<?php

namespace App\Models;

use App\Models\Concerns\HasWebpConversions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mews\Purifier\Facades\Purifier;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string|null $description
 * @property list<string>|null $core_subjects
 * @property-read string $path
 */
class Programme extends Model implements HasMedia
{
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }
    use InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = ['name', 'slug', 'summary', 'description', 'core_subjects', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'core_subjects' => 'array'];
    }

    protected function description(): Attribute
    {
        return Attribute::make(set: fn (?string $v) => $v === null ? null : Purifier::clean($v, 'default'));
    }

    protected function path(): Attribute
    {
        return Attribute::get(fn () => '/programmes/'.$this->slug);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /** @return HasMany<CourseCombination, $this> */
    public function combinations(): HasMany
    {
        return $this->hasMany(CourseCombination::class)->orderBy('sort_order');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'slug', 'is_active'])->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }
}
