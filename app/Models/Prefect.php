<?php

namespace App\Models;

use App\Models\Concerns\HasWebpConversions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/** A person who held a prefect position on a board. */
class Prefect extends Model implements HasMedia
{
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }
    use InteractsWithMedia;

    protected $fillable = ['prefect_board_id', 'prefect_position_id', 'custom_title', 'name', 'house_id', 'programme_class', 'bio', 'sort_order', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    /** @return BelongsTo<PrefectBoard, $this> */
    public function board(): BelongsTo
    {
        return $this->belongsTo(PrefectBoard::class, 'prefect_board_id');
    }

    /** @return BelongsTo<PrefectPosition, $this> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(PrefectPosition::class, 'prefect_position_id');
    }

    /** @return BelongsTo<House, $this> */
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    /** The standard position title, or the custom one when none fits. */
    public function displayTitle(): string
    {
        return (string) ($this->custom_title ?: $this->position?->title);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }
}
