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
 * @property string|null $history
 * @property string|null $about
 * @property string|null $colour
 * @property-read string $path
 */
class House extends Model implements HasMedia
{
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }
    use InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = ['name', 'slug', 'colour', 'motto', 'summary', 'about', 'history', 'namesake', 'housemaster', 'founded_year', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'founded_year' => 'integer'];
    }

    /** History is rich text from the admin; purified on write like every other body. */
    protected function history(): Attribute
    {
        return Attribute::make(set: fn (?string $v) => $v === null ? null : Purifier::clean($v, 'default'));
    }

    protected function about(): Attribute
    {
        return Attribute::make(set: fn (?string $v) => $v === null ? null : Purifier::clean($v, 'default'));
    }

    /** One record per school year, newest first. @return HasMany<HouseYear, $this> */
    public function years(): HasMany
    {
        return $this->hasMany(HouseYear::class)->orderByDesc('academic_year');
    }

    /** The most recent year on record, which tells who currently leads the house. */
    public function currentYear(): ?HouseYear
    {
        /** @var HouseYear|null $latest */
        $latest = $this->years->first();

        return $latest;
    }

    protected function path(): Attribute
    {
        return Attribute::get(fn () => '/houses/'.$this->slug);
    }

    /** A safe CSS colour: only #rrggbb values are ever output into styles. */
    public function cssColour(): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', (string) $this->colour) ? (string) $this->colour : '#115e03';
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /** @return HasMany<Prefect, $this> */
    public function prefects(): HasMany
    {
        return $this->hasMany(Prefect::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'slug', 'is_active'])->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('crest')->singleFile();
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }
}
