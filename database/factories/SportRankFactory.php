<?php

namespace Database\Factories;

use App\Models\SportRank;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SportRank> */
class SportRankFactory extends Factory
{
    protected $model = SportRank::class;

    public function definition(): array
    {
        return ['name' => fake()->unique()->words(2, true), 'code' => fake()->unique()->bothify('R-####'), 'sort_order' => 10];
    }
}
