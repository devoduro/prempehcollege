<?php

namespace App\Services\Migration\Importers;

use App\Models\StaffGroup;
use App\Models\StaffMember;
use App\Services\Migration\ImportContext;
use Illuminate\Support\Str;
use Throwable;

/** AWSM Team Pro: awsm_team posts are groups, awsm_team_member posts are people (mostly photo-only). */
class StaffImporter
{
    public function __construct(private readonly ImportContext $ctx)
    {
    }

    public function run(): void
    {
        $groups = $this->ctx->wp()->table('posts')->where('post_type', 'awsm_team')->where('post_status', 'publish')->orderBy('ID')->get();
        $groupMap = [];
        foreach ($groups as $i => $g) {
            $this->ctx->report->source('staff groups');
            $name = html_entity_decode((string) $g->post_title);
            $model = StaffGroup::updateOrCreate(['legacy_id' => $g->ID], [
                'name' => $name,
                'slug' => Str::slug($name) ?: 'group-'.$g->ID,
                'kind' => str_contains(strtolower($name), 'headmaster') ? 'headmasters' : 'year',
                'sort_order' => $i,
            ]);
            $groupMap[(int) $g->ID] = $model;
            $this->ctx->report->imported('staff groups');
        }

        $members = $this->ctx->wp()->table('posts')->where('post_type', 'awsm_team_member')->where('post_status', 'publish')->orderBy('menu_order')->orderBy('ID')->get();
        foreach ($members as $i => $m) {
            $this->ctx->report->source('staff members');
            try {
                $this->member($m, $i, $groupMap);
            } catch (Throwable $e) {
                $this->ctx->report->failed('staff members', "wp {$m->ID}: ".$e->getMessage());
            }
        }
    }

    /** @param  array<int, StaffGroup>  $groupMap */
    private function member(object $m, int $order, array $groupMap): void
    {
        $groupIds = array_filter(array_map('intval', explode(',', (string) $this->ctx->meta((int) $m->ID, 'awsm-member-teams'))));
        $targets = array_values(array_filter($groupIds, fn ($id) => isset($groupMap[$id])));
        if ($targets === []) {
            $this->ctx->report->failed('staff members', "wp {$m->ID} belongs to no known group");

            return;
        }

        $title = trim(html_entity_decode((string) $m->post_title));
        $name = ($title === '' || ctype_digit($title)) ? null : $title;
        $bio = trim($this->ctx->cleaner->plain((string) $m->post_content)) ?: null;
        $thumb = (int) $this->ctx->meta((int) $m->ID, '_thumbnail_id');
        $rel = $thumb ? $this->ctx->resolver->attachmentRelative($thumb) : null;
        if (! $rel) {
            $this->ctx->report->note('Staff records with no photo', "wp {$m->ID} (title \"$title\")");
        }

        foreach ($targets as $gid) {
            $model = StaffMember::updateOrCreate(['legacy_id' => $m->ID, 'staff_group_id' => $groupMap[$gid]->id], [
                'name' => $name,
                'bio' => $bio,
                'sort_order' => $order,
                'is_published' => true,
            ]);
            if ($rel) {
                $this->ctx->media->attach($model, $rel, 'photo', $name);
            }
        }
        if (count($targets) > 1) {
            $this->ctx->report->note('Staff records placed in more than one group (copied into each)', "wp {$m->ID}: ".count($targets).' groups');
        }
        if ($name === null) {
            $this->ctx->report->note('Staff records have no name in WordPress (name appears only inside the photo); enter names in the admin', 'photo-only record');
        }
        $this->ctx->report->imported('staff members');
    }
}
