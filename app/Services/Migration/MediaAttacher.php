<?php

namespace App\Services\Migration;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/** Imports local image files into the media library and rewrites <img> tags to the stored copies. */
class MediaAttacher
{
    public function __construct(
        private readonly MediaResolver $resolver,
        private readonly MigrationReport $report,
        private readonly bool $dryRun,
    ) {}

    public function attach(HasMedia&Model $model, string $relative, string $collection, ?string $alt = null): ?Media
    {
        $path = $this->resolver->path($relative);
        if ($path === null) {
            $this->report->note('Images missing from the uploads folder', $relative);

            return null;
        }
        if ($this->dryRun) {
            $this->report->imported('images (dry run, not stored)');

            return null;
        }

        $existing = $model->getMedia($collection)->first(fn (Media $m) => $m->getCustomProperty('wp_rel') === $relative);
        if ($existing) {
            return $existing;
        }

        $media = $model->addMedia($path)
            ->preservingOriginal()
            ->usingFileName($this->safeName(basename($path)))
            ->withCustomProperties(['wp_rel' => $relative, 'alt' => $alt])
            ->toMediaCollection($collection);
        $this->report->imported('images');

        return $media;
    }

    /** Rewrites every site-hosted <img> in the HTML to point at the stored media copy. */
    public function rewrite(HasMedia&Model $model, string $html, string $context): string
    {
        return preg_replace_callback('/<img\b[^>]*>/i', function (array $m) use ($model, $context) {
            $tag = $m[0];
            if (! preg_match('/\ssrc="([^"]+)"/i', $tag, $src)) {
                return $tag;
            }
            $rel = $this->resolver->relative($src[1]);
            if ($rel === null) {
                $this->report->note('External or non-upload images left as-is', $src[1]);

                return $tag;
            }

            preg_match('/\salt="([^"]*)"/i', $tag, $altM);
            $alt = trim(html_entity_decode($altM[1] ?? ''));
            if ($alt === '') {
                $meta = $this->resolver->meta($rel);
                $alt = $meta['alt'] ?? '';
                if ($alt === '') {
                    $this->report->note('Images without alt text (add descriptions in the admin)', $context);
                }
            }

            $media = $this->attach($model, $rel, 'content', $alt ?: null);
            if ($media === null) {
                return $tag;
            }
            $url = '/storage/'.$media->getPathRelativeToRoot();

            return '<img src="'.htmlspecialchars($url, ENT_QUOTES).'" alt="'.htmlspecialchars($alt, ENT_QUOTES).'">';
        }, $html) ?? $html;
    }

    private function safeName(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9._-]+/', '-', $name) ?: 'file';
    }
}
