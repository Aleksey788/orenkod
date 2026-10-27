<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('age_groups', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100)->unique();

            $table->unsignedSmallInteger('min_age');
            $table->unsignedSmallInteger('max_age')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('age_groups');
    }
};