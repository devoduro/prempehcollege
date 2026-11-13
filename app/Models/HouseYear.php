<?php

namespace App\Models;

use App\Models\Concerns\HasWebpConversions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * A school year in the life of a house: its housemaster, house prefect, achievements and photos.
 *
 * @property string $academic_year
 */
class HouseYear extends Model implements HasMedia
{
    use HasWebpConversions {
        HasWebpConversions::registerMediaConversions insteadof InteractsWithMedia;
    }
    use InteractsWithMedia;

    protected $fillable = ['house_id', 'academic_year', 'housemaster', 'assistant_housemaster', 'house_prefect', 'assistant_house_prefect', 'achievements', 'notes'];

    /** @return BelongsTo<House, $this> */
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    /** Achievements are stored as plain text, one per line. @return list<string> */
    public function achievementLines(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->achievements) ?: [])));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('group_photo')->singleFile();
        $this->addMediaCollection('housemaster_photo')->singleFile();
        $this->addMediaCollection('house_prefect_photo')->singleFile();
        $this->addMediaCollection('gallery');
    }
}
