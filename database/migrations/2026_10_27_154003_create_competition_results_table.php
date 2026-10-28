<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_results', function (Blueprint $table) {
            $table->id();

            // Одобренная заявка участника
            $table->foreignId('competition_application_id')
                ->unique()
                ->constrained('competition_applications')
                ->cascadeOnDelete();

            // Занятое место
            $table->unsignedInteger('place')
                ->nullable();

            // Набранные баллы
            $table->decimal('score', 12, 2)
                ->nullable();

            // Статус результата
            $table->string('status', 30)
                ->default('ranked');

            // Примечания
            $table->text('notes')->nullable();

            // Кто внёс результат
            $table->foreignId('entered_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_results');
    }
};