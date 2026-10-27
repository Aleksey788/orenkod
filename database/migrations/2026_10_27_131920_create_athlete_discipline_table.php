<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('athlete_discipline', function (Blueprint $table) {

            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->cascadeOnDelete();

            $table->foreignId('discipline_id')
                ->constrained('disciplines')
                ->cascadeOnDelete();

            $table->primary([
                'athlete_id',
                'discipline_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('athlete_discipline');
    }
};