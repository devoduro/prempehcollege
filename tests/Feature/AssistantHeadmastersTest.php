<?php

use App\Models\StaffGroup;
use App\Models\StaffMember;
use Database\Seeders\AssistantHeadmastersSeeder;

it('shows the assistant headmasters page', function () {
    $g = StaffGroup::create(['name' => 'Management', 'slug' => 'm', 'kind' => 'leadership', 'sort_order' => 0]);
    foreach (['Assistant Headmaster, Academics', 'Assistant Headmaster, Admin', 'Assistant Headmaster, Domestic'] as $i => $role) {
        StaffMember::create(['staff_group_id' => $g->id, 'name' => 'Person '.$i, 'role' => $role, 'is_published' => true, 'sort_order' => $i]);
    }

    $this->seed(AssistantHeadmastersSeeder::class);
    $this->seed(AssistantHeadmastersSeeder::class);

    $this->get('/leadership/assistant-headmasters')->assertOk()->assertSee('Assistant Headmaster, Administration')->assertSee('Assistant Headmaster, Domestic');
    expect(StaffMember::where('role', 'Assistant Headmaster, Domestic')->count())->toBe(2); // the Management original plus one copy, even after seeding twice
});
