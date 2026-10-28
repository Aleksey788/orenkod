<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judge_category_awards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('judge_id')
                ->constrained('judges')
                ->restrictOnDelete();

            $table->foreignId('judge_category_id')
                ->constrained('judge_categories')
                ->restrictOnDelete();

            $table->foreignId('judge_category_application_id')
                ->unique()
                ->constrained('judge_category_applications')
                ->restrictOnDelete();

            $table->date('awarded_at');

            $table->date('expires_at')
                ->nullable();

            $table->string('order_number', 100);
            $table->date('order_date');

            $table->string('order_path', 500)
                ->nullable();

            $table->timestamps();

            $table->index(['judge_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judge_category_awards');
    }
};