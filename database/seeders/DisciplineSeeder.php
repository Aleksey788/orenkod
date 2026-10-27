<?php

namespace Database\Seeders;

use App\Models\Discipline;
use Illuminate\Database\Seeder;

class DisciplineSeeder extends Seeder
{
    public function run(): void
    {
        $disciplines = [
            'Программирование алгоритмическое',
            'Программирование продуктовое',
            'Программирование систем информационной безопасности',
            'Программирование систем искусственного интеллекта',
            'Программирование робототехники',
            'Программирование беспилотных авиационных систем',
        ];

        foreach ($disciplines as $name) {
            Discipline::firstOrCreate([
                'name' => $name
            ]);
        }
    }
}