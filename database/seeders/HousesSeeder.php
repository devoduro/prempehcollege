<?php

namespace Database\Seeders;

use App\Models\House;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The eleven boarding houses of Prempeh College (Arthur Clark and Matthew Opoku Prempeh were
 * formerly House 10 and House 11, renamed in June 2023). Safe to re-run: houses are matched by slug,
 * and anything edited in the admin (colour, motto, history, ...) is left untouched.
 * Add colours, namesakes and housemasters under Filament > Houses.
 */
class HousesSeeder extends Seeder
{
    public function run(): void
    {
        $houses = ['Ramseyer', 'Freeman', 'Pearson', 'Aggrey', 'Guggisberg', 'Osei Tutu', 'Serwaa', 'Opoku Ware', 'Butler', 'Arthur Clark', 'Matthew Opoku Prempeh'];

        foreach ($houses as $i => $name) {
            // withTrashed so a house deleted in the admin is restored rather than duplicated.
            $house = House::withTrashed()->firstOrNew(['slug' => Str::slug($name)]);

            if (! $house->exists) {
                $house->fill(['name' => $name, 'sort_order' => $i + 1, 'is_active' => true]);
                $house->save();
            }
        }
    }
}
