<?php

namespace App\Services\Migration;

/** Cleans classic/Gutenberg post_content before it reaches the purifier. */
class HtmlCleaner
{
    public function __construct(private readonly MigrationReport $report, private readonly ElementorParser $parser) {}

    public function clean(string $html, string $context): string
    {
        // Gutenberg block comments and any other HTML comments.
        $html = preg_replace('/<!--.*?-->/s', '', $html) ?? $html;

        // Known page-builder and media shortcodes (counted so nothing disappears unnoticed).
        $html = preg_replace_callback('/\[(\/?)(caption|gallery|embed|vc_[a-z_]+|et_pb_[a-z_]+|elementor-template|wpforms|contact-form-7)[^\]]*\]/i', function ($m) use ($context) {
            if (! in_array(strtolower($m[2]), ['caption', 'embed'], true)) {
                $this->report->note('Shortcodes removed', "[{$m[2]}] in $context");
            }

            return '';
        }, $html) ?? $html;

        // Standalone YouTube links (classic embeds) become privacy-enhanced iframes.
        $html = preg_replace_callback('~<(p|div)[^>]*>\s*(https?://(?:www\.)?(?:youtube\.com/watch\?[^\s<]+|youtu\.be/[^\s<]+))\s*</\1>~i', function ($m) {
            $id = $this->parser->youtubeId($m[2]);

            return $id ? '<div class="pc-video"><iframe src="https://www.youtube-nocookie.com/embed/'.$id.'" title="Video" allowfullscreen></iframe></div>' : $m[0];
        }, $html) ?? $html;

        // Gutenberg gallery wrappers carry no images when the data lives elsewhere; drop the empty shells.
        $html = preg_replace('~<figure[^>]*wp-block-gallery[^>]*>\s*</figure>~i', '', $html) ?? $html;

        // srcset/sizes point at WordPress sizes; the new media pipeline generates its own.
        $html = preg_replace('/\s(srcset|sizes|loading|decoding)="[^"]*"/i', '', $html) ?? $html;

        // Empty paragraphs and stray &nbsp; padding.
        $html = preg_replace('~<p>\s*(?:&nbsp;|\xC2\xA0|\s)*</p>~i', '', $html) ?? $html;

        return trim($html);
    }

    /** Plain text used for excerpts and demo-copy detection. */
    public function plain(string $html): string
    {
        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html))) ?? '');
    }
}
