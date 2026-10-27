<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Athlete;

class AthleteController extends Controller
{
    // 1. Получить список всех профилей спортсменов (с дисциплинами и организацией)
    public function index(Request $request)
    {
        $athletes = Athlete::with(['user', 'organization', 'disciplines'])->get();

        return response()->json([
            'success' => true,
            'data'    => $athletes
        ], 200);
    }

    // 2. Посмотреть профиль конкретного спортсмена по ID
    public function show($id)
    {
        $athlete = Athlete::with(['user', 'organization', 'disciplines'])->find($id);

        if (!$athlete) {
            return response()->json([
                'success' => false,
                'message' => 'Профиль спортсмена не найден.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $athlete
        ], 200);
    }

    // 3. Создать или привязать профиль спортсмена к аккаунту (таблица athletes)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'         => 'required|integer|exists:users,id',
            'organization_id' => 'nullable|integer|exists:organizations,id',
            'birth_date'      => 'required|date',
            'municipality'    => 'required|string|max:150',
            'start_year'      => 'required|integer|min:1900|max:' . date('Y'),
            'training_stage'  => 'required|string|max:100', // например: "ЭНП", "ТЭ"
            'discipline_ids'  => 'nullable|array', // массив ID дисциплин
            'discipline_ids.*'=> 'exists:disciplines,id',
        ]);

        // Создаем запись в таблице athletes
        $athlete = Athlete::create([
            'user_id'         => $validated['user_id'],
            'organization_id' => $validated['organization_id'] ?? null,
            'birth_date'      => $validated['birth_date'],
            'municipality'    => $validated['municipality'],
            'start_year'      => $validated['start_year'],
            'training_stage'  => $validated['training_stage'],
        ]);

        // Привязываем выбранные дисциплины (таблица athlete_discipline)
        if (!empty($validated['discipline_ids'])) {
            $athlete->disciplines()->attach($validated['discipline_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Профиль спортсмена успешно создан.',
            'data'    => $athlete->load(['disciplines', 'organization'])
        ], 201);
    }

    // 4. Обновить профиль спортсмена и его дисциплины
    public function update(Request $request, $id)
    {
        $athlete = Athlete::find($id);

        if (!$athlete) {
            return response()->json([
                'success' => false,
                'message' => 'Профиль спортсмена не найден.'
            ], 404);
        }

        $validated = $request->validate([
            'organization_id' => 'nullable|integer|exists:organizations,id',
            'birth_date'      => 'nullable|date',
            'municipality'    => 'nullable|string|max:150',
            'start_year'      => 'nullable|integer|min:1900|max:' . date('Y'),
            'training_stage'  => 'nullable|string|max:100',
            'discipline_ids'  => 'nullable|array',
            'discipline_ids.*'=> 'exists:disciplines,id',
        ]);

        $athlete->update(array_filter($validated));

        // Синхронизируем дисциплины (если передан массив)
        if (isset($validated['discipline_ids'])) {
            $athlete->disciplines()->sync($validated['discipline_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Профиль спортсмена успешно обновлен.',
            'data'    => $athlete->load(['disciplines', 'organization'])
        ], 200);
    }
}