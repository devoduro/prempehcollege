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
 * @property list<array{title?: string, text?: string}>|null $activities
 * @property-read string $path
 */
class Denomination extends Model implements HasMedia
{
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }
    use InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = ['name', 'slug', 'summary', 'about', 'patron', 'patron_role', 'meeting_times', 'venue', 'activities', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['activities' => 'array', 'is_active' => 'boolean'];
    }

    protected function about(): Attribute
    {
        return Attribute::make(set: fn (?string $v) => $v === null ? null : Purifier::clean($v, 'default'));
    }

    protected function path(): Attribute
    {
        return Attribute::get(fn () => '/church-life/'.$this->slug);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('name');
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
