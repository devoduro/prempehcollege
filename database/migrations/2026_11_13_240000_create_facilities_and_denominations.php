<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // School facilities: dining hall and kitchen, clinic, staff common room, chapel, dormitories, labs, library...
        Schema::create('facilities', function (Blueprint $t) {
            $t->id();
            $t->string('name', 140);
            $t->string('slug')->unique();
            $t->string('category', 30)->default('general')->index(); // dining, health, staff, boarding, worship, academic, sports, general
            $t->text('summary')->nullable();
            $t->longText('about')->nullable();                 // purified HTML
            $t->json('services')->nullable();                  // ["Three meals a day", ...] or [{title,text}]
            $t->string('hours', 190)->nullable();              // e.g. "Mon to Fri, 7am to 5pm"
            $t->string('location', 190)->nullable();
            $t->string('in_charge', 120)->nullable();          // person in charge
            $t->string('in_charge_role', 120)->nullable();
            $t->string('contact', 120)->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true)->index();
            $t->boolean('is_planned')->default(false);         // announced but not yet built (e.g. a new dormitory)
            $t->timestamps();
            $t->softDeletes();
        });

        // Denominations and fellowships on campus, with their activities.
        Schema::create('denominations', function (Blueprint $t) {
            $t->id();
            $t->string('name', 140);
            $t->string('slug')->unique();
            $t->text('summary')->nullable();
            $t->longText('about')->nullable();                 // purified HTML
            $t->string('patron', 120)->nullable();             // chaplain, patron or leader
            $t->string('patron_role', 120)->nullable();
            $t->string('meeting_times', 190)->nullable();      // e.g. "Sundays, 8am, College Chapel"
            $t->string('venue', 190)->nullable();
            $t->json('activities')->nullable();                // [{title, text}]
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('denominations');
        Schema::dropIfExists('facilities');
    }
};
