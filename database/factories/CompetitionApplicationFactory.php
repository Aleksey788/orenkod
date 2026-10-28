<?php

namespace Database\Factories;

use App\Models\CompetitionApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CompetitionApplication> */
class CompetitionApplicationFactory extends Factory
{
    protected $model = CompetitionApplication::class;

    public function definition(): array
    {
        return ['competition_id' => CompetitionFactory::new(), 'athlete_id' => AthleteFactory::new(), 'submitted_by_user_id' => UserFactory::new(), 'status' => 'approved'];
    }
}
