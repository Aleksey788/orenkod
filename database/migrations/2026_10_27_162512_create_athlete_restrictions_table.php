<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('athlete_restrictions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->restrictOnDelete();

            // Дисквалификация или отстранение
            $table->string('type', 30);

            $table->text('reason')->nullable();

            $table->date('starts_on');

            $table->date('ends_on')->nullable();

            // Досрочная отмена ограничения
            $table->timestamp('revoked_at')->nullable();

            $table->string('decision_number', 100)
                ->nullable();

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'athlete_id',
                'starts_on',
                'ends_on',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('athlete_restrictions');
    }
};