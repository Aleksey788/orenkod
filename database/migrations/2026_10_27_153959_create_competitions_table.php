<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();

            $table->string('title', 255);

            $table->foreignId('discipline_id')
                ->constrained('disciplines')
                ->restrictOnDelete();

            $table->foreignId('competition_type_id')
                ->constrained('competition_types')
                ->restrictOnDelete();

            $table->foreignId('age_group_id')
                ->constrained('age_groups')
                ->restrictOnDelete();

            // Уровень соревнования
            $table->string('level', 50);

            // Формат проведения
            $table->string('format', 30);

            // Место проведения
            $table->string('location', 255)->nullable();

            // Даты соревнования
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');

            // Период подачи заявок
            $table->timestamp('registration_starts_at');
            $table->timestamp('registration_ends_at');

            // Состояние соревнования
            $table->string('status', 30)
                ->default('draft');

            // Календарный план
            $table->string('calendar_plan', 30)
                ->default('none');

            $table->string('calendar_plan_number', 100)
                ->nullable();

            // Документы
            $table->string('regulation_path', 500)
                ->nullable();

            $table->string('protocol_path', 500)
                ->nullable();

            // Кто создал соревнование
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};