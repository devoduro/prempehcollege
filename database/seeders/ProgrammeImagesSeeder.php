<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Programme;
use Illuminate\Database\Seeder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Adds STEM as a programme and gives every programme a cover image. Real Prempeh photos are used where they exist
 * (robotics, NSMQ, debate). The site has no photos for Business or Visual Arts, so a simple branded illustration is
 * generated for those two; replace them from the admin (Content > Programmes). Safe to run more than once.
 */
class ProgrammeImagesSeeder extends Seeder
{
    public function run(): void
    {
        $this->stem();

        $photos = [
            'stem' => fn () => Media::where('file_name', 'like', 'MG_1134%')->first(),
            'general-science' => fn () => Post::where('slug', 'like', 'prempeh-college-has-won-the-2021%')->first()?->getFirstMedia('featured'),
            'general-arts' => fn () => Media::where('file_name', 'like', '504376327%')->first(),
        ];

        foreach (Programme::all() as $programme) {
            if ($programme->getFirstMedia('cover')) {
                continue;
            }
            $source = isset($photos[$programme->slug]) ? ($photos[$programme->slug])() : null;
            if ($source && is_file($source->getPath())) {
                $programme->addMedia($source->getPath())->preservingOriginal()->toMediaCollection('cover');

                continue;
            }
            $file = $this->illustration($programme->slug);
            if ($file) {
                $programme->addMedia($file)->usingFileName($programme->slug.'-cover.png')->toMediaCollection('cover');
            }
        }
    }

    private function stem(): void
    {
        if (Programme::where('slug', 'stem')->exists()) {
            return;
        }
        Programme::create([
            'name' => 'STEM',
            'slug' => 'stem',
            'summary' => 'Science, technology, engineering and mathematics, with hands-on robotics and computing.',
            'core_subjects' => ['English Language', 'Core Mathematics', 'Integrated Science', 'Social Studies'],
            'sort_order' => 1,
            'is_active' => true,
        ]);
        // Keep the other programmes after STEM in display order.
        Programme::where('slug', '!=', 'stem')->where('sort_order', '>=', 1)->increment('sort_order');
    }

    /** A simple 1600x900 illustration in the school colours, for programmes that have no photograph. */
    private function illustration(string $slug): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }
        [$w, $h] = [1600, 900];
        $im = imagecreatetruecolor($w, $h);
        imagealphablending($im, true);
        imageantialias($im, true);

        // Dark green vertical gradient
        for ($y = 0; $y < $h; $y++) {
            $t = $y / $h;
            $c = imagecolorallocate($im, (int) (3 + 8 * $t), (int) (42 + 38 * $t), (int) (13 + 20 * $t));
            imageline($im, 0, $y, $w, $y, $c);
        }
        $gold = imagecolorallocate($im, 255, 197, 58);
        $goldSoft = imagecolorallocatealpha($im, 255, 197, 58, 70);
        $white = imagecolorallocatealpha($im, 255, 255, 255, 90);
        $green = imagecolorallocatealpha($im, 120, 200, 130, 80);

        if ($slug === 'business') {
            // Rising bars with a trend line and a coin
            foreach ([0, 1, 2, 3, 4] as $i) {
                $bh = 160 + $i * 95;
                $x = 430 + $i * 150;
                imagefilledrectangle($im, $x, 760 - $bh, $x + 100, 760, $i === 4 ? $gold : $goldSoft);
            }
            imagesetthickness($im, 10);
            imageline($im, 440, 560, 580, 470, $white);
            imageline($im, 580, 470, 740, 520, $white);
            imageline($im, 740, 520, 880, 380, $white);
            imageline($im, 880, 380, 1030, 300, $white);
            imagesetthickness($im, 1);
            imagefilledellipse($im, 1290, 250, 230, 230, $gold);
            imagefilledellipse($im, 1290, 250, 170, 170, imagecolorallocate($im, 5, 71, 22));
            imagefilledellipse($im, 1290, 250, 110, 110, $gold);
        } else {
            // Overlapping colour discs and brush strokes
            imagefilledellipse($im, 700, 420, 520, 520, $goldSoft);
            imagefilledellipse($im, 930, 420, 520, 520, $white);
            imagefilledellipse($im, 815, 600, 520, 520, $green);
            imagesetthickness($im, 24);
            imageline($im, 1180, 180, 1480, 640, $gold);
            imageline($im, 1240, 160, 1540, 620, $white);
            imagesetthickness($im, 1);
            imagefilledpolygon($im, [1160, 700, 1280, 520, 1400, 700], $goldSoft);
        }

        $path = sys_get_temp_dir().'/'.$slug.'-cover-'.uniqid().'.png';
        imagepng($im, $path);
        imagedestroy($im);

        return $path;
    }
}
