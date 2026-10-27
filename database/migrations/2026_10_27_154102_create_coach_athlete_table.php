<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_athlete', function (Blueprint $table) {
            $table->id();

            $table->foreignId('coach_id')
                ->constrained('coaches')
                ->cascadeOnDelete();

            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->cascadeOnDelete();

            $table->foreignId('discipline_id')
                ->constrained('disciplines')
                ->cascadeOnDelete();

            $table->string('status', 20)
                ->default('pending');

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();

            $table->unique([
                'coach_id',
                'athlete_id',
                'discipline_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_athlete');
    }
};