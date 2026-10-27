<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('athlete_ranks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->restrictOnDelete();

            $table->foreignId('sport_rank_id')
                ->constrained('sport_ranks')
                ->restrictOnDelete();

            $table->foreignId('discipline_id')  
                ->constrained('disciplines')
                ->restrictOnDelete();

            // Одобренная заявка
            $table->foreignId('rank_application_id')
                ->unique()
                ->constrained('rank_applications')
                ->restrictOnDelete();

            // Дата присвоения
            $table->date('awarded_at');

            // Срок действия
            $table->date('expires_at')
                ->nullable();

            // Приказ
            $table->string('order_number', 100);
            $table->date('order_date');

            $table->string('order_path', 500)
                ->nullable();

            $table->timestamps();

            $table->index(['athlete_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('athlete_ranks');
    }
};