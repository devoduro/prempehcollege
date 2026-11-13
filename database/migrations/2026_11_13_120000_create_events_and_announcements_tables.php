<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('excerpt')->nullable();
            $t->longText('body')->nullable();
            $t->dateTime('starts_at')->index();
            $t->dateTime('ends_at')->nullable();
            $t->boolean('all_day')->default(false);
            $t->string('venue', 190)->nullable();
            $t->string('status', 20)->default('draft')->index();
            $t->timestamp('published_at')->nullable()->index();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('announcements', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('excerpt')->nullable();
            $t->longText('body')->nullable();
            $t->boolean('is_pinned')->default(false)->index();
            $t->string('status', 20)->default('draft')->index();
            $t->timestamp('published_at')->nullable()->index();
            // After this date the notice drops off the home page and lists (still reachable by link).
            $t->timestamp('expires_at')->nullable()->index();
            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('events');
    }
};
