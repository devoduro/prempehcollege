<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\StaffGroup;
use App\Models\StaffMember;
use Illuminate\Database\Seeder;

/**
 * Assistant Headmasters page (Academics, Administration, Domestic). The names, roles and photos are the ones already on the
 * Management page, copied here so the three assistant headmasters have a page of their own. No biographies were found
 * online, so none are shown. Add them in the admin (People > Staff profiles > Assistant Headmasters). Safe to run more than once.
 */
class AssistantHeadmastersSeeder extends Seeder
{
    private const ROLES = [
        'Assistant Headmaster, Academics' => 'Assistant Headmaster, Academics',
        'Assistant Headmaster, Admin' => 'Assistant Headmaster, Administration',
        'Assistant Headmaster, Domestic' => 'Assistant Headmaster, Domestic',
    ];

    public function run(): void
    {
        $page = Page::where('section', 'leadership')->where('slug', 'assistant-headmasters')->first();
        if (! $page) {
            $page = Page::create([
                'title' => 'Assistant Headmasters', 'slug' => 'assistant-headmasters', 'section' => 'leadership', 'template' => 'default',
                'status' => ContentStatus::Published, 'sort_order' => 15,
                'body' => '<p>The Headmaster is supported by three assistant headmasters, who look after the College\'s academic work, its administration and its domestic (boarding and welfare) life. Meet the <a href="/about/current-headmasters">Headmaster</a> and the rest of <a href="/leadership/management">Management</a>.</p>',
            ]);
        }
        if (StaffGroup::where('page_id', $page->id)->exists()) {
            return;
        }

        $group = StaffGroup::create(['name' => 'Assistant Headmasters', 'slug' => 'page-assistant-headmasters', 'kind' => 'leadership', 'page_id' => $page->id, 'sort_order' => 0]);
        $i = 0;
        foreach (self::ROLES as $old => $role) {
            $source = StaffMember::where('role', $old)->first();
            if (! $source) {
                continue;
            }
            $copy = StaffMember::create(['staff_group_id' => $group->id, 'name' => $source->name, 'role' => $role, 'bio' => $source->bio, 'is_published' => true, 'sort_order' => $i++]);
            if ($photo = $source->getFirstMedia('photo')) {
                $copy->addMedia($photo->getPath())->preservingOriginal()->toMediaCollection('photo');
            }
        }

        $parent = MenuItem::where('menu', 'main')->whereNull('parent_id')->where('label', 'Leadership')->first();
        if ($parent) {
            MenuItem::firstOrCreate(['menu' => 'main', 'parent_id' => $parent->id, 'url' => '/leadership/assistant-headmasters'], ['label' => 'Assistant Headmasters', 'sort_order' => 15, 'is_visible' => true]);
        }
    }
}
