<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regional_teams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rating_season_id')
                ->constrained('rating_seasons')
                ->restrictOnDelete();

            $table->foreignId('discipline_id')
                ->constrained('disciplines')
                ->restrictOnDelete();

            $table->foreignId('age_group_id')
                ->constrained('age_groups')
                ->restrictOnDelete();

            // Статус состава сборной
            $table->string('status', 30)
                ->default('draft');

            // Приказ о составе сборной
            $table->string('order_number', 100)
                ->nullable();

            $table->date('order_date')->nullable();

            $table->string('order_path', 500)
                ->nullable();

            // Кто сформировал состав
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique([
                'rating_season_id',
                'discipline_id',
                'age_group_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regional_teams');
    }
};