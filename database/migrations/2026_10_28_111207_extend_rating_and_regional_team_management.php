<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->string('rating_category', 50)->nullable()->index();
        });
        Schema::table('rating_seasons', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->index();
            $table->json('rating_snapshot')->nullable();
            $table->timestamp('ratings_calculated_at')->nullable();
            $table->unsignedInteger('rating_version')->default(0);
        });
        DB::table('rating_seasons')->where('is_active', true)->update(['status' => 'active']);
        Schema::table('regional_teams', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->foreignId('min_sport_rank_id')->nullable()->constrained('sport_ranks')->restrictOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('supersedes_team_id')->nullable()->constrained('regional_teams')->restrictOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('list_path', 500)->nullable();
            $table->dropUnique(['rating_season_id', 'discipline_id', 'age_group_id']);
            $table->unique(['rating_season_id', 'discipline_id', 'age_group_id', 'version'], 'regional_teams_scope_version_unique');
        });
        Schema::table('regional_team_members', function (Blueprint $table) {
            $table->json('rating_snapshot')->nullable();
            $table->foreignId('included_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('removed_at')->nullable();
            $table->text('removal_reason')->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('regional_teams')->select('rating_season_id', 'discipline_id', 'age_group_id')
            ->groupBy('rating_season_id', 'discipline_id', 'age_group_id')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Cannot roll back while multiple team versions exist.');
        }
        Schema::table('regional_team_members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('included_by_user_id');
            $table->dropColumn(['rating_snapshot', 'removed_at', 'removal_reason']);
        });
        Schema::table('regional_teams', function (Blueprint $table) {
            $table->dropUnique('regional_teams_scope_version_unique');
            $table->unique(['rating_season_id', 'discipline_id', 'age_group_id']);
            $table->dropConstrainedForeignId('min_sport_rank_id');
            $table->dropConstrainedForeignId('supersedes_team_id');
            $table->dropConstrainedForeignId('published_by_user_id');
            $table->dropColumn(['name', 'version', 'published_at', 'list_path']);
        });
        Schema::table('rating_seasons', function (Blueprint $table) {
            $table->dropColumn(['status', 'rating_snapshot', 'ratings_calculated_at', 'rating_version']);
        });
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn('rating_category');
        });
    }
};
