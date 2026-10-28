<?php

namespace Database\Factories;

use App\Models\RankApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RankApplication> */
class RankApplicationFactory extends Factory
{
    protected $model = RankApplication::class;

    public function definition(): array
    {
        return ['athlete_id' => AthleteFactory::new(), 'sport_rank_id' => SportRankFactory::new(), 'discipline_id' => DisciplineFactory::new(), 'competition_result_id' => CompetitionResultFactory::new(), 'submitted_by_user_id' => UserFactory::new(), 'status' => 'approved'];
    }
}
