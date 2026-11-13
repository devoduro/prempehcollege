<?php

namespace App\Services\Migration;

use Illuminate\Support\Facades\DB;

/** Maps WordPress upload URLs to local files in the unzipped uploads folder. */
class MediaResolver
{
    /** @var array<string, array{alt:?string, title:?string}> */
    private array $attachmentMeta = [];

    public function __construct(private readonly string $base) {}

    /** Returns the path relative to wp-content/uploads, or null if the URL is not a site upload. */
    public function relative(string $url): ?string
    {
        $url = html_entity_decode(trim($url));
        if (preg_match('#wp-content/uploads/(.+?)(?:\?.*)?$#i', $url, $m)) {
            return rawurldecode($m[1]);
        }

        return null;
    }

    /** Finds the best local file for a relative upload path, trying size-suffix and -scaled variants. */
    public function path(string $relative): ?string
    {
        foreach ($this->candidates($relative) as $candidate) {
            $full = rtrim($this->base, '/\\').'/'.$candidate;
            if (is_file($full)) {
                return $full;
            }
        }

        return null;
    }

    /** @return list<string> */
    private function candidates(string $rel): array
    {
        $out = [$rel];
        $noSize = preg_replace('/-\d+x\d+(?=\.[A-Za-z0-9]+$)/', '', $rel);
        $out[] = $noSize;
        $out[] = preg_replace('/-scaled(?=\.[A-Za-z0-9]+$)/', '', $noSize);
        $out[] = preg_replace('/(?=\.[A-Za-z0-9]+$)/', '-scaled', $noSize);

        return array_values(array_unique($out));
    }

    /** Looks up the stored upload path of an attachment post (for featured images). */
    public function attachmentRelative(int $attachmentId): ?string
    {
        $v = DB::connection('wp_source')->table('postmeta')
            ->where('post_id', $attachmentId)->where('meta_key', '_wp_attached_file')->value('meta_value');

        return $v ?: null;
    }

    /** @return array{alt:?string, title:?string} */
    public function meta(string $relative): array
    {
        if (isset($this->attachmentMeta[$relative])) {
            return $this->attachmentMeta[$relative];
        }
        $base = preg_replace('/-\d+x\d+(?=\.[A-Za-z0-9]+$)/', '', $relative);
        $id = DB::connection('wp_source')->table('postmeta')
            ->where('meta_key', '_wp_attached_file')->whereIn('meta_value', [$relative, $base])->value('post_id');
        $alt = $title = null;
        if ($id) {
            $alt = DB::connection('wp_source')->table('postmeta')
                ->where('post_id', $id)->where('meta_key', '_wp_attachment_image_alt')->value('meta_value');
            $title = DB::connection('wp_source')->table('posts')->where('ID', $id)->value('post_title');
        }

        return $this->attachmentMeta[$relative] = ['alt' => $alt ?: null, 'title' => $title ?: null];
    }
}
