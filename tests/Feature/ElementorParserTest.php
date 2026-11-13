<?php

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Services\Migration\ElementorParser;
use App\Services\Migration\HtmlCleaner;
use App\Services\Migration\MediaResolver;
use App\Services\Migration\MigrationReport;

function widget(string $type, array $settings): array
{
    return ['elType' => 'widget', 'widgetType' => $type, 'settings' => $settings, 'elements' => []];
}

function wrap(array ...$widgets): array
{
    return [['elType' => 'container', 'settings' => [], 'elements' => [
        ['elType' => 'container', 'settings' => [], 'elements' => $widgets],
    ]]];
}

it('flattens layout wrappers and maps headings, text and lists', function () {
    $html = (new ElementorParser(new MigrationReport))->toHtml(wrap(
        widget('heading', ['title' => 'The Beginning', 'header_size' => 'h1']),
        widget('text-editor', ['editor' => "First para\n\nSecond para"]),
        widget('icon-list', ['icon_list' => [['text' => 'Business'], ['text' => 'General Arts']]]),
    ), 1);

    expect($html)
        ->toContain('<h2>The Beginning</h2>')   // page template owns the only h1
        ->toContain('<p>First para</p>')
        ->toContain('<p>Second para</p>')
        ->toContain('<ul><li>Business</li><li>General Arts</li></ul>')
        ->not->toContain('container');
});

it('turns YouTube widgets into privacy-enhanced embeds', function () {
    $html = (new ElementorParser(new MigrationReport))->toHtml(wrap(
        widget('video', ['youtube_url' => 'https://www.youtube.com/watch?v=zmma5Ag0dE8']),
    ), 1);

    expect($html)->toContain('https://www.youtube-nocookie.com/embed/zmma5Ag0dE8');
});

it('reports unrecognised widgets instead of dropping them silently', function () {
    $report = new MigrationReport;
    (new ElementorParser($report))->toHtml(wrap(widget('mystery_widget', [])), 42);

    expect(json_encode($report->notes()))->toContain('mystery_widget on page 42');
});

it('ignores form and feed widgets without reporting them', function () {
    $report = new MigrationReport;
    $html = (new ElementorParser($report))->toHtml(wrap(widget('wpforms', ['form_id' => '1']), widget('posts', [])), 1);

    expect($html)->toBe('')->and($report->notes())->toBe([]);
});

it('records buttons that have no real link target', function () {
    $report = new MigrationReport;
    $html = (new ElementorParser($report))->toHtml(wrap(widget('button', ['text' => ' ONLINE ADMISSION', 'link' => ['url' => '#']])), 1);

    expect($html)->toBe('')->and(json_encode($report->notes()))->toContain('ONLINE ADMISSION');
});

it('cleans Gutenberg comments, shortcodes and empty paragraphs', function () {
    $report = new MigrationReport;
    $parser = new ElementorParser($report);
    $clean = (new HtmlCleaner($report, $parser))->clean(
        '<!-- wp:paragraph --><p>Hello</p><!-- /wp:paragraph --><p>&nbsp;</p>[gallery ids="1,2"]<p>https://youtu.be/zmma5Ag0dE8</p>',
        'test',
    );

    expect($clean)->toContain('<p>Hello</p>')
        ->not->toContain('wp:paragraph')->not->toContain('[gallery')->not->toContain('<p>&nbsp;</p>')
        ->toContain('youtube-nocookie.com/embed/zmma5Ag0dE8');
});

it('keeps safe embeds and details blocks but strips scripts and foreign iframes after purifying', function () {
    $page = Page::create([
        'title' => 'T', 'slug' => 't', 'status' => ContentStatus::Draft,
        'body' => '<details><summary>Q</summary><p>A</p></details>'
            .'<div class="pc-video"><iframe src="https://www.youtube-nocookie.com/embed/zmma5Ag0dE8" title="v"></iframe></div>'
            .'<iframe src="https://evil.example/x"></iframe><a href="javascript:alert(1)">x</a>',
    ]);
    $body = $page->fresh()->body;

    expect($body)->toContain('<details>')->toContain('youtube-nocookie.com/embed/zmma5Ag0dE8')
        ->not->toContain('evil.example')->not->toContain('javascript:');
});

it('resolves resized and -scaled upload URLs to the original local file', function () {
    $dir = sys_get_temp_dir().'/pc_media_'.uniqid();
    mkdir("$dir/2024/03", 0777, true);
    touch("$dir/2024/03/photo-scaled.jpg");

    $r = new MediaResolver($dir);
    $rel = $r->relative('https://prempehcollege.edu.gh/wp-content/uploads/2024/03/photo-768x1024.jpg');

    expect($rel)->toBe('2024/03/photo-768x1024.jpg')
        ->and($r->path($rel))->toEndWith('2024/03/photo-scaled.jpg')
        ->and($r->relative('https://example.com/other.jpg'))->toBeNull();
});

it('computes public paths for sections, root pages, home and news', function () {
    expect((new Page(['section' => 'about', 'slug' => 'history', 'template' => 'default']))->path)->toBe('/about/history')
        ->and((new Page(['section' => null, 'slug' => 'contact', 'template' => 'default']))->path)->toBe('/contact')
        ->and((new Page(['section' => null, 'slug' => 'home', 'template' => 'home']))->path)->toBe('/');
});
