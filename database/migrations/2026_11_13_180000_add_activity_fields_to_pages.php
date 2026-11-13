<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            // For club, team and activity pages (sports, cadet, olympiads): a stat strip, a timeline and related news.
            $t->json('facts')->nullable()->after('seo_description');       // [{label, value}]
            $t->json('highlights')->nullable()->after('facts');            // [{year, title, text}]
            $t->string('news_category', 190)->nullable()->after('highlights'); // comma-separated category slugs
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $t) {
            $t->dropColumn(['facts', 'highlights', 'news_category']);
        });
    }
};
