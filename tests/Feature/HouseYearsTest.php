<?php

use App\Models\House;
use App\Models\HouseYear;
use Illuminate\Database\QueryException;

beforeEach(function () {
    $this->withoutVite();
});

it('shows about, history, current leadership and a year-by-year record for a house', function () {
    $house = House::create(['name' => 'Aggrey House', 'slug' => 'aggrey', 'colour' => '#b91c1c', 'about' => '<p>A proud house.</p>', 'history' => '<p>Founded long ago.</p>']);
    HouseYear::create(['house_id' => $house->id, 'academic_year' => '2024/2025', 'housemaster' => 'Mr Old', 'house_prefect' => 'Kofi Old']);
    HouseYear::create(['house_id' => $house->id, 'academic_year' => '2025/2026', 'housemaster' => 'Mr Mensah', 'house_prefect' => 'Ama Boateng', 'achievements' => "Won athletics\nWon quiz"]);

    $this->get('/houses/aggrey')->assertOk()
        ->assertSee('A proud house.')->assertSee('Founded long ago.')
        ->assertSeeInOrder(['Leadership in 2025/2026', 'Mr Mensah', 'Ama Boateng'])
        ->assertSeeInOrder(['year by year', '2025/2026', 'Won athletics', 'Won quiz', '2024/2025', 'Mr Old']);
});

it('keeps one record per house per school year', function () {
    $house = House::create(['name' => 'Aggrey House', 'slug' => 'aggrey']);
    HouseYear::create(['house_id' => $house->id, 'academic_year' => '2025/2026']);

    expect(fn () => HouseYear::create(['house_id' => $house->id, 'academic_year' => '2025/2026']))->toThrow(QueryException::class);
});
