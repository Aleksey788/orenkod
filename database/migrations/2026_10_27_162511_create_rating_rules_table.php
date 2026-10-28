<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rating_season_id')
                ->constrained('rating_seasons')
                ->cascadeOnDelete();

            // Категория соревнования
            $table->string('category', 50);

            // Занятое место
            $table->unsignedSmallInteger('place');

            // Рейтинговые баллы
            $table->unsignedInteger('points');

            $table->timestamps();

            $table->unique([
                'rating_season_id',
                'category',
                'place',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating_rules');
    }
};