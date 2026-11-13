<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('houses', function (Blueprint $t) {
            $t->longText('about')->nullable()->after('summary'); // "About the house" (purified HTML), separate from history
        });

        // One row per house per school year: who led the house and what it achieved.
        Schema::create('house_years', function (Blueprint $t) {
            $t->id();
            $t->foreignId('house_id')->constrained()->cascadeOnDelete();
            $t->string('academic_year', 9);                 // e.g. 2025/2026
            $t->string('housemaster', 120)->nullable();
            $t->string('assistant_housemaster', 120)->nullable();
            $t->string('house_prefect', 120)->nullable();
            $t->string('assistant_house_prefect', 120)->nullable();
            $t->text('achievements')->nullable();           // plain text, one paragraph per line
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->unique(['house_id', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('house_years');
        Schema::table('houses', function (Blueprint $t) {
            $t->dropColumn('about');
        });
    }
};
