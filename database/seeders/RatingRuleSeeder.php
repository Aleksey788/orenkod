<?php

namespace Database\Seeders;

use App\Models\RatingSeason;
use Illuminate\Database\Seeder;

class RatingRuleSeeder extends Seeder
{
    public function run(): void
    {
        RatingSeason::query()->where('status', '!=', 'closed')->each(fn (RatingSeason $season) => self::seedSeason($season));
    }

    public static function seedSeason(RatingSeason $season): void
    {
        foreach ([
            'national_championship' => [100, 80, 60], 'national_youth' => [80, 60, 40],
            'interregional' => [60, 45, 30], 'regional' => [40, 30, 20],
        ] as $category => $points) {
            foreach ($points as $index => $value) {
                $season->rules()->firstOrCreate(['category' => $category, 'place' => $index + 1], ['points' => $value]);
            }
        }
    }
}
