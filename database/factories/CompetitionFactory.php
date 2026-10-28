<?php

namespace Database\Factories;

use App\Models\Competition;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Competition> */
class CompetitionFactory extends Factory
{
    protected $model = Competition::class;

    public function definition(): array
    {
        return ['title' => fake()->sentence(3), 'discipline_id' => DisciplineFactory::new(), 'competition_type_id' => CompetitionTypeFactory::new(), 'age_group_id' => AgeGroupFactory::new(), 'level' => 'regional', 'rating_category' => 'regional', 'format' => 'offline', 'location' => 'Оренбург', 'starts_at' => now()->subDays(5), 'ends_at' => now()->subDays(4), 'registration_starts_at' => now()->subDays(20), 'registration_ends_at' => now()->subDays(6), 'status' => 'published'];
    }
}
