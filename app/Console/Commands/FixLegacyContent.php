<?php

namespace App\Console\Commands;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Cleans problems found in the migrated WordPress content. Safe to run repeatedly.
 *  1. Pages that still carry another organisation's template text are set back to draft.
 *  2. The About overview is rebuilt from text that already existed on the old Prempeh site.
 *  3. Identical images repeated on one page are removed (the body reference and the stored file).
 */
class FixLegacyContent extends Command
{
    protected $signature = 'content:fix-legacy {--dry-run : Show what would change without saving}';

    protected $description = 'Unpublish template/demo pages, rebuild the About overview and remove duplicate images.';

    /** Text that only appears in the purchased theme\'s sample content (a college-consulting business). */
    private const DEMO_MARKERS = ['YAF Ghana', 'Young Achievers Foundation', 'Wesleyan University', 'Quayson', 'Comprehensive Narrative Strategy', 'Essential Package', 'Premier Package'];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $this->unpublishDemoPages($dry);
        $this->rebuildAbout($dry);
        $this->dedupeImages($dry);

        return self::SUCCESS;
    }

    private function unpublishDemoPages(bool $dry): void
    {
        foreach (Page::withTrashed()->where('status', ContentStatus::Published->value)->get() as $page) {
            $text = strip_tags((string) $page->getRawOriginal('body'));
            $hit = collect(self::DEMO_MARKERS)->first(fn ($m) => stripos($text, $m) !== false);
            if (! $hit) {
                continue;
            }
            $this->warn("Unpublishing \"{$page->title}\" (contains \"$hit\", which is template text, not Prempeh College)");
            if (! $dry) {
                $page->forceFill(['status' => ContentStatus::Draft])->save();
                // Photos of the template's people must not be reused anywhere on the site.
                $page->getMedia('content')->each->delete();
                $page->forceFill(['body' => null])->save();
            }
        }
    }

    /** Only uses statements already published on the old home, history and news pages. */
    private function rebuildAbout(bool $dry): void
    {
        $about = Page::where('section', 'about')->where('slug', 'overview')->first();
        if (! $about || filled($about->getRawOriginal('body'))) {
            return;
        }
        $this->info('Writing the About overview from existing site text');
        if ($dry) {
            return;
        }

        $about->forceFill([
            'status' => ContentStatus::Published,
            'body' => '<p>Prempeh College has a rich and storied history of providing quality education in Ghana. Over the years, our school has evolved and grown, adapting to the changing needs of students and embracing innovative teaching methods. Today, we proudly continue the legacy of academic excellence, nurturing young minds and preparing them for a successful future.</p>'
                .'<p>Since our founding in 1940, the College has redrawn and redefined the boundaries of intellectual and creative thought as a preeminent academic centre.</p>'
                .'<h2>Academic and creative achievement</h2>'
                .'<p>Prempeh College won the 2021 National Science and Maths Quiz (NSMQ), bringing its championship tally to five. In the field of robotics, the College is the first secondary institution in Ghana and Africa to win the World Robofest, in 2016, and it won the Toyota Innovation Award that year.</p>'
                .'<h2>Explore the College</h2>'
                .'<ul><li><a href="/about/history">Our history</a></li><li><a href="/about/mission-and-values">Mission and values</a></li><li><a href="/about/the-crest">The crest</a></li>'
                .'<li><a href="/about/current-headmasters">Current headmasters</a></li><li><a href="/about/past-headmasters">Past headmasters</a></li><li><a href="/houses">Our houses</a></li></ul>',
        ])->save();
    }

    private function dedupeImages(bool $dry): void
    {
        $removed = 0;
        foreach ([Page::withTrashed()->get(), Post::withTrashed()->get()] as $models) {
            foreach ($models as $model) {
                $seen = [];
                /** @var Media $media */
                foreach ($model->getMedia('content') as $media) {
                    $path = $media->getPath();
                    $hash = is_file($path) ? sha1_file($path) : null;
                    if ($hash === null) {
                        continue;
                    }
                    if (! isset($seen[$hash])) {
                        $seen[$hash] = $media;

                        continue;
                    }
                    $this->line("Duplicate image on \"{$model->title}\": {$media->file_name}");
                    $removed++;
                    if ($dry) {
                        continue;
                    }
                    // Drop the <figure> (or bare <img>) pointing at the duplicate, then the stored file.
                    $body = (string) $model->getRawOriginal('body');
                    $id = preg_quote((string) $media->getKey(), '~');
                    $body = preg_replace('~\s*<figure>\s*<img[^>]*src="/storage/'.$id.'/[^"]*"[^>]*>\s*</figure>~i', '', $body) ?? $body;
                    $body = preg_replace('~<img[^>]*src="/storage/'.$id.'/[^"]*"[^>]*>~i', '', $body) ?? $body;
                    $model->forceFill(['body' => $body])->save();
                    $media->delete();
                }
            }
        }
        $this->info("Duplicate images removed: $removed".($dry ? ' (dry run)' : ''));
    }
}
