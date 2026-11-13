<?php

namespace App\Services\Migration\Importers;

use App\Models\MenuItem;
use App\Services\Migration\ImportContext;

class MenuImporter
{
    /** WordPress nav_menu term id => new menu key. */
    private const MENUS = [47 => 'main', 44 => 'footer'];

    public function __construct(private readonly ImportContext $ctx) {}

    public function run(): void
    {
        foreach (self::MENUS as $termId => $key) {
            $items = $this->ctx->wp()->table('posts as p')
                ->join('term_relationships as tr', 'tr.object_id', '=', 'p.ID')
                ->join('term_taxonomy as tt', 'tt.term_taxonomy_id', '=', 'tr.term_taxonomy_id')
                ->where('tt.term_id', $termId)->where('tt.taxonomy', 'nav_menu')
                ->where('p.post_type', 'nav_menu_item')->where('p.post_status', 'publish')
                ->orderBy('p.menu_order')->get(['p.ID', 'p.post_title', 'p.menu_order']);

            $created = [];
            $pending = [];
            foreach ($items as $it) {
                $this->ctx->report->source('menu items');
                $type = $this->ctx->meta((int) $it->ID, '_menu_item_type');
                $object = (int) $this->ctx->meta((int) $it->ID, '_menu_item_object_id');
                $url = (string) $this->ctx->meta((int) $it->ID, '_menu_item_url');
                $parent = (int) $this->ctx->meta((int) $it->ID, '_menu_item_menu_item_parent');
                $label = html_entity_decode(trim((string) $it->post_title));
                $pageId = null;

                if ($type === 'post_type') {
                    $map = $this->ctx->mapped('page', $object);
                    if (! $map) {
                        $this->ctx->report->skipped('menu items', "\"$label\" (wp {$it->ID}) points at a page that was not imported");

                        continue;
                    }
                    $pageId = $map->model_id;
                    $label = $label !== '' ? $label : html_entity_decode((string) $this->ctx->wp()->table('posts')->where('ID', $object)->value('post_title'));
                    $url = '';
                } elseif ($type === 'taxonomy') {
                    $this->ctx->report->skipped('menu items', "\"$label\" (wp {$it->ID}) points at a category archive");

                    continue;
                }
                $url = ($url === '#') ? '' : $url;

                $model = MenuItem::updateOrCreate(['legacy_id' => $it->ID], [
                    'menu' => $key,
                    'label' => $label,
                    'url' => $url !== '' ? $url : null,
                    'page_id' => $pageId,
                    'sort_order' => (int) $it->menu_order,
                    'is_visible' => true,
                ]);
                $created[(int) $it->ID] = $model->id;
                $pending[(int) $it->ID] = $parent;
                $this->ctx->report->imported('menu items');
            }
            foreach ($pending as $wpId => $parentWp) {
                if ($parentWp && isset($created[$parentWp])) {
                    MenuItem::whereKey($created[$wpId])->update(['parent_id' => $created[$parentWp]]);
                }
            }
        }

        $empty = MenuItem::where('menu', 'main')->whereNull('url')->whereNull('page_id')->whereDoesntHave('children')->pluck('label');
        foreach ($empty as $label) {
            $this->ctx->report->note('Menu entries with no page or link and no children (design them or remove)', $label);
        }
    }
}
