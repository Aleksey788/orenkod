<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating_seasons', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->unsignedSmallInteger('year')->unique();

            $table->date('starts_on');
            $table->date('ends_on');

            // Коэффициент стабильности
            $table->decimal('stability_coefficient', 4, 2)
                ->default(1.20);

            // Минимум призовых результатов
            $table->unsignedSmallInteger('min_podium_results')
                ->default(2);

            $table->boolean('is_active')
                ->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating_seasons');
    }
};