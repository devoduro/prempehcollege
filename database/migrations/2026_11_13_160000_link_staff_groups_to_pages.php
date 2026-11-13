<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_groups', function (Blueprint $t) {
            // A leadership page (e.g. Management) can show its people as profile cards instead of a stack of photos.
            $t->foreignId('page_id')->nullable()->unique()->after('kind')->constrained('pages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('staff_groups', function (Blueprint $t) {
            $t->dropConstrainedForeignId('page_id');
        });
    }
};
