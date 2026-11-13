<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Every image gets small WebP variants for responsive use, generated synchronously
 * (no queue worker needed). Models call webpConversions() from registerMediaConversions().
 */
trait HasWebpConversions
{
    protected function webpConversions(): void
    {
        $this->addMediaConversion('thumb')->nonQueued()->fit(Fit::Crop, 480, 320)->format('webp');
        $this->addMediaConversion('card')->nonQueued()->width(800)->format('webp');
        $this->addMediaConversion('large')->nonQueued()->width(1600)->format('webp');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->webpConversions();
    }
}
