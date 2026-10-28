<?php

namespace Database\Factories;

use App\Models\AgeGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AgeGroup> */
class AgeGroupFactory extends Factory
{
    protected $model = AgeGroup::class;

    public function definition(): array
    {
        return ['name' => fake()->unique()->words(2, true), 'min_age' => 14, 'max_age' => 20];
    }
}
