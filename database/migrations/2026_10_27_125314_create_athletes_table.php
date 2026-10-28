<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('athletes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('organization_id')
                ->nullable()
                ->constrained('organizations')
                ->nullOnDelete();

            $table->date('birth_date');

            $table->string('municipality', 150);

            $table->smallInteger('start_year');

            $table->string('training_stage', 100)
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('athletes');
    }
};