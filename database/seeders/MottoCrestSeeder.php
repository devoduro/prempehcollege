<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Turns the old "The Crest" page into "Motto and Crest". The old page repeated the Shield paragraph under "The Stool"
 * (a copy mistake), so the stool text is replaced and a motto section is added. The motto's English meaning is the literal
 * Twi (suban = character, nimdeɛ = knowledge). The motto's origin and any earlier motto are reported differently by public
 * sources, so they are not stated. Runs once: it only acts while the old duplicated text is still there.
 */
class MottoCrestSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::where('section', 'about')->where('slug', 'the-crest')->first();
        if (! $page || ! str_contains((string) $page->getRawOriginal('body'), '<h2>THE STOOL</h2>')) {
            return;
        }

        $figure = '';
        if (preg_match('#<figure>.*?</figure>#s', (string) $page->getRawOriginal('body'), $m)) {
            $figure = $m[0];
        }

        $page->forceFill([
            'title' => 'Motto and Crest',
            'body' => $figure
                .'<h2>Our motto</h2>'
                .'<p>The College motto, written at the bottom of the shield, is <strong>&ldquo;Suban Ne Nimdeɛ&rdquo;</strong>, which means <strong>Character and Knowledge</strong>. It says what Prempeh College is for: to form young men of good character as well as sound learning.</p>'
                .'<hr><h2>Elements of the crest and their interpretation</h2>'
                .'<p>The School\'s Crest is composed of four main elements: a green and yellow shield; an indigenous Akan male stool in yellow colour on a green background; a green cross; and the School\'s motto, &ldquo;Suban Ne Nimdeɛ&rdquo;, at the bottom of the shield.</p>'
                .'<h2>The shield</h2>'
                .'<p>The shield is used to symbolize defense and security. These are attributes of the Ashantis, that has kept the Kingdom intact over the last three centuries.</p>'
                .'<h2>The stool</h2>'
                .'<p>The Akan stool, in yellow on a green background, roots the College in Asante tradition. The College was founded in 1949 with the authority of Asanteman.</p>'
                .'<h2>The cross</h2>'
                .'<p>The cross is a symbol of the Christian faith and represents the role of the Presbyterian and Methodist Churches in the moral and character training of students. The College has indeed has a feel of Presbyterian and Methodist Training, and has exemplified the role of the two churches in educational development.</p>',
        ])->save();

        MenuItem::where('menu', 'main')->where('url', 'like', '%/about/the-crest')->update(['label' => 'Motto and Crest']);
    }
}
