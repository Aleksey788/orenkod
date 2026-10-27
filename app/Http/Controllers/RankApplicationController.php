<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RankApplication;

class RankApplicationController extends Controller
{
    // 1. Получить список заявок на разряды (с фильтрами по спортсмену или статусу)
    public function index(Request $request)
    {
        $query = RankApplication::query();

        if ($request->has('athlete_id')) {
            $query->where('athlete_id', $request->athlete_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->with(['athlete'])->get();

        return response()->json([
            'success' => true,
            'data'    => $applications
        ], 200);
    }

    // 2. Подать заявку на присвоение спортивного разряда (Спортсмен / Тренер)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'athlete_id'       => 'required|integer|exists:athletes,id',
            'rank_name'        => 'required|string|max:100', // например: "1 спортивный разряд", "КМС"
            'competition_name' => 'nullable|string|max:255', // турнир, на котором выполнен нормативы
            'document_url'     => 'nullable|string|max:255', // ссылка на протокол / подтверждающий документ
        ]);

        $rankApp = RankApplication::create([
            'athlete_id'       => $validated['athlete_id'],
            'rank_name'        => $validated['rank_name'],
            'competition_name' => $validated['competition_name'] ?? null,
            'document_url'     => $validated['document_url'] ?? null,
            'status'           => 'pending', // на рассмотрении
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Заявка на спортивный разряд успешно подана.',
            'data'    => $rankApp
        ], 201);
    }

    // 3. Изменить статус заявки и указать номер приказа (Федерация)
    public function updateStatus(Request $request, $id)
    {
        $rankApp = RankApplication::find($id);

        if (!$rankApp) {
            return response()->json([
                'success' => false,
                'message' => 'Заявка не найдена.'
            ], 404);
        }

        $validated = $request->validate([
            'status'       => 'required|in:approved,rejected,pending',
            'order_number' => 'nullable|string|max:100', // Номер приказа о присвоении разряда
        ]);

        $rankApp->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Статус заявки на разряд успешно обновлен.',
            'data'    => $rankApp
        ], 200);
    }
}