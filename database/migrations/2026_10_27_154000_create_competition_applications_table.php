<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('competition_id')
                ->constrained('competitions')
                ->cascadeOnDelete();

            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->cascadeOnDelete();

            // Кто подал заявку
            $table->foreignId('submitted_by_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Состояние заявки
            $table->string('status', 30)
                ->default('pending');

            // Кто рассмотрел заявку
            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')
                ->nullable();

            $table->text('rejection_reason')
                ->nullable();

            $table->timestamps();

            // Один спортсмен — одна заявка
            // на конкретное соревнование
            $table->unique([
                'competition_id',
                'athlete_id'
            ]);

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_applications');
    }
};