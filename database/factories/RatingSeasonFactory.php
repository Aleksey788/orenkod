<?php

namespace Database\Factories;

use App\Models\RatingSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RatingSeason> */
class RatingSeasonFactory extends Factory
{
    protected $model = RatingSeason::class;

    public function definition(): array
    {
        return ['name' => 'Сезон '.now()->year, 'year' => now()->year, 'starts_on' => now()->startOfYear()->toDateString(), 'ends_on' => now()->endOfYear()->toDateString(), 'status' => 'active', 'is_active' => true, 'stability_coefficient' => '1.20', 'min_podium_results' => 2];
    }
}
