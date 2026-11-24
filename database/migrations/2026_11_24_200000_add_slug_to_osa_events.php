<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('osa_events', fn (Blueprint $t) => $t->string('slug', 120)->nullable()->after('title'));

        $used = [];
        foreach (DB::table('osa_events')->orderBy('id')->get() as $e) {
            $base = Str::slug($e->title) ?: 'meeting';
            $slug = $base;
            for ($i = 2; in_array($slug, $used, true); $i++) {
                $slug = $base.'-'.$i;
            }
            $used[] = $slug;
            DB::table('osa_events')->where('id', $e->id)->update(['slug' => $slug]);
        }

        Schema::table('osa_events', fn (Blueprint $t) => $t->unique('slug'));
    }

    public function down(): void
    {
        Schema::table('osa_events', function (Blueprint $t) {
            $t->dropUnique(['slug']);
            $t->dropColumn('slug');
        });
    }
};
