<?php

namespace Database\Factories;

use App\Models\Athlete;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Athlete> */
class AthleteFactory extends Factory
{
    protected $model = Athlete::class;

    public function definition(): array
    {
        return ['user_id' => UserFactory::new(), 'organization_id' => OrganizationFactory::new(), 'birth_date' => '2008-01-01', 'municipality' => 'Оренбург', 'start_year' => 2020];
    }
}
