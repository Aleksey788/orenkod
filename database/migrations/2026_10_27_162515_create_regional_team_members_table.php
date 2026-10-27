<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regional_team_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('regional_team_id')
                ->constrained('regional_teams')
                ->cascadeOnDelete();

            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->restrictOnDelete();

            // Дата включения в сборную
            $table->date('included_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique([
                'regional_team_id',
                'athlete_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regional_team_members');
    }
};