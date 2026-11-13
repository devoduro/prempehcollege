<?php

namespace App\Services\Migration\Importers;

use App\Models\Page;
use App\Models\Post;
use App\Models\Redirect;
use App\Services\Migration\ImportContext;

/** Builds 301 redirects from old WordPress paths and rewrites internal links inside migrated content. */
class RedirectImporter
{
    public function __construct(private readonly ImportContext $ctx) {}

    public function run(): void
    {
        foreach (Page::withTrashed()->whereNotNull('legacy_url')->get() as $page) {
            $this->add($page->legacy_url, $page->path);
        }
        foreach (Post::withTrashed()->whereNotNull('legacy_url')->get() as $post) {
            $this->add($post->legacy_url, $post->path);
        }
        $this->ctx->report->note('Redirects', Redirect::count().' old URLs now redirect with 301');

        $this->rewriteLinks();
    }

    private function add(string $legacyUrl, string $to): void
    {
        $from = $this->normalise((string) parse_url($legacyUrl, PHP_URL_PATH));
        if ($from === '/' || $from === $this->normalise($to)) {
            return;
        }
        Redirect::updateOrCreate(['from_path' => $from], ['to_path' => $to, 'status_code' => 301]);
        $this->ctx->report->imported('redirects');
    }

    private function normalise(string $path): string
    {
        return '/'.trim($path, '/').(trim($path, '/') === '' ? '' : '/');
    }

    private function rewriteLinks(): void
    {
        $map = Redirect::pluck('to_path', 'from_path')->all();
        $host = preg_quote((string) parse_url((string) config('migration.site_url'), PHP_URL_HOST), '~');

        $fix = function (string $html, string $context) use ($map, $host): string {
            return preg_replace_callback('~href="(?:https?://(?:www\.)?'.$host.')?(/[^"#?]*)([^"]*)"~i', function ($m) use ($map, $context) {
                $path = $this->normalise($m[1]);
                if (str_contains($path, '/wp-content/uploads/')) {
                    $this->ctx->report->note('Links to uploaded documents (files must be added to Downloads)', $context.' -> '.$m[1]);

                    return $m[0];
                }
                if (isset($map[$path])) {
                    return 'href="'.$map[$path].$m[2].'"';
                }
                $this->ctx->report->note('Internal links with no matching page (fix by hand)', $context.' -> '.$m[1]);

                return $m[0];
            }, $html) ?? $html;
        };

        foreach (Page::query()->whereNotNull('body')->get() as $page) {
            $new = $fix((string) $page->getRawOriginal('body'), "page \"{$page->title}\"");
            if ($new !== $page->getRawOriginal('body')) {
                $page->body = $new;
                $page->save();
            }
        }
        foreach (Post::query()->whereNotNull('body')->get() as $post) {
            $new = $fix((string) $post->getRawOriginal('body'), "post \"{$post->title}\"");
            if ($new !== $post->getRawOriginal('body')) {
                $post->body = $new;
                $post->save();
            }
        }
    }
}
