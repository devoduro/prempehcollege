<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasWebpConversions;
use App\Models\Concerns\IsPublishable;
use App\Models\Concerns\PurifiesBody;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string|null $body
 * @property ContentStatus $status
 * @property-read string $path
 * @property string|null $legacy_url
 * @property list<array{label?: string, value?: string}>|null $facts
 * @property list<array{year?: string, title?: string, text?: string}>|null $highlights
 * @property string|null $news_category
 */
class Page extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, IsPublishable, LogsActivity, PurifiesBody, SoftDeletes;
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = ['parent_id', 'title', 'slug', 'section', 'body', 'template', 'status',
        'seo_title', 'seo_description', 'sort_order', 'published_at', 'legacy_id', 'legacy_url',
        'facts', 'highlights', 'news_category'];

    /** Public URL path, e.g. /about/history, /contact, or / for the home page. */
    protected function path(): Attribute
    {
        return Attribute::get(fn () => $this->template === 'home'
            ? '/'
            : '/'.trim(($this->section ? $this->section.'/' : '').$this->slug, '/'));
    }

    protected function casts(): array
    {
        return ['status' => ContentStatus::class, 'published_at' => 'datetime', 'facts' => 'array', 'highlights' => 'array'];
    }

    /** @return list<string> Category slugs whose news belongs on this page (activity pages). */
    public function newsCategorySlugs(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->news_category))));
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /** @return HasOne<StaffGroup, $this> */
    public function staffGroup(): HasOne
    {
        return $this->hasOne(StaffGroup::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['title', 'slug', 'status'])->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
        $this->addMediaCollection('content');
        $this->addMediaCollection('gallery');
    }
}
