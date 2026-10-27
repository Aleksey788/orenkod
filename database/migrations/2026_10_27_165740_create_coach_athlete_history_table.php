<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_athlete_history', function (Blueprint $table) {
            $table->id();

            // Связь спортсмена и тренера
            $table->foreignId('coach_athlete_id')
                ->constrained('coach_athlete')
                ->restrictOnDelete();

            // Предыдущий статус
            $table->string('old_status', 20)
                ->nullable();

            // Новый статус
            $table->string('new_status', 20);

            // Кто изменил статус
            $table->foreignId('changed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Причина изменения
            $table->text('reason')->nullable();

            $table->timestamp('created_at')
                ->useCurrent();

            $table->index([
                'coach_athlete_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_athlete_history');
    }
};