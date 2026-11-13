<?php

namespace App\Services\Migration;

/**
 * Converts Elementor's JSON layout tree into clean semantic HTML.
 * Layout wrappers (sections, columns, containers) are flattened; each widget maps to plain markup.
 * Unknown widgets are reported, never silently dropped.
 */
class ElementorParser
{
    /** Widgets that carry no content for the new site (forms and feeds are rebuilt natively). */
    private const IGNORED = [
        'spacer', 'icon', 'theme-page-title', 'wpforms', 'ct_ctf7', 'ct_clients_list', 'posts', 'loop-grid',
        'shortcode', 'menu-anchor', 'sidebar', 'google_maps', 'social-icons', 'counter', 'progress',
    ];

    public function __construct(private readonly MigrationReport $report)
    {
    }

    /** @param  array<int, array<string, mixed>>  $tree */
    public function toHtml(array $tree, int $pageId): string
    {
        $out = '';
        foreach ($tree as $el) {
            $out .= $this->element($el, $pageId);
        }

        return trim($out);
    }

    /**
     * Collect all widgets of the given types, depth first (used for the home page data).
     *
     * @param  array<int, array<string, mixed>>  $tree
     * @param  list<string>  $types
     * @return list<array<string, mixed>>
     */
    public function widgets(array $tree, array $types): array
    {
        $found = [];
        foreach ($tree as $el) {
            if (($el['elType'] ?? '') === 'widget' && in_array($el['widgetType'] ?? '', $types, true)) {
                $found[] = $el;
            }
            if (! empty($el['elements'])) {
                array_push($found, ...$this->widgets($el['elements'], $types));
            }
        }

        return $found;
    }

    /** @param  array<string, mixed>  $el */
    private function element(array $el, int $pageId): string
    {
        if (($el['elType'] ?? '') !== 'widget') {
            $inner = '';
            foreach ($el['elements'] ?? [] as $child) {
                $inner .= $this->element($child, $pageId);
            }

            return $inner;
        }

        $type = (string) ($el['widgetType'] ?? '');
        $s = $el['settings'] ?? [];

        return match ($type) {
            'heading', 'ct_heading' => $this->heading($s),
            'text-editor' => $this->text((string) ($s['editor'] ?? '')),
            'image' => $this->image($s['image'] ?? [], $s['caption'] ?? ''),
            'image-box' => $this->image($s['image'] ?? []).$this->heading(['title' => $s['title_text'] ?? '', 'header_size' => 'h3']).$this->text((string) ($s['description_text'] ?? '')),
            'ct_fancy_box' => $this->heading(['title' => $s['title_text'] ?? '', 'header_size' => 'h3']).$this->text((string) ($s['description_text'] ?? '')),
            'button' => $this->button($s),
            'icon-list' => $this->iconList($s['icon_list'] ?? []),
            'gallery' => $this->gallery($s['gallery'] ?? []),
            'image-carousel' => $this->gallery($s['carousel'] ?? []),
            'video' => $this->video($s),
            'divider' => '<hr>',
            'html' => $this->text((string) ($s['html'] ?? '')),
            'ct_accordion' => $this->accordion($s['ct_accordion'] ?? []),
            'accordion', 'toggle' => $this->accordion($s['tabs'] ?? []),
            default => $this->unknown($type, $pageId),
        };
    }

    /** @param  array<string, mixed>  $s */
    private function heading(array $s): string
    {
        $text = $this->inline((string) ($s['title'] ?? ''));
        if ($text === '') {
            return '';
        }
        $size = (string) ($s['header_size'] ?? 'h2');
        // The page template owns the single h1, so content headings start at h2.
        $tag = match ($size) {
            'h1', 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4', 'h5', 'h6' => 'h5',
            default => null,
        };

        return $tag ? "<$tag>$text</$tag>\n" : "<p><strong>$text</strong></p>\n";
    }

    private function text(string $html): string
    {
        $html = str_ireplace('</br>', '<br>', trim($html));
        if ($html === '' || trim(strip_tags($html, '<img><iframe>')) === '') {
            return '';
        }
        if (! preg_match('/<(p|div|ul|ol|h[1-6]|table|blockquote|figure)\b/i', $html)) {
            $paras = preg_split('/\R\s*\R/', $html) ?: [$html];
            $html = implode('', array_map(fn ($p) => '<p>'.nl2br(trim($p), false).'</p>', array_filter($paras, fn ($p) => trim($p) !== '')));
        }

        return $html."\n";
    }

    /** @param  array<string, mixed>  $img */
    private function image(array $img, string $caption = ''): string
    {
        $url = (string) ($img['url'] ?? '');
        if ($url === '') {
            return '';
        }
        $alt = htmlspecialchars((string) ($img['alt'] ?? ''), ENT_QUOTES);
        $cap = $caption !== '' ? '<figcaption>'.$this->inline($caption).'</figcaption>' : '';

        return '<figure><img src="'.htmlspecialchars($url, ENT_QUOTES).'" alt="'.$alt.'">'.$cap."</figure>\n";
    }

    /** @param  array<string, mixed>  $s */
    private function button(array $s): string
    {
        $text = $this->inline((string) ($s['text'] ?? ''));
        $url = (string) ($s['link']['url'] ?? '');
        if ($text === '') {
            return '';
        }
        if ($url === '' || $url === '#') {
            $this->report->note('Buttons with no target (need real links)', trim(strip_tags($text)));

            return '';
        }

        return '<p><a class="btn" href="'.htmlspecialchars($url, ENT_QUOTES).'">'.$text."</a></p>\n";
    }

    /** @param  list<array<string, mixed>>  $items */
    private function iconList(array $items): string
    {
        $lis = '';
        foreach ($items as $i) {
            $t = $this->inline((string) ($i['text'] ?? ''));
            if ($t !== '') {
                $lis .= "<li>$t</li>";
            }
        }

        return $lis ? "<ul>$lis</ul>\n" : '';
    }

    /** @param  list<array<string, mixed>>  $items */
    private function gallery(array $items): string
    {
        $figs = '';
        foreach ($items as $i) {
            if (! empty($i['url'])) {
                $figs .= '<figure><img src="'.htmlspecialchars((string) $i['url'], ENT_QUOTES).'" alt=""></figure>';
            }
        }

        return $figs ? '<div class="pc-gallery">'.$figs."</div>\n" : '';
    }

    /** @param  array<string, mixed>  $s */
    private function video(array $s): string
    {
        $url = (string) ($s['youtube_url'] ?? '');
        if (($id = $this->youtubeId($url)) !== null) {
            return '<div class="pc-video"><iframe src="https://www.youtube-nocookie.com/embed/'.$id.'" title="Video" allowfullscreen></iframe></div>'."\n";
        }
        $this->report->note('Videos not on YouTube (check manually)', json_encode(array_filter([$s['vimeo_url'] ?? null, $s['video_type'] ?? null])) ?: 'unknown');

        return '';
    }

    /** @param  list<array<string, mixed>>  $items */
    private function accordion(array $items): string
    {
        $out = '';
        foreach ($items as $i) {
            $title = $this->inline((string) ($i['ac_title'] ?? $i['tab_title'] ?? ''));
            $body = (string) ($i['ac_content'] ?? $i['tab_content'] ?? '');
            if ($title !== '') {
                $out .= "<details><summary>$title</summary>".$this->text($body).'</details>';
            }
        }

        return $out."\n";
    }

    private function unknown(string $type, int $pageId): string
    {
        if (! in_array($type, self::IGNORED, true)) {
            $this->report->note('Unrecognised Elementor widgets (content may be lost)', "$type on page $pageId");
        }

        return '';
    }

    /** Inline text: keeps basic emphasis, drops layout markup and tidies break tags. */
    private function inline(string $html): string
    {
        $html = str_ireplace('</br>', '<br>', $html);
        $html = strip_tags($html, '<br><strong><em><b><i><a>');
        $html = preg_replace('/\s*(<br\s*\/?>)\s*/i', '<br>', trim($html)) ?? $html;
        $html = preg_replace('/^(<br>)+|(<br>)+$/i', '', $html) ?? $html;

        return trim($html);
    }

    public function youtubeId(string $url): ?string
    {
        if (preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}
