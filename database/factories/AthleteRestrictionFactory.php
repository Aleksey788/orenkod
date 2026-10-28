<?php

namespace Database\Factories;

use App\Models\AthleteRestriction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AthleteRestriction> */
class AthleteRestrictionFactory extends Factory
{
    protected $model = AthleteRestriction::class;

    public function definition(): array
    {
        return ['athlete_id' => AthleteFactory::new(), 'type' => 'disqualification', 'reason' => 'Тестовое решение', 'starts_on' => now()->subDay()->toDateString(), 'ends_on' => now()->addDay()->toDateString()];
    }
}
