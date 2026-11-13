<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Home page slider, managed from the admin.
        Schema::create('hero_slides', function (Blueprint $t) {
            $t->id();
            $t->string('eyebrow', 80)->nullable();
            $t->string('title', 160);
            $t->string('subtitle', 300)->nullable();
            $t->string('button_label', 40)->nullable();
            $t->string('button_url', 255)->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
        });

        Schema::create('houses', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('slug')->unique();
            $t->string('colour', 7)->nullable();          // house colour as #rrggbb
            $t->string('motto', 190)->nullable();
            $t->text('summary')->nullable();
            $t->longText('history')->nullable();          // purified HTML
            $t->string('namesake', 190)->nullable();      // who the house is named after
            $t->string('housemaster', 120)->nullable();
            $t->unsignedSmallInteger('founded_year')->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('programmes', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('slug')->unique();
            $t->text('summary')->nullable();
            $t->longText('description')->nullable();      // purified HTML
            $t->json('core_subjects')->nullable();        // ["English Language", ...]
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
            $t->softDeletes();
        });

        // Elective combinations offered inside a programme, e.g. "Physics, Chemistry, Elective Maths, Biology".
        Schema::create('course_combinations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $t->string('name', 120)->nullable();
            $t->json('subjects');
            $t->string('notes', 300)->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        // Prefectship: a board (one school year), a reusable list of positions, and the people who held them.
        Schema::create('prefect_boards', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);                      // e.g. "2025/2026 Prefectorial Board"
            $t->string('slug')->unique();
            $t->string('academic_year', 20)->index();     // e.g. 2025/2026
            $t->text('description')->nullable();
            $t->boolean('is_current')->default(false)->index();
            $t->boolean('is_published')->default(true)->index();
            $t->timestamps();
        });

        Schema::create('prefect_positions', function (Blueprint $t) {
            $t->id();
            $t->string('title', 120)->unique();           // e.g. "Senior Prefect"
            $t->text('description')->nullable();
            $t->unsignedSmallInteger('rank')->default(100); // lower = shown first
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
        });

        Schema::create('prefects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('prefect_board_id')->constrained()->cascadeOnDelete();
            $t->foreignId('prefect_position_id')->nullable()->constrained()->nullOnDelete();
            $t->string('custom_title', 120)->nullable();  // used when no standard position fits
            $t->string('name', 120);
            $t->foreignId('house_id')->nullable()->constrained()->nullOnDelete();
            $t->string('programme_class', 80)->nullable(); // e.g. "3 Science 1"
            $t->text('bio')->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_published')->default(true)->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['prefects', 'prefect_positions', 'prefect_boards', 'course_combinations', 'programmes', 'houses', 'hero_slides'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
