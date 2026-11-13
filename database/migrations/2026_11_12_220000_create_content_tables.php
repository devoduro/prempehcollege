<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });

        Schema::create('tags', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });

        Schema::create('pages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $t->string('title');
            $t->string('slug');
            $t->string('section', 40)->nullable()->index();
            $t->longText('body')->nullable();
            $t->string('template', 40)->default('default');
            $t->string('status', 20)->default('draft')->index();
            $t->string('seo_title')->nullable();
            $t->string('seo_description', 320)->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamp('published_at')->nullable()->index();
            $t->unsignedBigInteger('legacy_id')->nullable()->unique();
            $t->string('legacy_url')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['section', 'slug']);
        });

        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('excerpt')->nullable();
            $t->longText('body')->nullable();
            $t->string('status', 20)->default('draft')->index();
            $t->string('seo_title')->nullable();
            $t->string('seo_description', 320)->nullable();
            $t->timestamp('published_at')->nullable()->index();
            $t->unsignedBigInteger('legacy_id')->nullable()->unique();
            $t->string('legacy_url')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('post_tag', function (Blueprint $t) {
            $t->foreignId('post_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $t->primary(['post_id', 'tag_id']);
        });

        Schema::create('staff_groups', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('kind', 30)->default('prefects')->index();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->unsignedBigInteger('legacy_id')->nullable()->unique();
            $t->timestamps();
        });

        Schema::create('staff_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('staff_group_id')->constrained()->cascadeOnDelete();
            $t->string('name')->nullable(); // most source records are photo-only
            $t->string('role')->nullable();
            $t->text('bio')->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_published')->default(true)->index();
            $t->unsignedBigInteger('legacy_id')->nullable();
            $t->timestamps();
            $t->unique(['legacy_id', 'staff_group_id']);
        });

        Schema::create('menu_items', function (Blueprint $t) {
            $t->id();
            $t->string('menu', 30)->default('main')->index();
            $t->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $t->string('label');
            $t->string('url')->nullable();
            $t->foreignId('page_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_visible')->default(true);
            $t->unsignedBigInteger('legacy_id')->nullable()->unique();
            $t->timestamps();
        });

        Schema::create('downloads', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('category', 40)->nullable();
            $t->boolean('is_public')->default(true);
            $t->timestamps();
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
            $t->timestamps();
        });

        Schema::create('redirects', function (Blueprint $t) {
            $t->id();
            $t->string('from_path', 500)->unique();
            $t->string('to_path', 500);
            $t->unsignedSmallInteger('status_code')->default(301);
            $t->unsignedInteger('hits')->default(0);
            $t->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('email', 190);
            $t->string('phone', 40)->nullable();
            $t->string('subject', 190)->nullable();
            $t->text('message');
            $t->string('ip_hash', 64)->nullable();
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
            $t->index('created_at');
        });

        Schema::create('import_maps', function (Blueprint $t) {
            $t->id();
            $t->string('wp_type', 40);
            $t->unsignedBigInteger('wp_id');
            $t->string('model');
            $t->unsignedBigInteger('model_id');
            $t->char('checksum', 40)->nullable();
            $t->timestamp('imported_at')->nullable();
            $t->unique(['wp_type', 'wp_id']);
        });
    }

    public function down(): void
    {
        foreach (['import_maps', 'contact_messages', 'redirects', 'settings', 'downloads', 'menu_items', 'staff_members', 'staff_groups', 'post_tag', 'posts', 'pages', 'tags', 'categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
