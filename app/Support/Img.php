<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Img
{
    /** URL of a WebP conversion when it exists, otherwise the original. */
    public static function url(?Media $media, string $conversion = 'card'): ?string
    {
        if ($media === null) {
            return null;
        }

        return $media->hasGeneratedConversion($conversion) ? $media->getUrl($conversion) : $media->getUrl();
    }

    /** Featured image, falling back to the first image used in the body. */
    public static function cover(HasMedia&Model $model): ?Media
    {
        return $model->getFirstMedia('featured') ?? $model->getFirstMedia('content');
    }

    public static function alt(?Media $media, string $fallback = ''): string
    {
        return (string) ($media?->getCustomProperty('alt') ?: $fallback);
    }
}
