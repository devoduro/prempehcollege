<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Post;
use App\Services\Migration\ImportContext;
use App\Services\Migration\Importers\MenuImporter;
use App\Services\Migration\Importers\PageImporter;
use App\Services\Migration\Importers\PostImporter;
use App\Services\Migration\Importers\RedirectImporter;
use App\Services\Migration\Importers\StaffImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MigrateWp extends Command
{
    protected $signature = 'migrate:wp
        {--type=all : all|pages|posts|staff|menus|redirects}
        {--dry-run : Run everything inside a transaction and roll back (no files or rows are kept)}
        {--fresh : Empty the imported content tables first}';

    protected $description = 'Import the WordPress database (read-only wp_source connection) into the new site.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $type = (string) $this->option('type');
        $ctx = new ImportContext($dry);

        // Images are converted to WebP during import; large originals need headroom.
        ini_set('memory_limit', '1G');
        config(['media-library.queue_conversions_by_default' => false]);

        if ($this->option('fresh') && ! $dry) {
            $this->truncateContent();
        }

        $steps = [
            'pages' => fn () => (new PageImporter($ctx))->run(),
            'posts' => fn () => (new PostImporter($ctx))->run(),
            'staff' => fn () => (new StaffImporter($ctx))->run(),
            'menus' => fn () => (new MenuImporter($ctx))->run(),
            'redirects' => fn () => (new RedirectImporter($ctx))->run(),
        ];

        DB::beginTransaction();
        try {
            foreach ($steps as $name => $run) {
                if ($type === 'all' || $type === $name) {
                    $this->components->task("Importing $name", $run);
                }
            }
            $dry ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname((string) config('migration.report_path')));
        File::put((string) config('migration.report_path'), $ctx->report->toMarkdown($dry));

        $rows = [];
        foreach ($ctx->report->counts() as $t => $c) {
            $rows[] = [$t, $c['source'], $c['imported'], $c['skipped'], $c['failed']];
        }
        $this->table(['Type', 'Source', 'Imported', 'Skipped', 'Failed'], $rows);
        $this->info('Report: '.config('migration.report_path'));

        return self::SUCCESS;
    }

    private function truncateContent(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (['import_maps', 'redirects', 'menu_items', 'staff_members', 'staff_groups', 'post_tag', 'posts', 'pages', 'tags', 'categories', 'media'] as $t) {
            DB::table($t)->truncate();
        }
        DB::table('settings')->where('key', 'like', 'home.%')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->warn('Content tables emptied. Stored files in storage/app/public are left in place; remove them manually if needed.');
    }

    public static function counts(): array
    {
        return ['pages' => Page::count(), 'posts' => Post::count()];
    }
}
