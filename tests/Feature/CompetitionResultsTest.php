<?php

namespace Tests\Feature;

use App\Models\AgeGroup;
use App\Models\Athlete;
use App\Models\AuditLog;
use App\Models\Competition;
use App\Models\CompetitionApplication;
use App\Models\CompetitionType;
use App\Models\Discipline;
use App\Models\Judge;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Services\Competition\CompetitionProtocolService;
use App\Services\Competition\CompetitionResultService;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class CompetitionResultsTest extends TestCase
{
    private User $admin;

    private Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->app->environment('testing')) {
            throw new RuntimeException('This suite only runs in testing.');
        }
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);
        DB::purge('sqlite');
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        $migrations = [
            '0001_01_01_000000_create_users_table.php',
            '2026_10_27_125254_create_organizations_table.php',
            '2026_10_27_125303_create_disciplines_table.php',
            '2026_10_27_125314_create_athletes_table.php',
            '2026_10_27_153957_create_competition_types_table.php',
            '2026_10_27_153958_create_age_groups_table.php',
            '2026_10_27_153959_create_competitions_table.php',
            '2026_10_27_154000_create_competition_applications_table.php',
            '2026_10_27_154003_create_competition_results_table.php',
            '2026_10_27_154101_create_judges_table.php',
            '2026_10_27_161000_create_roles_table.php',
            '2026_10_27_161001_create_role_user_table.php',
            '2026_10_27_162509_create_judge_assignments_table.php',
            '2026_10_27_165739_create_audit_logs_table.php',
            '2026_10_28_104834_add_results_publication_to_competitions_table.php',
        ];
        foreach ($migrations as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        $this->seed(RoleSeeder::class);
        Storage::fake('protocols');
        Model::preventLazyLoading();
        $this->admin = $this->user('admin');
        $this->actingAs($this->admin)->withSession(['active_role' => 'admin']);
        $this->competition = Competition::create([
            'title' => 'Кубок ОренКод',
            'discipline_id' => Discipline::create(['name' => 'Алгоритмическое программирование'])->id,
            'competition_type_id' => CompetitionType::create(['name' => 'Личное первенство'])->id,
            'age_group_id' => AgeGroup::create(['name' => 'Юниоры', 'min_age' => 14, 'max_age' => 20])->id,
            'level' => 'regional', 'format' => 'offline', 'location' => 'Оренбург',
            'starts_at' => now()->subDays(2), 'ends_at' => now()->subDay(),
            'registration_starts_at' => now()->subDays(10), 'registration_ends_at' => now()->subDays(3),
            'status' => 'published', 'created_by_user_id' => $this->admin->id,
        ]);
    }

    protected function tearDown(): void
    {
        Model::preventLazyLoading(false);
        parent::tearDown();
    }

    public function test_admin_saves_and_updates_without_duplicates_and_audits_changes(): void
    {
        $application = $this->participant();
        $this->save([$this->row($application)])->assertOk()->assertJsonPath('data.0.result_status', 'finished');
        $this->save([$this->row($application)])->assertOk();
        $this->assertDatabaseCount('competition_results', 1);
        $this->assertDatabaseCount('audit_logs', 1);
        $this->save([$this->row($application, score: '75.25')])->assertOk()->assertJsonPath('data.0.score', '75.25');
        $this->assertDatabaseCount('competition_results', 1);
        $this->assertDatabaseHas('audit_logs', ['event' => 'result.updated', 'user_id' => $this->admin->id]);
        $this->assertDatabaseHas('competition_results', ['entered_by_user_id' => $this->admin->id]);
    }

    public function test_active_admin_role_and_verified_email_are_required(): void
    {
        $application = $this->participant();
        $this->actingAs($application->athlete->user)->withSession(['active_role' => 'athlete']);
        $this->save([$this->row($application)])->assertForbidden();
        $coach = $this->user('coach');
        $this->actingAs($coach)->withSession(['active_role' => 'coach']);
        $this->postJson($this->adminUrl('/results/publish'))->assertForbidden();
        $this->getJson($this->adminUrl('/protocol'))->assertForbidden();
        $this->actingAs($this->admin)->withSession(['active_role' => 'athlete']);
        $this->save([$this->row($application)])->assertForbidden();
        $this->admin->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($this->admin)->withSession(['active_role' => 'admin']);
        $this->save([$this->row($application)])->assertForbidden();
    }

    public function test_guest_cannot_read_admin_results_or_history(): void
    {
        auth()->forgetGuards();
        $this->getJson($this->adminUrl('/participants'))->assertUnauthorized();
        $this->getJson('/athlete/results')->assertUnauthorized();
    }

    public function test_rejected_pending_missing_and_other_competition_applications_are_rejected_atomically(): void
    {
        $approved = $this->participant();
        $rejected = $this->participant('rejected');
        $pending = $this->participant('pending');
        $other = $this->participant();
        $otherCompetition = $this->competition->replicate();
        $otherCompetition->save();
        $other->update(['competition_id' => $otherCompetition->id]);

        foreach ([$rejected->athlete_id, $pending->athlete_id, $other->athlete_id, 999999] as $athleteId) {
            $invalid = $this->row($approved);
            $invalid['athlete_id'] = $athleteId;
            $this->save([$this->row($approved), $invalid])->assertUnprocessable()->assertJsonValidationErrors('results.1.athlete_id');
        }
        $this->assertDatabaseCount('competition_results', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_duplicate_athlete_in_batch_is_rejected(): void
    {
        $application = $this->participant();
        $this->save([$this->row($application), $this->row($application)])->assertUnprocessable();
        $this->assertDatabaseCount('competition_results', 0);
    }

    public function test_draft_and_future_competitions_cannot_accept_results(): void
    {
        $application = $this->participant();
        $this->competition->update(['status' => 'draft']);
        $this->save([$this->row($application)])->assertUnprocessable();
        $this->competition->update(['status' => 'published', 'ends_at' => now()->addDay()]);
        $this->save([$this->row($application)])->assertUnprocessable();
        $this->postJson($this->adminUrl('/results/publish'))->assertUnprocessable();
    }

    public function test_invalid_places_statuses_scores_and_service_fields_are_rejected(): void
    {
        $application = $this->participant();
        foreach ([
            ['place' => 0], ['place' => -1], ['place' => 1.5], ['place' => 2147483648],
            ['result_status' => 'ranked'], ['result_status' => 'dns', 'place' => 1],
            ['score' => null], ['score' => '1.234'], ['score' => '10000000000'],
            ['entered_by_user_id' => 100], ['competition_application_id' => 100],
        ] as $changes) {
            $this->save([array_replace($this->row($application), $changes)])->assertUnprocessable();
        }
        $this->putJson($this->adminUrl('/results'), [
            'results' => [$this->row($application)], 'results_published_by_user_id' => $this->admin->id,
        ])->assertUnprocessable();
        $this->assertDatabaseCount('competition_results', 0);
    }

    public function test_all_statuses_and_missing_results_are_in_standings_and_only_approved_participants_are_listed(): void
    {
        $first = $this->participant();
        $second = $this->participant();
        $dns = $this->participant();
        $dnf = $this->participant();
        $dsq = $this->participant();
        $this->participant();
        $this->participant('rejected');
        $this->save([
            $this->row($first, place: 2, score: '999'), $this->row($second, place: 1, score: '-5.25'),
            $this->row($dns, 'dns', null, null), $this->row($dnf, 'dnf', null, '1'), $this->row($dsq, 'dsq', null, null),
        ])->assertOk()->assertJsonCount(6, 'data')
            ->assertJsonPath('data.0.athlete_id', $second->athlete_id)
            ->assertJsonPath('data.1.athlete_id', $first->athlete_id);
        $response = $this->getJson($this->adminUrl('/participants'))->assertOk()->assertJsonCount(6, 'data');
        $this->assertSame(1, collect($response->json('data'))->whereNull('result')->count());
        $this->assertStringNotContainsString('email', $response->getContent());
        $this->assertStringNotContainsString('birth_date', $response->getContent());
    }

    public function test_shared_official_places_do_not_depend_on_scores(): void
    {
        $first = $this->participant();
        $second = $this->participant();
        $this->save([$this->row($first), $this->row($second, score: '15')])->assertOk()
            ->assertJsonPath('data.0.place', 1)->assertJsonPath('data.1.place', 1);
        $this->postJson($this->adminUrl('/results/publish'))->assertOk();
    }

    public function test_empty_incomplete_and_legacy_invalid_results_cannot_be_published(): void
    {
        $this->postJson($this->adminUrl('/results/publish'))->assertUnprocessable();
        $first = $this->participant();
        $second = $this->participant();
        $this->save([$this->row($first)])->assertOk();
        $this->postJson($this->adminUrl('/results/publish'))->assertUnprocessable();
        $second->result()->create(['status' => 'ranked', 'score' => 20, 'place' => 2]);
        $this->postJson($this->adminUrl('/results/publish'))->assertUnprocessable();
        $this->assertNull($this->competition->refresh()->results_published_at);
        $this->assertSame([], Storage::disk('protocols')->allFiles());
    }

    public function test_unpublished_results_and_pdf_are_private_and_draft_preview_contains_everyone(): void
    {
        $this->participant();
        $this->participant();
        $this->getJson('/competitions/'.$this->competition->id.'/results')->assertNotFound();
        $this->getJson('/competitions/'.$this->competition->id.'/protocol')->assertNotFound();
        $preview = $this->get($this->adminUrl('/protocol'))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $preview->getContent());
        $html = view('competitions.protocol', [
            'competition' => $this->competition->load(['discipline', 'competitionType', 'ageGroup', 'judgeAssignments.judge.user']),
            'rows' => app(CompetitionResultService::class)->standings($this->competition),
            'counts' => collect(['missing' => 2]), 'generatedAt' => now(),
        ])->render();
        foreach ($this->competition->applications()->with('athlete.user')->get() as $application) {
            $this->assertStringContainsString($application->athlete->user->last_name, $html);
        }
        $this->assertStringContainsString('Результат не заполнен', $html);
        $this->assertSame([], Storage::disk('protocols')->allFiles());
        $this->assertDatabaseHas('audit_logs', ['event' => 'protocol.previewed']);
    }

    public function test_publication_generates_real_pdf_is_idempotent_and_makes_results_public(): void
    {
        $application = $this->participant();
        $this->save([$this->row($application)])->assertOk();
        $this->postJson($this->adminUrl('/results/publish'))->assertOk()->assertJsonPath('meta.competition_id', $this->competition->id);
        $this->competition->refresh();
        $path = $this->competition->protocol_path;
        $contents = Storage::disk('protocols')->get($path);
        $this->assertStringStartsWith('%PDF-', $contents);
        $this->assertStringContainsString('%%EOF', $contents);
        $this->assertGreaterThan(10000, strlen($contents));
        $this->assertSame($this->admin->id, $this->competition->results_published_by_user_id);
        $this->postJson($this->adminUrl('/results/publish'))->assertOk();
        $this->assertSame($path, $this->competition->refresh()->protocol_path);
        $this->assertSame($contents, Storage::disk('protocols')->get($path));
        $this->assertCount(1, Storage::disk('protocols')->allFiles());
        $this->assertDatabaseCount('competition_results', 1);
        $this->assertSame(1, AuditLog::where('event', 'results.published')->count());
        auth()->forgetGuards();
        $this->getJson('/competitions/'.$this->competition->id.'/results')->assertOk()->assertJsonCount(1, 'data');
        $this->get('/competitions/'.$this->competition->id.'/protocol')->assertOk()->assertHeader('Content-Type', 'application/pdf')
            ->assertStreamedContent($contents);
    }

    public function test_publication_failure_leaves_no_publication_or_partial_file(): void
    {
        $application = $this->participant();
        $this->save([$this->row($application)])->assertOk();
        AuditLog::creating(function (AuditLog $log): void {
            if ($log->event === 'results.published') {
                throw new RuntimeException('Simulated audit failure after file write');
            }
        });
        try {
            config(['app.debug' => true]);
            $this->postJson($this->adminUrl('/results/publish'))->assertStatus(500)
                ->assertExactJson(['message' => 'Не удалось обработать запрос. Повторите попытку позднее.']);
        } finally {
            AuditLog::flushEventListeners();
        }
        $this->assertNull($this->competition->refresh()->results_published_at);
        $this->assertNull($this->competition->protocol_path);
        $this->assertSame([], Storage::disk('protocols')->allFiles());
    }

    public function test_pdf_render_failure_does_not_publish_results(): void
    {
        $application = $this->participant();
        $this->save([$this->row($application)])->assertOk();
        $service = Mockery::mock(CompetitionProtocolService::class, [app(CompetitionResultService::class)])->makePartial();
        $service->shouldReceive('render')->once()->andThrow(new RuntimeException('PDF failure'));
        $this->app->instance(CompetitionProtocolService::class, $service);
        config(['app.debug' => true]);
        $this->postJson($this->adminUrl('/results/publish'))->assertStatus(500)
            ->assertExactJson(['message' => 'Не удалось обработать запрос. Повторите попытку позднее.']);
        $this->assertNull($this->competition->refresh()->results_published_at);
        $this->assertSame([], Storage::disk('protocols')->allFiles());
    }

    public function test_published_results_and_participant_list_are_frozen(): void
    {
        $application = $this->participant();
        $pending = $this->participant('pending');
        $this->save([$this->row($application)])->assertOk();
        $this->postJson($this->adminUrl('/results/publish'))->assertOk();
        $this->save([$this->row($application, score: '5')])->assertUnprocessable();
        $this->patchJson('/admin/applications/'.$pending->id.'/approve')->assertUnprocessable();
        $this->patchJson('/admin/applications/'.$pending->id.'/reject', ['rejection_reason' => 'Позднее решение'])->assertUnprocessable();
        $this->assertDatabaseHas('competition_results', ['competition_application_id' => $application->id, 'score' => 95.5]);
        $this->assertDatabaseHas('competition_applications', ['id' => $pending->id, 'status' => 'pending']);
    }

    public function test_history_only_contains_session_athletes_published_results_and_has_pagination(): void
    {
        $mine = $this->participant();
        $other = $this->participant();
        $this->save([$this->row($mine), $this->row($other, place: 2)])->assertOk();
        $this->actingAs($mine->athlete->user)->withSession(['active_role' => 'athlete']);
        $this->getJson('/athlete/results')->assertOk()->assertJsonCount(0, 'data');
        $this->actingAs($this->admin)->withSession(['active_role' => 'admin']);
        $this->postJson($this->adminUrl('/results/publish'))->assertOk();
        $this->actingAs($mine->athlete->user)->withSession(['active_role' => 'athlete']);
        $response = $this->getJson('/athlete/results?athlete_id='.$other->athlete_id)->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('data.0.competition.id', $this->competition->id);
        $this->assertSame($mine->result->id, $response->json('data.0.id'));
        $this->assertStringContainsString('/protocol', $response->json('data.0.protocol_url'));
    }

    public function test_athlete_without_profile_gets_empty_history(): void
    {
        $this->actingAs($this->user('athlete'))->withSession(['active_role' => 'athlete']);
        $this->getJson('/athlete/results')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_database_prevents_duplicate_results(): void
    {
        $application = $this->participant();
        $this->save([$this->row($application)])->assertOk();
        $this->expectException(UniqueConstraintViolationException::class);
        $application->result()->create(['score' => 10, 'place' => 1, 'status' => 'finished']);
    }

    public function test_nonexistent_competition_returns_not_found(): void
    {
        $this->putJson('/admin/competitions/999999/results', ['results' => []])->assertNotFound();
    }

    public function test_protocol_handles_cyrillic_long_names_and_multiple_pages(): void
    {
        $rows = [];
        for ($index = 1; $index <= 75; $index++) {
            $application = $this->participant();
            $application->athlete->user->update([
                'last_name' => 'Проверочный-Спортсмен'.$index,
                'middle_name' => 'Александрович',
            ]);
            $rows[] = $this->row($application, place: $index);
        }
        Organization::query()->update(['name' => 'Государственное образовательное учреждение дополнительного образования «Центр спортивного программирования Оренбургской области»']);
        $this->save($rows)->assertOk()->assertJsonCount(75, 'data');
        $this->postJson($this->adminUrl('/results/publish'))->assertOk();
        $contents = Storage::disk('protocols')->get($this->competition->refresh()->protocol_path);
        $this->assertGreaterThan(1, preg_match_all('~/Type\s*/Page\b~', $contents));
        file_put_contents(storage_path('framework/testing/protocol-qa.pdf'), $contents);
    }

    public function test_protocol_uses_real_assigned_judges_without_inventing_signatures(): void
    {
        $judgeUser = $this->user('judge');
        $judge = Judge::create(['user_id' => $judgeUser->id]);
        $this->competition->judgeAssignments()->create([
            'judge_id' => $judge->id, 'position' => 'Главный судья', 'assigned_by_user_id' => $this->admin->id,
        ]);
        $html = view('competitions.protocol', [
            'competition' => $this->competition->load(['discipline', 'competitionType', 'ageGroup', 'judgeAssignments.judge.user']),
            'rows' => collect(), 'counts' => collect(), 'generatedAt' => now(),
        ])->render();
        $this->assertStringContainsString('Главный судья', $html);
        $this->assertStringContainsString($judgeUser->last_name, $html);
        $this->assertStringNotContainsString('реквизиты не заполнены', $html);
        $this->get($this->adminUrl('/protocol'))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_history_is_sorted_by_competition_date_and_paginated(): void
    {
        $application = $this->participant();
        $competitions = collect();
        for ($index = 0; $index < 16; $index++) {
            $competition = $this->competition->replicate();
            $competition->starts_at = now()->subDays(30 - $index);
            $competition->ends_at = now()->subDays(29 - $index);
            $competition->save();
            $competition->forceFill(['results_published_at' => now()])->save();
            $record = $competition->applications()->create([
                'athlete_id' => $application->athlete_id, 'submitted_by_user_id' => $this->admin->id, 'status' => 'approved',
            ]);
            $record->result()->create(['status' => 'finished', 'place' => 1, 'score' => 50]);
            $competitions->push($competition);
        }
        $this->actingAs($application->athlete->user)->withSession(['active_role' => 'athlete']);
        $this->getJson('/athlete/results')->assertOk()->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.total', 16)->assertJsonPath('data.0.competition.id', $competitions->last()->id);
        $this->getJson('/athlete/results?page=2')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.competition.id', $competitions->first()->id);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', $role)->firstOrFail());

        return $user;
    }

    private function participant(string $status = 'approved'): CompetitionApplication
    {
        $user = $this->user('athlete');
        $user->update(['first_name' => 'Алексей', 'last_name' => 'Участник'.$user->id]);
        $athlete = Athlete::create([
            'user_id' => $user->id,
            'organization_id' => Organization::firstOrCreate(['name' => 'Учебная организация'], ['type' => 'school', 'municipality' => 'Оренбург'])->id,
            'birth_date' => '2008-01-01', 'municipality' => 'Оренбург', 'start_year' => 2020,
        ]);

        return $this->competition->applications()->create([
            'athlete_id' => $athlete->id, 'submitted_by_user_id' => $user->id, 'status' => $status,
        ]);
    }

    /** @return array{athlete_id: int, score: ?string, place: ?int, result_status: string} */
    private function row(CompetitionApplication $application, string $status = 'finished', ?int $place = 1, ?string $score = '95.50'): array
    {
        return ['athlete_id' => $application->athlete_id, 'score' => $score, 'place' => $place, 'result_status' => $status];
    }

    /** @param list<array<string, mixed>> $rows */
    private function save(array $rows): TestResponse
    {
        return $this->putJson($this->adminUrl('/results'), ['results' => $rows]);
    }

    private function adminUrl(string $suffix): string
    {
        return '/admin/competitions/'.$this->competition->id.$suffix;
    }
}
