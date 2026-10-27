<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin' => 'Администратор федерации',
            'athlete' => 'Спортсмен',
            'coach' => 'Тренер',
            'judge' => 'Судья',
        ];

        foreach ($roles as $name => $description) {
            Role::updateOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }
    }
}