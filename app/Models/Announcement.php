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
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string|null $body
 * @property ContentStatus $status
 * @property bool $is_pinned
 * @property-read string $path
 */
class Announcement extends Model implements HasMedia
{
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }
    use InteractsWithMedia, IsPublishable, LogsActivity, PurifiesBody, SoftDeletes;

    protected $fillable = ['title', 'slug', 'excerpt', 'body', 'is_pinned', 'status', 'published_at', 'expires_at'];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected function path(): Attribute
    {
        return Attribute::get(fn () => '/announcements/'.$this->slug);
    }

    /** Published and not expired; pinned notices first, then newest. */
    public function scopeCurrent(Builder $q): Builder
    {
        return $this->scopePublished($q)
            ->where(fn ($w) => $w->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderByDesc('is_pinned')->latest('published_at');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['title', 'slug', 'status', 'is_pinned'])->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
        $this->addMediaCollection('content');
        // Downloadable notices (PDF letters, forms, timetables).
        $this->addMediaCollection('files')->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }
}
