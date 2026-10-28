<?php

namespace Tests\Feature;

use App\Models\Athlete;
use App\Models\AuditLog;
use App\Models\RegionalTeam;
use App\Models\Role;
use Database\Factories\AgeGroupFactory;
use Database\Factories\AthleteRankFactory;
use Database\Factories\AthleteRestrictionFactory;
use Database\Factories\DisciplineFactory;
use Database\Factories\RankApplicationFactory;
use Database\Factories\SportRankFactory;
use Database\Seeders\RatingRuleSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\RatingTestCase;

class RatingAndRegionalTeamTest extends RatingTestCase
{
    #[DataProvider('basePoints')]
    public function test_base_points_follow_editable_category_rules(string $category, int $place, int $points): void
    {
        $this->competitionResult($this->athlete(), $place, $category);
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonPath('data.0.base_points', $points)
            ->assertJsonPath('data.0.total_points', $points.'.00')->assertJsonPath('data.0.stability_coefficient', '1.00');
    }

    public static function basePoints(): array
    {
        return [
            ['regional', 1, 40], ['regional', 2, 30], ['regional', 3, 20], ['regional', 4, 0],
            ['interregional', 1, 60], ['interregional', 2, 45], ['interregional', 3, 30],
            ['national_championship', 1, 100], ['national_championship', 2, 80], ['national_championship', 3, 60],
            ['national_youth', 1, 80], ['national_youth', 2, 60], ['national_youth', 3, 40],
        ];
    }

    public function test_two_distinct_podium_competitions_apply_coefficient_to_sum(): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete, 1);
        $this->competitionResult($athlete, 3);
        $this->competitionResult($athlete, 4);
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonPath('data.0.base_points', 60)
            ->assertJsonPath('data.0.podium_competitions_count', 2)->assertJsonPath('data.0.stability_coefficient', '1.20')
            ->assertJsonPath('data.0.total_points', '72.00');
        $this->getJson($this->ratingsUrl().'/athletes/'.$athlete->id)->assertOk()->assertJsonCount(3, 'data.results')
            ->assertJsonPath('data.results.0.awarded_points', 40)->assertJsonPath('data.results.1.awarded_points', 20);
    }

    #[DataProvider('nonFinishStatuses')]
    public function test_non_finished_results_receive_no_rating_points(string $status): void
    {
        $this->competitionResult($this->athlete(), status: $status);
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonPath('data.0.total_points', '0.00')
            ->assertJsonPath('data.0.podium_competitions_count', 0);
    }

    public static function nonFinishStatuses(): array
    {
        return [['dns'], ['dnf'], ['dsq']];
    }

    public function test_only_approved_official_results_in_season_and_discipline_are_used(): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete, 2);
        $this->competitionResult($athlete, published: false);
        $this->competitionResult($athlete, competitionData: ['starts_at' => '2025-12-31 23:59:59']);
        $this->competitionResult($athlete, competitionData: ['starts_at' => '2027-01-01 00:00:00']);
        $this->competitionResult($athlete, competitionData: ['discipline_id' => DisciplineFactory::new()->create()->id]);
        $this->competitionResult($athlete, competitionData: ['status' => 'draft']);
        $rejected = $this->competitionResult($athlete);
        $rejected->application->update(['status' => 'rejected']);
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonPath('data.0.base_points', 30)
            ->assertJsonPath('data.0.podium_competitions_count', 1);
    }

    public function test_classification_is_explicit_and_does_not_use_competition_title(): void
    {
        $result = $this->competitionResult($this->athlete());
        $competition = $result->application->competition;
        $competition->forceFill(['rating_category' => null, 'title' => 'Чемпионат России'])->save();
        $this->getJson($this->ratingsUrl())->assertUnprocessable()->assertJsonValidationErrors('rating_category');
        $url = '/admin/competitions/'.$competition->id.'/rating-category';
        $this->patchJson($url, ['rating_category' => 'national_championship', 'reason' => 'Проверка'])->assertUnprocessable();
        $this->patchJson($url, ['rating_category' => 'regional', 'reason' => 'Классификация федерации'])->assertOk();
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonPath('data.0.base_points', 40);
        $this->assertDatabaseHas('audit_logs', ['event' => 'competition.classified']);
    }

    public function test_rules_are_editable_and_seeding_never_overwrites_federation_values(): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete);
        $rule = $this->season->rules()->where('category', 'regional')->where('place', 1)->firstOrFail();
        $payload = ['rating_season_id' => $this->season->id, 'category' => 'regional', 'place' => 1, 'points' => 47];
        $this->putJson('/admin/rating-rules/'.$rule->id, $payload)->assertOk();
        $this->seed(RatingRuleSeeder::class);
        $this->seed(RatingRuleSeeder::class);
        $this->assertDatabaseCount('rating_rules', 12);
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonPath('data.0.base_points', 47);
        $this->postJson('/admin/rating-rules', $payload)->assertUnprocessable();
        $this->postJson('/admin/rating-rules', [...$payload, 'place' => 4, 'points' => 5])->assertSuccessful();
        $other = $this->athlete();
        $this->competitionResult($other, 4);
        $response = $this->getJson($this->ratingsUrl())->assertOk();
        $this->assertSame(5, collect($response->json('data'))->firstWhere('athlete_id', $other->id)['base_points']);
    }

    public function test_missing_podium_rule_is_reported_instead_of_guessing_points(): void
    {
        $this->competitionResult($this->athlete());
        $this->season->rules()->where('category', 'regional')->where('place', 1)->delete();
        $this->getJson($this->ratingsUrl())->assertUnprocessable()->assertJsonValidationErrors('rules');
    }

    public function test_equal_points_share_rank_and_skip_the_next_rank(): void
    {
        $this->competitionResult($this->athlete());
        $this->competitionResult($this->athlete());
        $this->competitionResult($this->athlete(), 2);
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonPath('data.0.rating_rank', 1)
            ->assertJsonPath('data.1.rating_rank', 1)->assertJsonPath('data.2.rating_rank', 3);
    }

    public function test_season_stability_settings_are_configurable(): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete);
        $this->competitionResult($athlete, 3);
        $payload = ['name' => $this->season->name, 'year' => 2026, 'starts_on' => '2026-01-01', 'ends_on' => '2026-12-31',
            'status' => 'active', 'stability_coefficient' => '1.50', 'min_podium_results' => 3];
        $this->putJson('/admin/rating-seasons/'.$this->season->id, $payload)->assertOk();
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonPath('data.0.total_points', '60.00');
        $this->competitionResult($athlete, 2);
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonPath('data.0.total_points', '135.00')
            ->assertJsonPath('data.0.stability_coefficient', '1.50');
    }

    #[DataProvider('restrictionTypes')]
    public function test_active_restrictions_exclude_athletes_and_expired_ones_do_not(string $type): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete);
        $restriction = AthleteRestrictionFactory::new()->create(['athlete_id' => $athlete->id, 'type' => $type]);
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonCount(0, 'data');
        $restriction->ends_on = '2026-10-27';
        $restriction->save();
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonCount(1, 'data');
        $this->getJson($this->ratingsUrl().'?as_of=2026-10-27')->assertOk()->assertJsonCount(0, 'data');
    }

    public static function restrictionTypes(): array
    {
        return [['disqualification'], ['suspension']];
    }

    public function test_restrictions_can_be_revoked_with_audited_reason(): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete);
        $response = $this->postJson('/admin/athlete-restrictions', ['athlete_id' => $athlete->id, 'type' => 'suspension',
            'reason' => 'Решение', 'starts_on' => '2026-10-01', 'ends_on' => null])->assertSuccessful();
        $id = $response->json('data.id');
        $this->getJson($this->ratingsUrl())->assertJsonCount(0, 'data');
        $this->postJson('/admin/athlete-restrictions/'.$id.'/revoke', ['reason' => 'Решение отменено'])->assertOk();
        $this->getJson($this->ratingsUrl())->assertJsonCount(1, 'data');
        $this->getJson($this->ratingsUrl().'?as_of=2026-10-20')->assertJsonCount(0, 'data');
        $this->assertDatabaseHas('audit_logs', ['event' => 'restriction.saved']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'restriction.revoked']);
    }

    public function test_age_is_evaluated_at_season_end_and_rank_uses_explicit_order_and_validity(): void
    {
        $young = $this->athlete();
        $young->update(['birth_date' => '2012-12-30']);
        $older = $this->athlete();
        $older->update(['birth_date' => '1990-01-01']);
        $youngResult = $this->competitionResult($young);
        $this->competitionResult($older);
        $this->getJson($this->ratingsUrl().'?age_group_id='.$this->ageGroup->id)->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.athlete_id', $young->id);
        $strong = SportRankFactory::new()->create(['sort_order' => 30]);
        $weak = SportRankFactory::new()->create(['sort_order' => 10]);
        $application = RankApplicationFactory::new()->create(['athlete_id' => $young->id, 'sport_rank_id' => $strong->id,
            'discipline_id' => $this->discipline->id, 'competition_result_id' => $youngResult->id]);
        $rank = AthleteRankFactory::new()->create(['athlete_id' => $young->id, 'sport_rank_id' => $strong->id,
            'discipline_id' => $this->discipline->id, 'rank_application_id' => $application->id]);
        $url = $this->ratingsUrl().'?min_sport_rank_id='.$weak->id;
        $this->getJson($url)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.athlete_id', $young->id);
        $rank->update(['expires_at' => '2026-10-27']);
        $this->getJson($url)->assertOk()->assertJsonCount(0, 'data');
        $weak->update(['sort_order' => 0]);
        $this->getJson($url)->assertUnprocessable();
    }

    public function test_season_management_rejects_overlap_invalid_dates_and_service_fields(): void
    {
        $payload = ['name' => 'Сезон 2027', 'year' => 2027, 'starts_on' => '2027-01-01', 'ends_on' => '2027-12-31',
            'status' => 'draft', 'stability_coefficient' => '1.20', 'min_podium_results' => 2];
        $this->postJson('/admin/rating-seasons', [...$payload, 'starts_on' => '2026-12-31'])->assertUnprocessable();
        $this->postJson('/admin/rating-seasons', [...$payload, 'ends_on' => '2026-12-31'])->assertUnprocessable();
        $this->postJson('/admin/rating-seasons', [...$payload, 'rating_snapshot' => []])->assertUnprocessable();
        $this->postJson('/admin/rating-seasons', $payload)->assertSuccessful()->assertJsonPath('data.status', 'draft')->assertJsonCount(12, 'data.rules');
        $this->assertDatabaseHas('audit_logs', ['event' => 'season.created']);
    }

    public function test_closed_season_is_frozen_until_explicit_audited_recalculation(): void
    {
        $athlete = $this->athlete();
        $result = $this->competitionResult($athlete);
        $payload = ['name' => $this->season->name, 'year' => 2026, 'starts_on' => '2026-01-01', 'ends_on' => '2026-10-28',
            'status' => 'closed', 'stability_coefficient' => '1.20', 'min_podium_results' => 2];
        $this->putJson('/admin/rating-seasons/'.$this->season->id, $payload)->assertOk()->assertJsonPath('data.rating_version', 1);
        $this->competitionResult($athlete, 3);
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonPath('meta.mode', 'snapshot')->assertJsonPath('data.0.total_points', '40.00');
        $this->putJson('/admin/rating-seasons/'.$this->season->id, $payload)->assertUnprocessable();
        $rule = $this->season->rules()->first();
        $this->putJson('/admin/rating-rules/'.$rule->id, ['rating_season_id' => $this->season->id,
            'category' => $rule->category, 'place' => $rule->place, 'points' => 999])->assertUnprocessable();
        $this->patchJson('/admin/competitions/'.$result->application->competition_id.'/rating-category',
            ['rating_category' => 'regional', 'reason' => 'Изменение'])->assertUnprocessable();
        $this->postJson('/admin/rating-seasons/'.$this->season->id.'/recalculate', ['reason' => 'Учтён поздний официальный протокол'])
            ->assertOk()->assertJsonPath('data.rating_version', 2);
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonPath('data.0.total_points', '72.00');
        $this->assertDatabaseHas('audit_logs', ['event' => 'rating.recalculated']);
    }

    public function test_public_rating_and_detail_do_not_leak_private_athlete_fields(): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete);
        auth()->forgetGuards();
        foreach ([$this->ratingsUrl(), $this->ratingsUrl().'/athletes/'.$athlete->id] as $url) {
            $response = $this->getJson($url)->assertOk();
            foreach (['birth_date', 'email', 'phone', '_rank_order', '_total_hundredths', 'password', 'restriction'] as $private) {
                $this->assertStringNotContainsString($private, $response->getContent());
            }
        }
        $this->season->update(['status' => 'draft']);
        $this->getJson($this->ratingsUrl())->assertNotFound();
    }

    public function test_athlete_can_only_get_own_personal_rating(): void
    {
        $mine = $this->athlete();
        $other = $this->athlete();
        $this->competitionResult($mine, 2);
        $this->competitionResult($other);
        $user = $mine->user;
        $user->roles()->attach(Role::where('name', 'athlete')->firstOrFail());
        $this->actingAs($user)->withSession(['active_role' => 'athlete']);
        $this->getJson('/athlete/rating?season_id='.$this->season->id.'&discipline_id='.$this->discipline->id.'&athlete_id='.$other->id)
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.athlete_id', $mine->id)->assertJsonPath('data.0.rating_rank', 2);
    }

    public function test_closed_snapshot_preserves_filter_definitions_and_team_rechecks_present_day_eligibility(): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete);
        $payload = ['name' => $this->season->name, 'year' => 2026, 'starts_on' => '2026-01-01', 'ends_on' => '2026-10-26',
            'status' => 'closed', 'stability_coefficient' => '1.20', 'min_podium_results' => 2];
        $this->putJson('/admin/rating-seasons/'.$this->season->id, $payload)->assertOk();
        $this->ageGroup->update(['min_age' => 50, 'max_age' => 99]);
        $this->getJson($this->ratingsUrl().'?age_group_id='.$this->ageGroup->id)->assertOk()->assertJsonCount(1, 'data');
        AthleteRestrictionFactory::new()->create(['athlete_id' => $athlete->id, 'starts_on' => '2026-10-28']);
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonCount(1, 'data');
        $team = $this->team();
        $this->getJson('/admin/regional-teams/'.$team->id.'/candidates')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.eligible', false);
        $this->addMember($team, $athlete, false);
    }

    public function test_admin_creates_team_selects_candidates_manually_and_preserves_snapshot(): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete, 3);
        $this->competitionResult($this->athlete());
        $team = $this->team();
        $this->assertDatabaseCount('regional_team_members', 0);
        $this->getJson('/admin/regional-teams/'.$team->id.'/candidates')->assertOk()->assertJsonCount(2, 'data');
        $url = '/admin/regional-teams/'.$team->id.'/members';
        $this->postJson($url, ['athlete_id' => $athlete->id, 'reason' => 'Решение федерации'])->assertSuccessful()
            ->assertJsonPath('data.rating_points_at_inclusion', '20.00');
        $this->postJson($url, ['athlete_id' => $athlete->id, 'reason' => 'Повтор'])->assertUnprocessable();
        $rule = $this->season->rules()->where('category', 'regional')->where('place', 3)->firstOrFail();
        $this->putJson('/admin/rating-rules/'.$rule->id, ['rating_season_id' => $this->season->id,
            'category' => 'regional', 'place' => 3, 'points' => 200])->assertOk();
        $this->getJson('/admin/regional-teams/'.$team->id)->assertOk()->assertJsonPath('data.members.0.rating_points_at_inclusion', '20.00');
        $this->assertDatabaseCount('regional_team_members', 1);
    }

    public function test_ineligible_or_wrong_scope_athlete_cannot_be_added(): void
    {
        $restricted = $this->athlete();
        $this->competitionResult($restricted);
        AthleteRestrictionFactory::new()->create(['athlete_id' => $restricted->id]);
        $wrongAge = $this->athlete();
        $wrongAge->update(['birth_date' => '1990-01-01']);
        $this->competitionResult($wrongAge);
        $team = $this->team();
        foreach ([$restricted, $wrongAge, $this->athlete()] as $athlete) {
            $this->postJson('/admin/regional-teams/'.$team->id.'/members', ['athlete_id' => $athlete->id, 'reason' => 'Отбор'])->assertUnprocessable();
        }
        $this->assertDatabaseCount('regional_team_members', 0);
    }

    public function test_member_removal_preserves_record_and_decision_history_and_can_be_readded(): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete);
        $team = $this->team();
        $this->addMember($team, $athlete);
        $member = $team->members()->firstOrFail();
        $this->deleteJson('/admin/regional-teams/'.$team->id.'/members/'.$member->id, ['reason' => 'Решение пересмотрено'])->assertNoContent();
        $this->assertDatabaseCount('regional_team_members', 1);
        $this->assertNotNull($member->refresh()->removed_at);
        $this->getJson('/admin/regional-teams/'.$team->id)->assertJsonCount(0, 'data.members');
        $this->addMember($team, $athlete);
        $this->assertDatabaseCount('regional_team_members', 1);
        $this->assertDatabaseHas('audit_logs', ['event' => 'team.member_removed']);
    }

    public function test_team_publication_requires_members_and_rechecks_current_restrictions(): void
    {
        $team = $this->team();
        $this->postJson('/admin/regional-teams/'.$team->id.'/publish')->assertUnprocessable();
        $athlete = $this->athlete();
        $this->competitionResult($athlete);
        $this->addMember($team, $athlete);
        AthleteRestrictionFactory::new()->create(['athlete_id' => $athlete->id]);
        $this->postJson('/admin/regional-teams/'.$team->id.'/publish')->assertUnprocessable();
        $this->assertSame('draft', $team->refresh()->status);
        $this->assertSame([], Storage::disk('team_documents')->allFiles());
    }

    public function test_both_pdfs_exist_and_draft_documents_are_private(): void
    {
        $athlete = $this->athlete();
        $athlete->user->update(['first_name' => 'Алексей', 'last_name' => 'Тестовый', 'middle_name' => 'Александрович']);
        $this->competitionResult($athlete);
        $team = $this->team();
        $this->addMember($team, $athlete);
        foreach (['document', 'order-draft'] as $kind) {
            $response = $this->get('/admin/regional-teams/'.$team->id.'/'.$kind)->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
        }
        $this->getJson('/regional-teams/'.$team->id)->assertNotFound();
        $this->getJson('/regional-teams/'.$team->id.'/document')->assertNotFound();
        $this->postJson('/admin/regional-teams/'.$team->id.'/publish')->assertOk();
        $team->refresh();
        $this->assertCount(2, Storage::disk('team_documents')->allFiles());
        $this->assertStringStartsWith('%PDF-', Storage::disk('team_documents')->get($team->list_path));
        $this->assertStringStartsWith('%PDF-', Storage::disk('team_documents')->get($team->order_path));
        $paths = [$team->list_path, $team->order_path];
        $this->postJson('/admin/regional-teams/'.$team->id.'/publish')->assertOk();
        $this->assertSame($paths, [$team->refresh()->list_path, $team->order_path]);
        auth()->forgetGuards();
        $response = $this->getJson('/regional-teams/'.$team->id)->assertOk();
        $this->assertStringNotContainsString('reason', $response->getContent());
        $this->get('/regional-teams/'.$team->id.'/document')->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->getJson('/admin/regional-teams/'.$team->id.'/order-draft')->assertUnauthorized();
    }

    public function test_published_team_is_immutable_and_new_version_preserves_previous_composition(): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete);
        $team = $this->team();
        $this->addMember($team, $athlete);
        $this->postJson('/admin/regional-teams/'.$team->id.'/publish')->assertOk();
        $this->putJson('/admin/regional-teams/'.$team->id, $this->teamData())->assertUnprocessable();
        $this->addMember($team, $athlete, false);
        $response = $this->postJson('/admin/regional-teams/'.$team->id.'/versions', ['reason' => 'Уточнение официального состава'])
            ->assertCreated()->assertJsonPath('data.version', 2)->assertJsonCount(1, 'data.members');
        $next = RegionalTeam::findOrFail($response->json('data.id'));
        $this->assertDatabaseCount('regional_team_members', 2);
        $this->postJson('/admin/regional-teams/'.$next->id.'/publish')->assertOk();
        $this->assertSame('superseded', $team->refresh()->status);
        $this->getJson('/regional-teams/'.$team->id)->assertOk()->assertJsonCount(1, 'data.members');
        $this->get('/regional-teams/'.$team->id.'/document')->assertOk();
        $this->getJson('/regional-teams')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_publication_failure_rolls_back_team_and_cleans_generated_files(): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete);
        $team = $this->team();
        $this->addMember($team, $athlete);
        AuditLog::creating(function (AuditLog $log): void {
            if ($log->event === 'team.published') {
                throw new RuntimeException('Test failure after storing both PDFs');
            }
        });
        try {
            config(['app.debug' => true]);
            $this->postJson('/admin/regional-teams/'.$team->id.'/publish')->assertStatus(500)
                ->assertExactJson(['message' => 'Не удалось обработать запрос. Повторите попытку позднее.']);
        } finally {
            AuditLog::flushEventListeners();
        }
        $this->assertSame('draft', $team->refresh()->status);
        $this->assertNull($team->published_at);
        $this->assertSame([], Storage::disk('team_documents')->allFiles());
    }

    public function test_different_team_member_cannot_be_removed_and_service_fields_are_rejected(): void
    {
        $athlete = $this->athlete();
        $this->competitionResult($athlete);
        $team = $this->team();
        $this->addMember($team, $athlete);
        $otherGroup = AgeGroupFactory::new()->create();
        $response = $this->postJson('/admin/regional-teams', [...$this->teamData(), 'age_group_id' => $otherGroup->id])->assertSuccessful();
        $member = $team->members()->firstOrFail();
        $this->deleteJson('/admin/regional-teams/'.$response->json('data.id').'/members/'.$member->id, ['reason' => 'Подмена'])->assertNotFound();
        $this->postJson('/admin/regional-teams', [...$this->teamData(), 'status' => 'published'])->assertUnprocessable();
        $this->postJson('/admin/regional-teams/'.$team->id.'/members', ['athlete_id' => $athlete->id, 'reason' => 'Отбор',
            'included_by_user_id' => $this->admin->id])->assertUnprocessable();
        $this->postJson('/admin/athlete-restrictions', ['athlete_id' => $athlete->id, 'type' => 'suspension', 'reason' => 'Решение',
            'starts_on' => '2026-10-01', 'created_by_user_id' => $this->admin->id])->assertUnprocessable();
    }

    public function test_roles_and_email_protect_every_administrative_operation(): void
    {
        $athlete = $this->athlete();
        $user = $athlete->user;
        $user->roles()->attach(Role::where('name', 'athlete')->firstOrFail());
        $this->actingAs($user)->withSession(['active_role' => 'athlete']);
        $this->postJson('/admin/regional-teams', $this->teamData())->assertForbidden();
        $this->postJson('/admin/rating-rules', [])->assertForbidden();
        $this->postJson('/admin/athlete-restrictions', [])->assertForbidden();
        $this->postJson('/admin/rating-seasons', [])->assertForbidden();
        $this->actingAs($this->admin)->withSession(['active_role' => 'athlete']);
        $this->getJson('/admin/rating-seasons')->assertForbidden();
        $this->admin->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($this->admin)->withSession(['active_role' => 'admin']);
        $this->getJson('/admin/rating-seasons')->assertForbidden();
        auth()->forgetGuards();
        $this->postJson('/admin/regional-teams', $this->teamData())->assertUnauthorized();
        $this->getJson('/athlete/rating?season_id='.$this->season->id.'&discipline_id='.$this->discipline->id)->assertUnauthorized();
    }

    public function test_rating_queries_do_not_grow_per_athlete(): void
    {
        $this->competitionResult($this->athlete());
        DB::enableQueryLog();
        $this->getJson($this->ratingsUrl())->assertOk();
        $small = count(DB::getQueryLog());
        DB::disableQueryLog();
        for ($index = 0; $index < 10; $index++) {
            $this->competitionResult($this->athlete());
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->getJson($this->ratingsUrl())->assertOk()->assertJsonCount(11, 'data');
        $large = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertSame($small, $large);
    }

    public function test_documents_handle_long_cyrillic_names_and_multiple_pages(): void
    {
        $team = $this->team();
        for ($index = 1; $index <= 30; $index++) {
            $athlete = $this->athlete();
            $athlete->user->update(['last_name' => 'Проверочный-Спортсмен'.$index, 'first_name' => 'Алексей', 'middle_name' => 'Александрович']);
            $athlete->organization->update(['name' => 'Центр спортивного программирования Оренбургской области']);
            $this->competitionResult($athlete);
            $this->addMember($team, $athlete);
        }
        $this->postJson('/admin/regional-teams/'.$team->id.'/publish')->assertOk();
        $team->refresh();
        foreach (['team-list' => $team->list_path, 'order-draft' => $team->order_path] as $name => $path) {
            $contents = Storage::disk('team_documents')->get($path);
            $this->assertGreaterThan(1, preg_match_all('~/Type\s*/Page\b~', $contents));
            file_put_contents(storage_path('framework/testing/'.$name.'-qa.pdf'), $contents);
        }
    }

    private function addMember(RegionalTeam $team, Athlete $athlete, bool $successful = true): void
    {
        $response = $this->postJson('/admin/regional-teams/'.$team->id.'/members', ['athlete_id' => $athlete->id, 'reason' => 'Решение федерации']);
        $successful ? $response->assertSuccessful() : $response->assertUnprocessable();
    }
}
