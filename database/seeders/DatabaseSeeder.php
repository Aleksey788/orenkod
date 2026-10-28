<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Discipline;
use App\Models\Organization;
use App\Models\Athlete;
use App\Models\Coach;
use App\Models\Judge;
use App\Models\CompetitionType;
use App\Models\AgeGroup;
use App\Models\SportRank;
use App\Models\JudgeCategory;
use App\Models\Competition;
use App\Models\RatingSeason;
use App\Models\CompetitionApplication;
use App\Models\CompetitionResult;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Роли пользователей
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['description' => 'Администратор Федерации']);
        $athleteRole = Role::firstOrCreate(['name' => 'athlete'], ['description' => 'Спортсмен']);
        $coachRole = Role::firstOrCreate(['name' => 'coach'], ['description' => 'Тренер']);
        $judgeRole = Role::firstOrCreate(['name' => 'judge'], ['description' => 'Судья']);

        // 2. Спортивные дисциплины
        $discipline = Discipline::firstOrCreate(['name' => 'Спортивная борьба']);

        // 3. Спортивные организации
        $org = Organization::firstOrCreate([
            'name' => 'СШОР №1 г. Оренбурга',
            'type' => 'Спортивная школа',
            'municipality' => 'г. Оренбург',
            'address' => 'ул. Советская, 1'
        ]);

        // 4. Справочники типов и возрастных групп
        $compType = CompetitionType::firstOrCreate(['name' => 'Чемпионат области'], ['description' => 'Региональные соревнования']);
        $ageGroup = AgeGroup::firstOrCreate(['name' => 'Юноши 14-16 лет'], ['min_age' => 14, 'max_age' => 16]);

        // 5. Справочники разрядов и категорий
        SportRank::firstOrCreate(['code' => 'KMS'], ['name' => 'Кандидат в мастера спорта', 'sort_order' => 1]);
        JudgeCategory::firstOrCreate(['code' => '1_CATEGORY'], ['name' => 'Первая судейская категория', 'sort_order' => 1]);

        // 6. Сезон рейтинга
        RatingSeason::firstOrCreate([
            'year' => 2026
        ], [
            'name' => 'Сезон 2026',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'stability_coefficient' => 1.20,
            'min_podium_results' => 2,
            'is_active' => true
        ]);

        // 7. Создаем пользователей с разными ролями
        $adminUser = User::firstOrCreate(['email' => 'admin@orenkod.ru'], [
            'first_name' => 'Иван',
            'last_name' => 'Администраторов',
            'middle_name' => 'Петрович',
            'phone' => '+79001112233',
            'password' => Hash::make('password')
        ]);
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        $coachUser = User::firstOrCreate(['email' => 'coach@orenkod.ru'], [
            'first_name' => 'Алексей',
            'last_name' => 'Тренеров',
            'middle_name' => 'Иванович',
            'phone' => '+79002223344',
            'password' => Hash::make('password')
        ]);
        $coachUser->roles()->syncWithoutDetaching([$coachRole->id]);
        $coach = Coach::firstOrCreate(['user_id' => $coachUser->id], ['organization_id' => $org->id]);

        $judgeUser = User::firstOrCreate(['email' => 'judge@orenkod.ru'], [
            'first_name' => 'Сергей',
            'last_name' => 'Судьев',
            'middle_name' => 'Степанович',
            'phone' => '+79003334455',
            'password' => Hash::make('password')
        ]);
        $judgeUser->roles()->syncWithoutDetaching([$judgeRole->id]);
        Judge::firstOrCreate(['user_id' => $judgeUser->id]);

        $athleteUser = User::firstOrCreate(['email' => 'athlete@orenkod.ru'], [
            'first_name' => 'Максим',
            'last_name' => 'Спортсменов',
            'middle_name' => 'Дмитриевич',
            'phone' => '+79004445566',
            'password' => Hash::make('password')
        ]);
        $athleteUser->roles()->syncWithoutDetaching([$athleteRole->id]);
        
        $athlete = Athlete::firstOrCreate(['user_id' => $athleteUser->id], [
            'organization_id' => $org->id,
            'birth_date' => '2010-05-15',
            'municipality' => 'г. Оренбург',
            'start_year' => 2020,
            'training_stage' => 'УТГ-2'
        ]);
        $athlete->disciplines()->syncWithoutDetaching([$discipline->id]);

        // 8. Тестовое соревнование
        $competition = Competition::firstOrCreate(['title' => 'Первенство Оренбургской области по спортивной борьбе'], [
            'discipline_id' => $discipline->id,
            'competition_type_id' => $compType->id,
            'age_group_id' => $ageGroup->id,
            'level' => 'regional',
            'format' => 'очно',
            'location' => 'СК "Оренбуржье"',
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(12),
            'registration_starts_at' => now()->subDays(5),
            'registration_ends_at' => now()->addDays(5),
            'status' => 'published',
            'calendar_plan' => 'main',
            'calendar_plan_number' => '12345',
            'created_by_user_id' => $adminUser->id
        ]);

        // 9. Заявка спортсмена на соревнование
        $app = CompetitionApplication::firstOrCreate([
            'competition_id' => $competition->id,
            'athlete_id' => $athlete->id
        ], [
            'submitted_by_user_id' => $coachUser->id,
            'status' => 'approved',
            'reviewed_by_user_id' => $adminUser->id,
            'reviewed_at' => now()
        ]);

        // 10. Результат соревнования
        CompetitionResult::firstOrCreate([
            'competition_application_id' => $app->id
        ], [
            'place' => 1,
            'score' => 100.00,
            'status' => 'ranked',
            'notes' => 'Победитель соревнований',
            'entered_by_user_id' => $judgeUser->id
        ]);
    }
}