<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rank_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->restrictOnDelete();

            $table->foreignId('sport_rank_id')
                ->constrained('sport_ranks')
                ->restrictOnDelete();

            $table->foreignId('discipline_id')
                ->constrained('disciplines')
                ->restrictOnDelete();

            // Результат соревнования — основание заявки
            $table->foreignId('competition_result_id')
                ->constrained('competition_results')
                ->restrictOnDelete();

            // Спортсмен или тренер, подавший заявку
            $table->foreignId('submitted_by_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Присвоение или подтверждение
            $table->string('request_type', 30)
                ->default('assignment');

            // Статус рассмотрения
            $table->string('status', 30)
                ->default('pending');

            // Сотрудник федерации
            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')
                ->nullable();

            $table->text('rejection_reason')
                ->nullable();

            $table->timestamps();

            $table->index(['athlete_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rank_applications');
    }
};