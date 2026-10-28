<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->string('title', 255);

            // Тип документа
            $table->string('kind', 50);

            // Информация о файле
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('mime_type', 100)->nullable();

            // Связь с соревнованием
            $table->foreignId('competition_id')
                ->nullable()
                ->constrained('competitions')
                ->nullOnDelete();

            // Связь со спортсменом
            $table->foreignId('athlete_id')
                ->nullable()
                ->constrained('athletes')
                ->nullOnDelete();

            // Связь с судьёй
            $table->foreignId('judge_id')
                ->nullable()
                ->constrained('judges')
                ->nullOnDelete();

            // Связь со сборной
            $table->foreignId('regional_team_id')
                ->nullable()
                ->constrained('regional_teams')
                ->nullOnDelete();

            // Кто создал документ
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('is_public')
                ->default(false);

            $table->timestamp('published_at')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};