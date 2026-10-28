<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->timestamp('results_published_at')->nullable()->index();
            $table->foreignId('results_published_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
        });

        Schema::table('competition_results', function (Blueprint $table) {
            $table->string('status', 30)->default('finished')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competition_results', function (Blueprint $table) {
            $table->string('status', 30)->default('ranked')->change();
        });

        Schema::table('competitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('results_published_by_user_id');
            $table->dropColumn('results_published_at');
        });
    }
};
