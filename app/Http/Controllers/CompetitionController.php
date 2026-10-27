<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Здесь потом подключим модель соревнования, когда партнер ее скинет:
// use App\Models\Competition;

class CompetitionController extends Controller
{
    /**
     * Создание нового соревнования федерацией
     */
    public function store(Request $request)
    {
        // 1. ШАГ: ВАЛИДАЦИЯ (Охранник проверяет данные)
        $validated = $request->validate([
            'title' => 'required|string|max:255',       // Название обязательно, текст, до 255 символов
            'discipline_id' => 'required|integer',      // ID дисциплины обязателен (число)
            'format' => 'required|in:очно,онлайн,смешанно', // Только один из трех вариантов
            'start_date' => 'required|date',            // Дата начала
            'end_date' => 'required|date|after_or_equal:start_date', // Дата окончания (не раньше начала)
            'venue' => 'required|string',               // Место проведения
            'calendar_number' => 'required|string',     // Номер в календарном плане
        ]);

        // 2. ШАГ: СОХРАНЕНИЕ В БАЗУ ДАННЫХ
        // Когда твой партнер сделает миграцию и модель Competition, 
        // эта строчка будет сохранять соревнование в базу одной командой:
        // $competition = Competition::create($validated);

        // 3. ШАГ: ОТВЕТ КЛИЕНТУ (Фронтенду)
        return response()->json([
            'message' => 'Соревнование успешно создано и прошло валидацию!',
            'data' => $validated // возвращаем то, что проверили (для проверки)
        ], 201);
    }
}