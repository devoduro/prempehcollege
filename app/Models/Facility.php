<?php

namespace App\Models;

use App\Models\Concerns\HasWebpConversions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mews\Purifier\Facades\Purifier;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string|null $about
 * @property list<string>|null $services
 * @property bool $is_planned
 * @property-read string $path
 */
class Facility extends Model implements HasMedia
{
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }
    use InteractsWithMedia, LogsActivity, SoftDeletes;

    public const CATEGORIES = [
        'dining' => 'Dining and kitchen',
        'health' => 'Health',
        'staff' => 'Staff',
        'boarding' => 'Boarding',
        'worship' => 'Worship',
        'academic' => 'Academic',
        'sports' => 'Sports',
        'general' => 'Other',
    ];

    protected $fillable = ['name', 'slug', 'category', 'summary', 'about', 'services', 'hours', 'location', 'in_charge', 'in_charge_role', 'contact', 'sort_order', 'is_active', 'is_planned'];

    protected function casts(): array
    {
        return ['services' => 'array', 'is_active' => 'boolean', 'is_planned' => 'boolean'];
    }

    protected function about(): Attribute
    {
        return Attribute::make(set: fn (?string $v) => $v === null ? null : Purifier::clean($v, 'default'));
    }

    protected function path(): Attribute
    {
        return Attribute::get(fn () => '/facilities/'.$this->slug);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /** True when there is nothing beyond the name and summary to show yet. */
    public function needsDetails(): bool
    {
        return trim(strip_tags((string) $this->about)) === '' && ! $this->services && ! $this->hours && ! $this->in_charge && $this->getMedia('gallery')->isEmpty();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'slug', 'is_active'])->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }
}
