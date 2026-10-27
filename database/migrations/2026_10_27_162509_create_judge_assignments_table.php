<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judge_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('judge_id')
                ->constrained('judges')
                ->restrictOnDelete();

            $table->foreignId('competition_id')
                ->constrained('competitions')
                ->cascadeOnDelete();

            // Судейская должность
            $table->string('position', 100);

            // Кто назначил судью
            $table->foreignId('assigned_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique([
                'judge_id',
                'competition_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judge_assignments');
    }
};