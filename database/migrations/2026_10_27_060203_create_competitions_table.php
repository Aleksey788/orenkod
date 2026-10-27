<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('title');                                    // Название соревнования
            $table->unsignedBigInteger('discipline_id');                // ID дисциплины
            $table->unsignedBigInteger('type_id');                      // ID типа соревнования
            $table->unsignedBigInteger('level_id');                     // ID уровня соревнования
            $table->enum('format', ['очно', 'онлайн', 'смешанно']);     // Формат
            $table->date('start_date');                                 // Дата начала
            $table->date('end_date');                                   // Дата окончания
            $table->string('venue');                                    // Место проведения
            $table->string('age_group');                                // Возрастная группа
            $table->string('regulation_file')->nullable();              // Положение (файл), может быть пустым
            $table->enum('calendar_plan_type', ['ЕКП Минспорта РФ', 'Календарный план Оренбургской области']);
            $table->string('calendar_number');                          // Номер в плане
            $table->boolean('registration_open')->default(true);        // Открыта ли регистрация
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
