<?php

namespace Database\Factories;

use App\Models\CompetitionResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CompetitionResult> */
class CompetitionResultFactory extends Factory
{
    protected $model = CompetitionResult::class;

    public function definition(): array
    {
        return ['competition_application_id' => CompetitionApplicationFactory::new(), 'place' => 1, 'score' => '95.50', 'status' => 'finished'];
    }
}
