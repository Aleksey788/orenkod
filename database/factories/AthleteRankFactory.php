<?php

namespace Database\Factories;

use App\Models\AthleteRank;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AthleteRank> */
class AthleteRankFactory extends Factory
{
    protected $model = AthleteRank::class;

    public function definition(): array
    {
        return ['athlete_id' => AthleteFactory::new(), 'sport_rank_id' => SportRankFactory::new(), 'discipline_id' => DisciplineFactory::new(), 'rank_application_id' => RankApplicationFactory::new(), 'awarded_at' => now()->subMonth()->toDateString(), 'expires_at' => null, 'order_number' => 'ТЕСТ-1', 'order_date' => now()->subMonth()->toDateString()];
    }
}
