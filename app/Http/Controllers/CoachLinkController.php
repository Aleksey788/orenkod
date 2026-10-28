<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CoachAthlete;

class CoachLinkController extends Controller
{
    // 1. Подать запрос на закрепление тренера за спортсменом
    public function store(Request $request)
    {
        $validated = $request->validate([
            'coach_id'      => 'required|integer|exists:coaches,id',
            'athlete_id'    => 'required|integer|exists:athletes,id',
            'discipline_id' => 'required|integer|exists:disciplines,id', // Требуем дисциплину
            'status'        => 'nullable|in:pending,active,rejected',
        ]);

        $link = CoachAthlete::create([
            'coach_id'      => $validated['coach_id'],
            'athlete_id'    => $validated['athlete_id'],
            'discipline_id' => $validated['discipline_id'],
            'status'        => $validated['status'] ?? 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Запрос на привязку тренера успешно отправлен.',
            'data'    => $link
        ], 201);
    }

    // 2. Подтвердить или отклонить связь
    public function updateStatus(Request $request, $id)
    {
        $link = CoachAthlete::find($id);

        if (!$link) {
            return response()->json([
                'success' => false,
                'message' => 'Связь не найдена.'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:active,rejected,pending',
        ]);

        $link->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Статус связи успешно обновлен.',
            'data'    => $link
        ], 200);
    }
}