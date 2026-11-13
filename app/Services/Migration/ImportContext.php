<?php

namespace App\Services\Migration;

use App\Models\ImportMap;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Shared services and helpers passed to every importer. */
class ImportContext
{
    public readonly MigrationReport $report;

    public readonly MediaResolver $resolver;

    public readonly MediaAttacher $media;

    public readonly ElementorParser $elementor;

    public readonly HtmlCleaner $cleaner;

    public function __construct(public readonly bool $dryRun)
    {
        $this->report = new MigrationReport;
        $this->resolver = new MediaResolver((string) config('migration.media_path'));
        $this->media = new MediaAttacher($this->resolver, $this->report, $dryRun);
        $this->elementor = new ElementorParser($this->report);
        $this->cleaner = new HtmlCleaner($this->report, $this->elementor);
    }

    public function wp(): ConnectionInterface
    {
        return DB::connection('wp_source');
    }

    public function meta(int $postId, string $key): ?string
    {
        $v = $this->wp()->table('postmeta')->where('post_id', $postId)->where('meta_key', $key)->value('meta_value');

        return $v === null ? null : (string) $v;
    }

    public function mapped(string $type, int $wpId): ?ImportMap
    {
        return ImportMap::where('wp_type', $type)->where('wp_id', $wpId)->first();
    }

    public function remember(string $type, int $wpId, Model $model, string $checksum): void
    {
        ImportMap::updateOrCreate(
            ['wp_type' => $type, 'wp_id' => $wpId],
            ['model' => $model::class, 'model_id' => $model->getKey(), 'checksum' => $checksum, 'imported_at' => now()],
        );
    }

    /** Elementor JSON tree for a post, or null when the post is not built with Elementor. */
    public function elementorTree(int $postId): ?array
    {
        $raw = $this->meta($postId, '_elementor_data');
        if (! $raw) {
            return null;
        }
        $tree = json_decode($raw, true);

        return is_array($tree) ? $tree : null;
    }

    /** Body HTML for a WordPress post: Elementor layout when present, otherwise cleaned post_content. */
    public function bodyHtml(object $post, string $context): string
    {
        $tree = $this->elementorTree((int) $post->ID);
        if ($tree !== null) {
            $html = $this->elementor->toHtml($tree, (int) $post->ID);
            if (trim($html) !== '') {
                return $this->cleaner->clean($html, $context);
            }
        }

        return $this->cleaner->clean((string) $post->post_content, $context);
    }

    public function publishedAt(object $post): ?string
    {
        $gmt = (string) $post->post_date_gmt;

        return $gmt !== '' && ! str_starts_with($gmt, '0000') ? $gmt : (string) $post->post_date;
    }

    public function seo(int $postId): array
    {
        $title = $this->meta($postId, '_yoast_wpseo_title');
        $desc = $this->meta($postId, '_yoast_wpseo_metadesc');

        // Yoast templates contain %%variables%% that only make sense inside WordPress.
        return [
            'seo_title' => $title && ! str_contains($title, '%%') ? $title : null,
            'seo_description' => $desc && ! str_contains($desc, '%%') ? mb_substr($desc, 0, 320) : null,
        ];
    }

    public function noteEncodingDamage(string $text, string $where): void
    {
        // Source data shows '?' where curly quotes or accented letters were lost in an old export.
        if (preg_match('/\w\?(?:\s|\?|$)\S|\?\s\?/u', $text)) {
            $this->report->note("Possible character damage ('?' where a symbol was lost); review the text", "$where: ".mb_substr($text, 0, 80));
        }
    }
}
