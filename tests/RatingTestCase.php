<?php

namespace Tests;

use App\Models\AgeGroup;
use App\Models\Athlete;
use App\Models\CompetitionResult;
use App\Models\Discipline;
use App\Models\RatingSeason;
use App\Models\RegionalTeam;
use App\Models\Role;
use App\Models\User;
use App\Services\Rating\RatingCategory;
use Carbon\CarbonImmutable;
use Database\Factories\AgeGroupFactory;
use Database\Factories\AthleteFactory;
use Database\Factories\CompetitionApplicationFactory;
use Database\Factories\CompetitionFactory;
use Database\Factories\CompetitionResultFactory;
use Database\Factories\DisciplineFactory;
use Database\Factories\RatingSeasonFactory;
use Database\Seeders\RatingRuleSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

abstract class RatingTestCase extends TestCase
{
    private ?string $postgresDatabase = null;

    protected User $admin;

    protected RatingSeason $season;

    protected Discipline $discipline;

    protected AgeGroup $ageGroup;

    protected function setUp(): void
    {
        parent::setUp();
        if (! $this->app->environment('testing')) {
            throw new RuntimeException('An isolated testing environment is required.');
        }
        $this->travelTo(now()->setDate(2026, 10, 28)->setTime(12, 0));
        CarbonImmutable::setTestNow('2026-10-28 12:00:00');
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'session.driver' => 'array', 'cache.default' => 'array']);
        DB::purge('sqlite');
        if (getenv('RATING_TEST_POSTGRES') === '1') {
            $connection = config('database.connections.pgsql');
            $connection['url'] = null;
            $connection['database'] = 'postgres';
            config(['database.connections.rating_test_admin' => $connection]);
            $this->postgresDatabase = 'orenkod_rating_test_'.bin2hex(random_bytes(8));
            DB::connection('rating_test_admin')->statement('CREATE DATABASE "'.$this->postgresDatabase.'"');
            $connection['database'] = $this->postgresDatabase;
            config(['database.default' => 'pgsql', 'database.connections.pgsql' => $connection]);
            DB::purge('pgsql');
        }
        $database = DB::connection()->getDatabaseName();
        if (! ((DB::connection()->getDriverName() === 'sqlite' && $database === ':memory:')
            || (DB::connection()->getDriverName() === 'pgsql' && $database === $this->postgresDatabase
                && preg_match('/^orenkod_rating_test_[a-f0-9]{16}$/', $database)))) {
            throw new RuntimeException('Only an isolated test database is allowed by this suite.');
        }
        $migrations = [
            '0001_01_01_000000_create_users_table.php',
            '2026_10_27_125254_create_organizations_table.php', '2026_10_27_125303_create_disciplines_table.php',
            '2026_10_27_125314_create_athletes_table.php', '2026_10_27_153957_create_competition_types_table.php',
            '2026_10_27_153958_create_age_groups_table.php', '2026_10_27_153959_create_competitions_table.php',
            '2026_10_27_154000_create_competition_applications_table.php', '2026_10_27_154003_create_competition_results_table.php',
            '2026_10_27_155208_create_sport_ranks_table.php', '2026_10_27_155210_create_rank_applications_table.php',
            '2026_10_27_155212_create_athlete_ranks_table.php', '2026_10_27_161000_create_roles_table.php',
            '2026_10_27_161001_create_role_user_table.php', '2026_10_27_162510_create_rating_seasons_table.php',
            '2026_10_27_162511_create_rating_rules_table.php', '2026_10_27_162512_create_athlete_restrictions_table.php',
            '2026_10_27_162513_create_regional_teams_table.php', '2026_10_27_162515_create_regional_team_members_table.php',
            '2026_10_27_165739_create_audit_logs_table.php', '2026_10_28_104834_add_results_publication_to_competitions_table.php',
            '2026_10_28_111207_extend_rating_and_regional_team_management.php',
        ];
        foreach ($migrations as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        $this->seed(RoleSeeder::class);
        Model::preventLazyLoading();
        Storage::fake('team_documents');
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach(Role::where('name', 'admin')->firstOrFail());
        $this->actingAs($this->admin)->withSession(['active_role' => 'admin']);
        $this->season = RatingSeasonFactory::new()->create();
        RatingRuleSeeder::seedSeason($this->season);
        $this->discipline = DisciplineFactory::new()->create(['name' => 'Программирование алгоритмическое']);
        $this->ageGroup = AgeGroupFactory::new()->create(['name' => 'Юниоры']);
    }

    protected function tearDown(): void
    {
        Model::preventLazyLoading(false);
        CarbonImmutable::setTestNow();
        $this->travelBack();
        if ($this->postgresDatabase !== null && preg_match('/^orenkod_rating_test_[a-f0-9]{16}$/', $this->postgresDatabase)) {
            DB::purge('pgsql');
            DB::connection('rating_test_admin')->statement('DROP DATABASE "'.$this->postgresDatabase.'"');
            DB::purge('rating_test_admin');
        }
        parent::tearDown();
    }

    protected function athlete(): Athlete
    {
        return AthleteFactory::new()->create();
    }

    /** @param array<string, mixed> $competitionData */
    protected function competitionResult(Athlete $athlete, int $place = 1, string $category = 'regional', string $status = 'finished', bool $published = true, array $competitionData = []): CompetitionResult
    {
        $competition = CompetitionFactory::new()->create([
            'discipline_id' => $this->discipline->id, 'age_group_id' => $this->ageGroup->id,
            'rating_category' => $category, 'level' => RatingCategory::LEVELS[$category] ?? 'regional',
            ...$competitionData,
        ]);
        if ($published) {
            $competition->forceFill(['results_published_at' => now()])->save();
        }
        $application = CompetitionApplicationFactory::new()->create([
            'competition_id' => $competition->id, 'athlete_id' => $athlete->id, 'submitted_by_user_id' => $athlete->user_id,
        ]);

        return CompetitionResultFactory::new()->create([
            'competition_application_id' => $application->id, 'place' => $status === 'finished' ? $place : null, 'status' => $status,
        ]);
    }

    protected function ratingsUrl(): string
    {
        return '/ratings/'.$this->season->id.'/'.$this->discipline->id;
    }

    /** @return array<string, mixed> */
    protected function teamData(): array
    {
        return ['name' => 'Сборная Оренбургской области', 'rating_season_id' => $this->season->id,
            'discipline_id' => $this->discipline->id, 'age_group_id' => $this->ageGroup->id, 'min_sport_rank_id' => null];
    }

    protected function team(): RegionalTeam
    {
        $response = $this->postJson('/admin/regional-teams', $this->teamData())->assertSuccessful();

        return RegionalTeam::findOrFail($response->json('data.id'));
    }
}
