<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** "Class of 2027" boards become the academic year they finish in: "2026/2027" (address /prefects/2026-2027). */
    public function up(): void
    {
        foreach (DB::table('prefect_boards')->where('slug', 'like', 'class-of-%')->get() as $board) {
            if (! preg_match('/^class-of-(\d{4})$/', $board->slug, $m)) {
                continue;
            }
            $end = (int) $m[1];
            $year = ($end - 1).'/'.$end;
            $slug = ($end - 1).'-'.$end;
            if (DB::table('prefect_boards')->where('slug', $slug)->exists()) {
                continue;
            }
            DB::table('prefect_boards')->where('id', $board->id)->update([
                'name' => $year.' Prefectorial Board',
                'slug' => $slug,
                'academic_year' => $year,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('prefect_boards')->where('slug', 'regexp', '^[0-9]{4}-[0-9]{4}$')->get() as $board) {
            $end = (int) substr($board->slug, 5);
            DB::table('prefect_boards')->where('id', $board->id)->update([
                'name' => 'Class of '.$end, 'slug' => 'class-of-'.$end, 'academic_year' => (string) $end,
            ]);
        }
    }
};
