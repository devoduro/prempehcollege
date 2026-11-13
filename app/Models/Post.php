<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\HasWebpConversions;
use App\Models\Concerns\IsPublishable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Concerns\PurifiesBody;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, IsPublishable, LogsActivity, PurifiesBody, SoftDeletes;
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $fillable = ['category_id', 'title', 'slug', 'excerpt', 'body', 'status',
        'seo_title', 'seo_description', 'published_at', 'legacy_id', 'legacy_url'];

    protected function path(): Attribute
    {
        return Attribute::get(fn () => '/news/'.$this->slug);
    }

    protected function casts(): array
    {
        return ['status' => ContentStatus::class, 'published_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
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
