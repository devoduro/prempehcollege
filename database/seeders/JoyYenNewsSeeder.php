<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Draft success and award stories from MyJoyOnline and Yen.com.gh that are not already on the site. Each uses only facts in
 * the report headline or summary, with a credit line. Saved as DRAFTS with no date: check them against the original
 * articles, set the date, add photos and publish from the admin (News). Safe to run more than once.
 */
class JoyYenNewsSeeder extends Seeder
{
    public function run(): void
    {
        $cat = fn (string $slug, string $name) => Category::firstOrCreate(['slug' => $slug], ['name' => $name]);
        $sports = $cat('sports', 'Sports');
        $robotics = $cat('robotics', 'Robotics');
        $nsmq = $cat('nsmq', 'NSMQ');
        $honour = $cat('honour', 'Honour');

        $joy = '<p><em>Reported by MyJoyOnline.</em></p>';
        $yen = '<p><em>Reported by Yen.com.gh.</em></p>';

        $stories = [
            [$robotics, 'prempeh-college-wins-two-titles-at-2023-robofest-world-championship', 'Prempeh College wins two titles at the 2023 Robofest World Championship',
                'The robotics club clinched two world championship titles in the United States.',
                '<p>Prempeh College\'s robotics club won two titles at the 2023 Robofest World Championship in the United States, adding to the school\'s record as the first secondary school in Africa to win the World Robofest, in 2016.</p>'.$joy],
            [$sports, 'prempeh-college-wins-2024-ashanti-regional-schools-super-zonal', 'Prempeh College wins the 2024 Ashanti Regional Schools Super Zonal',
                'The boys\' team won the 2024 title with 142 points, on the strength of sprint and distance wins.',
                '<p>Prempeh College won the 2024 Ashanti Regional Schools Super Zonal athletics title in the boys\' category, scoring 142 points with wins in the sprints and distance races.</p>'.$yen],
            [$sports, 'ernest-acheampong-overall-best-athlete-2025-ashanti-super-zonals', 'Ernest Acheampong is Overall Best Athlete at the 2025 Ashanti Super Zonals',
                'Three gold medals, a silver and a bronze for the Prempeh College athlete.',
                '<p>At the 2025 Ashanti Region Super Zonals, where Prempeh College defended its title as the best boys\' team, Ernest Acheampong won three gold medals, one silver and one bronze. He was named Best Athlete in the track events and Overall Best Athlete in the boys\' category.</p>'.$joy],
            [$nsmq, 'prempeh-college-2017-nsmq-champions-rewarded', 'Prempeh College 2017 NSMQ champions are rewarded',
                'The 2017 title brought GH¢40,000 and packages from Tigo and GCB Bank, and a reward from the Asantehene.',
                '<p>Prempeh College\'s 2017 National Science and Maths Quiz triumph earned the school GH¢40,000 and packages from Tigo and GCB Bank. The Asantehene also rewarded the team and their teachers, and the boys made a triumphant entry into campus after the victory.</p>'.$joy],
            [$nsmq, 'prempeh-college-2021-nsmq-highest-scorer-prize-and-trophy-to-asantehene', '2021 NSMQ champions win the Highest Scorer prize and present the trophy to the Asantehene',
                'Prempeh College beat PRESEC-Legon and Keta SHTS and took home GH¢10,000 as the grand finale\'s highest scorer.',
                '<p>Prempeh College won the 2021 National Science and Maths Quiz with 53 points, ahead of PRESEC-Legon (49) and Keta Senior High Technical School (30). The team also won the AirtelTigo Highest Scorer award of GH¢10,000 for the grand finale, and later presented the trophy to the Asantehene. The President congratulated the school on its win.</p>'.$joy],
            [$robotics, 'prempeh-college-overall-champions-2025-national-robotics-championship', 'Prempeh College are overall champions at the 2025 National Robotics Championship',
                'The robotics team won the Senior Category.',
                '<p>Prempeh College was crowned overall winner of the 2025 National Robotics Championship in the Senior Category.</p>'.$yen],
            [$honour, 'prempeh-college-student-lewis-asare-medals-simoc-2026', 'Prempeh College student wins medals in all three categories at SIMOC 2026',
                'Lewis Asare took two bronzes and a gold at the Singapore International Math Olympiad Challenge.',
                '<p>Prempeh College student Lewis Asare won a medal in each of the three categories at the 2026 Singapore International Math Olympiad Challenge (SIMOC): a Bronze Medal in the Math Olympiad, a Bronze Medal in Master Mind and a Gold Medal in Math Warriors. The gold was reported as a first for a Ghanaian.</p>'.$yen],
            [$honour, 'prempeh-college-produces-15-medical-doctors-from-knust-2025-2026', 'Prempeh College produces 15 medical doctors from KNUST\'s 2025/2026 academic year',
                'Alumni make up 15 of the new medical doctors from KNUST.',
                '<p>Fifteen Prempeh College alumni are among the medical doctors produced by KNUST in the 2025/2026 academic year, a record that the report called historic for the school.</p>'.$yen],
            [$honour, 'prempeh-college-alumnus-picks-up-four-awards-at-ucc-medical-congregation', 'Prempeh College alumnus graduates from medical school and picks up four awards',
                'A Prempeh College old boy won four awards at the University of Cape Coast congregation.',
                '<p>A Prempeh College alumnus graduated from the University of Cape Coast medical school and picked up four awards at the congregation.</p>'.$yen],
            [$honour, 'prempeh-college-2015-nsmq-champion-overall-best-graduating-student-knust', '2015 NSMQ champion is overall best graduating student at KNUST',
                'A member of Prempeh College\'s 2015 NSMQ-winning team won the KNUST excellence award.',
                '<p>A member of Prempeh College\'s 2015 National Science and Maths Quiz-winning team was adjudged the overall best graduating student at the KNUST excellence awards.</p>'.$joy],
            [$honour, 'evans-oppong-2021-prempeh-nsmq-winner-gets-us-scholarship', 'Evans Oppong, 2021 NSMQ winner, gets a scholarship to study in the US',
                'A member of the 2021 NSMQ-winning team goes on to study in the United States.',
                '<p>Evans Oppong, a member of the Prempeh College team that won the 2021 National Science and Maths Quiz, has received a scholarship to study in the United States.</p>'.$yen],
        ];

        foreach ($stories as [$category, $slug, $title, $excerpt, $body]) {
            if (Post::withTrashed()->where('slug', $slug)->exists()) {
                continue;
            }
            Post::create(['category_id' => $category->id, 'title' => $title, 'slug' => $slug, 'excerpt' => $excerpt, 'body' => $body,
                'status' => ContentStatus::Draft, 'published_at' => null]);
        }
    }
}
