<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_data_consents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Кто даёт согласие
            $table->string('consent_type', 30);

            // Версия текста согласия
            $table->string('consent_version', 30);

            // Дата согласия
            $table->timestamp('agreed_at');

            // Дата отзыва
            $table->timestamp('revoked_at')->nullable();

            // Законный представитель
            $table->string('guardian_full_name', 255)
                ->nullable();

            $table->string('guardian_relationship', 100)
                ->nullable();

            // Подтверждающий документ
            $table->string('document_path', 500)
                ->nullable();

            $table->ipAddress('ip_address')
                ->nullable();

            $table->timestamps();

            $table->index([
                'user_id',
                'consent_type',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_data_consents');
    }
};