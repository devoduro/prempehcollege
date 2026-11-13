<?php

namespace App\Services\Migration\Importers;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Services\Migration\ImportContext;
use Illuminate\Support\Str;
use Throwable;

class PostImporter
{
    public function __construct(private readonly ImportContext $ctx) {}

    public function run(): void
    {
        $posts = $this->ctx->wp()->table('posts')
            ->where('post_type', 'post')->whereIn('post_status', ['publish', 'draft', 'future', 'private'])
            ->orderBy('post_date')->get();

        foreach ($posts as $p) {
            $this->ctx->report->source('posts');
            if (in_array((int) $p->ID, config('migration.skip_post_ids', []), true)) {
                $this->ctx->report->skipped('posts', "\"{$p->post_title}\" (wp {$p->ID}) is an empty placeholder");

                continue;
            }
            try {
                $this->import($p);
            } catch (Throwable $e) {
                $this->ctx->report->failed('posts', "\"{$p->post_title}\" (wp {$p->ID}): ".$e->getMessage());
            }
        }
    }

    private function import(object $p): void
    {
        $title = html_entity_decode((string) $p->post_title);
        $html = $this->ctx->bodyHtml($p, "post \"$title\"");
        $this->ctx->noteEncodingDamage($title, "post title wp {$p->ID}");

        $checksum = sha1($html.$title.$p->post_modified);
        $existing = $this->ctx->mapped('post', (int) $p->ID);
        if ($existing && $existing->checksum === $checksum) {
            $this->ctx->report->imported('posts');

            return;
        }

        $status = $p->post_status === 'publish' ? ContentStatus::Published : ContentStatus::Draft;
        if ($status === ContentStatus::Draft) {
            $this->ctx->report->note('Posts imported as draft (were not published in WordPress)', "\"$title\" (wp {$p->ID})");
        }

        $slug = rawurldecode((string) $p->post_name) ?: Str::slug($title);
        $slug = Str::slug($slug) ?: 'post-'.$p->ID;
        if (Post::withTrashed()->where('slug', $slug)->where('legacy_id', '!=', $p->ID)->exists()) {
            $slug .= '-'.$p->ID;
        }

        $date = $this->ctx->publishedAt($p);
        $excerpt = trim((string) $p->post_excerpt) !== '' ? trim(strip_tags((string) $p->post_excerpt)) : Str::limit($this->ctx->cleaner->plain($html), 220);

        $post = Post::withTrashed()->updateOrCreate(['legacy_id' => $p->ID], [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'status' => $status,
            'published_at' => $date,
            'legacy_url' => rtrim((string) config('migration.site_url'), '/').'/'.date('Y/m/d', strtotime((string) $p->post_date)).'/'.$p->post_name.'/',
        ] + $this->ctx->seo((int) $p->ID));

        $post->body = $this->ctx->media->rewrite($post, $html, "post \"$title\"");
        $post->save();

        $this->terms($post, (int) $p->ID);

        if ($thumb = (int) $this->ctx->meta((int) $p->ID, '_thumbnail_id')) {
            if ($rel = $this->ctx->resolver->attachmentRelative($thumb)) {
                $this->ctx->media->attach($post, $rel, 'featured');
            } else {
                $this->ctx->report->note('Featured images whose file record is missing', "post \"$title\"");
            }
        }

        $this->ctx->remember('post', (int) $p->ID, $post, $checksum);
        $this->ctx->report->imported('posts');
    }

    private function terms(Post $post, int $wpId): void
    {
        $rows = $this->ctx->wp()->table('term_relationships as tr')
            ->join('term_taxonomy as tt', 'tt.term_taxonomy_id', '=', 'tr.term_taxonomy_id')
            ->join('terms as t', 't.term_id', '=', 'tt.term_id')
            ->where('tr.object_id', $wpId)->whereIn('tt.taxonomy', ['category', 'post_tag'])
            ->get(['t.name', 'tt.taxonomy']);

        $category = null;
        $tagIds = [];
        foreach ($rows as $r) {
            $name = html_entity_decode((string) $r->name);
            $slug = Str::slug($name);
            if ($slug === '' || $slug === 'uncategorized') {
                continue;
            }
            if ($r->taxonomy === 'category') {
                $category ??= Category::firstOrCreate(['slug' => $slug], ['name' => $name]);
            } else {
                // Tags are de-duplicated by slug so "Sports"/"sports" variants collapse.
                $tagIds[] = Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            }
        }
        $post->category_id = $category?->id;
        $post->save();
        $post->tags()->sync(array_unique($tagIds));
    }
}
