<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Prepares stored (already purified) HTML for display: makes root-relative links work under any
 * base URL, lazy-loads images, and swaps YouTube iframes for click-to-load buttons.
 */
class Content
{
    /**
     * Rendered HTML plus a table of contents built from its h2 headings (ids are added to the headings).
     *
     * @return array{html: string, toc: list<array{id: string, text: string}>}
     */
    public static function withToc(?string $html): array
    {
        $toc = [];
        $used = [];
        $html = preg_replace_callback('~<h2>(.*?)</h2>~is', function (array $m) use (&$toc, &$used) {
            $text = trim(html_entity_decode(strip_tags($m[1])));
            $id = Str::slug($text) ?: 'section';
            $id .= isset($used[$id]) ? '-'.(++$used[$id]) : '';
            $used[$id] ??= 1;
            $toc[] = ['id' => $id, 'text' => $text];

            return '<h2 id="'.$id.'">'.$m[1].'</h2>';
        }, (string) $html) ?? (string) $html;

        return ['html' => self::render($html), 'toc' => $toc];
    }

    /**
     * Stored images are full-size originals (some over 2000 px). Show the smaller WebP "card" version and keep the
     * larger one for the enlarge-on-click viewer, and give images with no description a readable one from the file name.
     */
    private static function optimiseImages(string $html): string
    {
        $root = storage_path('app/public');

        return preg_replace_callback('~<img\b[^>]*>~i', function (array $m) use ($root) {
            $tag = $m[0];
            if (! preg_match('~\ssrc="([^"]*/storage/(\d+)/([^"/]+))"~i', $tag, $s)) {
                return $tag;
            }
            [, $src, $id, $file] = $s;
            $name = pathinfo($file, PATHINFO_FILENAME);
            $base = substr($src, 0, strrpos($src, '/storage/') + 9);

            $card = "$id/conversions/$name-card.webp";
            $large = "$id/conversions/$name-large.webp";
            if (is_file("$root/$card")) {
                $tag = str_replace('src="'.$src.'"', 'src="'.$base.$card.'" data-full="'.$base.(is_file("$root/$large") ? $large : "$id/$file").'"', $tag);
            }

            if (preg_match('~\salt=""~i', $tag)) {
                $tag = preg_replace('~\salt=""~i', ' alt="'.e(self::altFromFilename($name)).'"', $tag, 1) ?? $tag;
            }

            return $tag;
        }, $html) ?? $html;
    }

    /** "Admin-headmaster" -> "Admin headmaster"; drops WordPress size/scaled suffixes and stray digits. */
    private static function altFromFilename(string $name): string
    {
        $name = preg_replace('/-(scaled|\d+x\d+)$/i', '', $name) ?? $name;
        $name = trim((string) preg_replace('/[-_]+/', ' ', $name));
        $name = trim((string) preg_replace('/\s+\d+$/', '', $name));

        return $name === '' || preg_match('/^[\d\s]+$/', $name) ? 'Photograph' : $name;
    }

    public static function render(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $storage = rtrim(asset('storage'), '/');
        $base = rtrim(url('/'), '/');

        $html = str_replace(['src="/storage/', 'href="/storage/'], ['src="'.$storage.'/', 'href="'.$storage.'/'], $html);
        $html = preg_replace('~href="/(?!/)~', 'href="'.$base.'/', $html) ?? $html;
        $html = self::optimiseImages($html);
        $html = preg_replace('/<img\b(?![^>]*\sloading=)/i', '<img loading="lazy" decoding="async"', $html) ?? $html;

        return preg_replace_callback(
            '~<iframe[^>]*src="https://www\.youtube-nocookie\.com/embed/([A-Za-z0-9_-]{11})[^"]*"[^>]*>\s*</iframe>~i',
            fn (array $m) => view('components.video-embed', ['id' => $m[1], 'title' => 'Video'])->render(),
            $html,
        ) ?? $html;
    }
}
