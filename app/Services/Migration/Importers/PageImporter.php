<?php

namespace App\Services\Migration\Importers;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Setting;
use App\Services\Migration\ImportContext;
use Illuminate\Support\Str;
use Throwable;

class PageImporter
{
    public function __construct(private readonly ImportContext $ctx)
    {
    }

    public function run(): void
    {
        $pages = $this->ctx->wp()->table('posts')
            ->where('post_type', 'page')->whereIn('post_status', ['publish', 'draft'])
            ->orderBy('menu_order')->orderBy('ID')->get();

        foreach ($pages as $p) {
            $this->ctx->report->source('pages');
            if (in_array((int) $p->ID, config('migration.skip_page_ids', []), true)) {
                $this->ctx->report->skipped('pages', "\"{$p->post_title}\" (wp {$p->ID}) is a theme demo page");

                continue;
            }
            try {
                $this->import($p);
            } catch (Throwable $e) {
                $this->ctx->report->failed('pages', "\"{$p->post_title}\" (wp {$p->ID}): ".$e->getMessage());
            }
        }

        // Second pass: parent links, now that every page has a row.
        foreach ($pages as $p) {
            if ($p->post_parent && ($child = $this->ctx->mapped('page', (int) $p->ID)) && ($parent = $this->ctx->mapped('page', (int) $p->post_parent))) {
                Page::whereKey($child->model_id)->update(['parent_id' => $parent->model_id]);
            }
        }
    }

    private function import(object $p): void
    {
        $title = html_entity_decode((string) $p->post_title);
        $cfg = config('migration.pages.'.$p->post_name);
        if ($cfg === null) {
            $this->ctx->report->note('Pages with no section mapping (placed at site root)', "{$p->post_name} (wp {$p->ID})");
            $cfg = [null, $p->post_name];
        }
        [$section, $slug] = $cfg;
        $template = $cfg[2] ?? 'default';

        $html = $template === 'home' ? '' : $this->ctx->bodyHtml($p, "page \"$title\"");
        $status = $p->post_status === 'publish' ? ContentStatus::Published : ContentStatus::Draft;

        $plain = mb_strtolower($this->ctx->cleaner->plain($html));
        foreach (config('migration.demo_markers', []) as $marker) {
            if ($plain !== '' && str_contains($plain, $marker)) {
                $status = ContentStatus::Draft;
                $this->ctx->report->note('Pages held as draft: contain theme placeholder text', "\"$title\" (wp {$p->ID}), matched \"$marker\"");
                break;
            }
        }
        if ($template !== 'home' && $plain === '') {
            $status = ContentStatus::Draft;
            $this->ctx->report->note('Pages held as draft: no text content (images only or empty)', "\"$title\" (wp {$p->ID})");
        }
        $this->ctx->noteEncodingDamage($title, "page title wp {$p->ID}");

        $checksum = sha1($html.$title.$p->post_modified);
        $existing = $this->ctx->mapped('page', (int) $p->ID);
        if ($existing && $existing->checksum === $checksum) {
            $this->ctx->report->imported('pages');

            return;
        }

        $page = Page::withTrashed()->updateOrCreate(['legacy_id' => $p->ID], [
            'title' => $title,
            'slug' => $slug,
            'section' => $section,
            'template' => $template,
            'status' => $status,
            'sort_order' => (int) $p->menu_order,
            'published_at' => $this->ctx->publishedAt($p),
            'legacy_url' => $this->legacyUrl($p),
        ] + $this->ctx->seo((int) $p->ID));

        if ($template === 'home') {
            $this->homeData($page, (int) $p->ID);
        } else {
            $page->body = $this->ctx->media->rewrite($page, $html, "page \"$title\"");
            $page->save();
        }

        if ($thumb = (int) $this->ctx->meta((int) $p->ID, '_thumbnail_id')) {
            if ($rel = $this->ctx->resolver->attachmentRelative($thumb)) {
                $this->ctx->media->attach($page, $rel, 'featured');
            }
        }

        $this->ctx->remember('page', (int) $p->ID, $page, $checksum);
        $this->ctx->report->imported('pages');
    }

    /** The home page is rebuilt in Blade; keep only its real data (hero photos, video, stats, programmes). */
    private function homeData(Page $page, int $wpId): void
    {
        $tree = $this->ctx->elementorTree($wpId) ?? [];
        $el = $this->ctx->elementor;

        $carousel = [];
        foreach ($el->widgets($tree, ['image-carousel']) as $w) {
            foreach ($w['settings']['carousel'] ?? [] as $img) {
                if (($rel = $this->ctx->resolver->relative((string) ($img['url'] ?? ''))) && $this->ctx->media->attach($page, $rel, 'gallery')) {
                    $carousel[] = $rel;
                }
            }
        }

        $video = null;
        foreach ($el->widgets($tree, ['video']) as $w) {
            $video ??= $el->youtubeId((string) ($w['settings']['youtube_url'] ?? ''));
        }

        $stats = [];
        foreach ($el->widgets($tree, ['price-list']) as $w) {
            foreach ($w['settings']['price_list'] ?? [] as $row) {
                $stats[] = ['programme' => trim((string) $row['title']), 'percent' => trim((string) $row['price'])];
            }
        }

        $programmes = [];
        foreach ($el->widgets($tree, ['icon-list']) as $w) {
            foreach ($w['settings']['icon_list'] ?? [] as $row) {
                $programmes[] = trim(strip_tags((string) ($row['text'] ?? '')));
            }
        }

        if (! $this->ctx->dryRun) {
            foreach (['home.video_id' => $video, 'home.admission_stats' => $stats, 'home.programmes' => array_values(array_unique(array_filter($programmes)))] as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : $value]);
            }
        }
        $this->ctx->report->note('Home page data captured (hero photos, video, stats, programmes); layout is rebuilt in Blade', 'carousel '.count($carousel).' photos, video '.($video ?? 'none').', '.count($stats).' stat rows, '.count($programmes).' programmes');
    }

    private function legacyUrl(object $p): string
    {
        $site = rtrim((string) config('migration.site_url'), '/');
        if ((int) $p->ID === (int) $this->ctx->wp()->table('options')->where('option_name', 'page_on_front')->value('option_value')) {
            return $site.'/';
        }
        $parts = [Str::of((string) $p->post_name)->toString()];
        $parent = (int) $p->post_parent;
        for ($i = 0; $parent && $i < 8; $i++) {
            $row = $this->ctx->wp()->table('posts')->where('ID', $parent)->first(['post_name', 'post_parent']);
            if (! $row) {
                break;
            }
            array_unshift($parts, $row->post_name);
            $parent = (int) $row->post_parent;
        }

        return $site.'/'.implode('/', $parts).'/';
    }

    public static function slugOf(string $s): string
    {
        return Str::slug($s);
    }
}
