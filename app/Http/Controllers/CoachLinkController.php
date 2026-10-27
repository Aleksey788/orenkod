<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CoachAthlete;

class CoachLinkController extends Controller
{
    // 1. Посмотреть список всех связок «тренер — спортсмен»
    public function index(Request $request)
    {
        $links = CoachAthlete::all();

        return response()->json([
            'success' => true,
            'data' => $links
        ], 200);
    }

    // 2. Тренер отправляет запрос на привязку спортсмена
    public function store(Request $request)
    {
        $validated = $request->validate([
            'coach_id'      => 'required|integer',
            'athlete_id'    => 'required|integer',
            'discipline_id' => 'required|integer',
        ]);

        // Проверяем, не отправлялся ли уже такой запрос
        $existing = CoachAthlete::where('coach_id', $validated['coach_id'])
            ->where('athlete_id', $validated['athlete_id'])
            ->where('discipline_id', $validated['discipline_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Запрос на привязку к этому спортсмену в данной дисциплине уже существует.'
            ], 422);
        }

        $link = CoachAthlete::create([
            'coach_id'      => $validated['coach_id'],
            'athlete_id'    => $validated['athlete_id'],
            'discipline_id' => $validated['discipline_id'],
            'status'        => 'pending', // статус по умолчанию: ожидает подтверждения спортсменом
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Запрос тренера успешно отправлен.',
            'data'    => $link
        ], 201);
    }

    // 3. Спортсмен подтверждает или отклоняет запрос тренера
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,rejected,terminated',
        ]);

        $link = CoachAthlete::find($id);

        if (!$link) {
            return response()->json([
                'success' => false,
                'message' => 'Связь не найдена.'
            ], 404);
        }

        $link->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Статус связи успешно обновлен.',
            'data'    => $link
        ], 200);
    }
}