<?php

namespace App\Support;

use App\Enums\ContentStatus;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;

/** Builds a navigation tree, hiding links to unpublished pages and empty dropdowns. */
class Menu
{
    /** @return list<array{label:string, url:?string, external:bool, children:list<array{label:string, url:?string, external:bool}>}> */
    public static function tree(string $menu): array
    {
        return Cache::remember("menu.$menu", 600, function () use ($menu) {
            $items = MenuItem::with(['page', 'children.page'])
                ->where('menu', $menu)->whereNull('parent_id')->where('is_visible', true)
                ->orderBy('sort_order')->get();

            $out = [];
            foreach ($items as $item) {
                $children = [];
                foreach ($item->children as $child) {
                    if ($link = self::link($child)) {
                        $children[] = $link;
                    }
                }
                $link = self::link($item);
                if ($link === null && $children === []) {
                    continue; // e.g. "Admission" (no page yet) or a parent whose pages are all drafts
                }
                $out[] = ($link ?? ['label' => self::label($item->label), 'url' => null, 'external' => false]) + ['children' => $children];
            }

            return $out;
        });
    }

    /** @return array{label:string, url:?string, external:bool}|null */
    private static function link(MenuItem $item): ?array
    {
        if (! $item->is_visible) {
            return null;
        }
        if ($item->page) {
            if ($item->page->status !== ContentStatus::Published) {
                return null;
            }

            return ['label' => self::label($item->label), 'url' => $item->page->path, 'external' => false];
        }
        if ($item->url) {
            return ['label' => self::label($item->label), 'url' => $item->url, 'external' => (bool) preg_match('~^https?://~', $item->url)];
        }

        return null;
    }

    private static function label(string $label): string
    {
        return trim(html_entity_decode($label, ENT_QUOTES | ENT_HTML5));
    }
}
