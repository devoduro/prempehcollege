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
        $this->addMediaConversion('thumb')->fit(Fit::Crop, 480, 320)->format('webp')->nonQueued();
        $this->addMediaConversion('card')->width(800)->format('webp')->nonQueued();
        $this->addMediaConversion('large')->width(1600)->format('webp')->nonQueued();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->webpConversions();
    }
}
