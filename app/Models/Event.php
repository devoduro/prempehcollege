<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasWebpConversions;
use App\Models\Concerns\IsPublishable;
use App\Models\Concerns\PurifiesBody;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string|null $body
 * @property ContentStatus $status
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 * @property-read string $path
 */
class Event extends Model implements HasMedia
{
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }
    use InteractsWithMedia, IsPublishable, LogsActivity, PurifiesBody, SoftDeletes;

    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'starts_at', 'ends_at', 'all_day', 'venue', 'status', 'published_at'];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'all_day' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected function path(): Attribute
    {
        return Attribute::get(fn () => '/events/'.$this->slug);
    }

    /** Events that have not finished yet, soonest first. */
    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->where(fn ($w) => $w->where('starts_at', '>=', now()->startOfDay())->orWhere('ends_at', '>=', now()))
            ->orderBy('starts_at');
    }

    public function scopePast(Builder $q): Builder
    {
        return $q->where('starts_at', '<', now()->startOfDay())
            ->where(fn ($w) => $w->whereNull('ends_at')->orWhere('ends_at', '<', now()))
            ->orderByDesc('starts_at');
    }

    public function isPast(): bool
    {
        return ($this->ends_at ?? $this->starts_at)->isPast() && ! $this->starts_at->isToday();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['title', 'slug', 'status', 'starts_at'])->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
        $this->addMediaCollection('content');
    }
}
