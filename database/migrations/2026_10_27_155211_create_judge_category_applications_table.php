<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judge_category_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('judge_id')
                ->constrained('judges')
                ->restrictOnDelete();

            $table->foreignId('judge_category_id')
                ->constrained('judge_categories')
                ->restrictOnDelete();

            $table->foreignId('submitted_by_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Присвоение или подтверждение
            $table->string('request_type', 30)
                ->default('assignment');

            $table->string('status', 30)
                ->default('pending');

            // Подтверждающий документ
            $table->string('evidence_path', 500)
                ->nullable();

            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')
                ->nullable();

            $table->text('rejection_reason')
                ->nullable();

            $table->timestamps();

            $table->index(['judge_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judge_category_applications');
    }
};