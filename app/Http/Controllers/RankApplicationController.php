<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RankApplication;

class RankApplicationController extends Controller
{
    // 1. Посмотреть список всех заявок на разряды (для федерации или отчетов)
    public function index(Request $request)
    {
        $applications = RankApplication::all();

        return response()->json([
            'success' => true,
            'data' => $applications
        ], 200);
    }

    // 2. Подать заявку на присвоение/подтверждение спортивного разряда (спортсмен / тренер)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'athlete_id'     => 'required|integer',
            'rank_name'      => 'required|string|max:100', // например: "1 разряд", "КМС", "МС"
            'competition_id' => 'required|integer', // соревнование-основание
            'document_path'  => 'nullable|string', // скан протокола или справки
        ]);

        $application = RankApplication::create([
            'athlete_id'     => $validated['athlete_id'],
            'rank_name'      => $validated['rank_name'],
            'competition_id' => $validated['competition_id'],
            'document_path'  => $validated['document_path'] ?? null,
            'status'         => 'pending', // статус по умолчанию: на рассмотрении
            'submitted_date' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Заявка на спортивный разряд успешно подана.',
            'data'    => $application
        ], 201);
    }

    // 3. Одобрить или отклонить заявку на разряд
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,pending',
        ]);

        $application = RankApplication::find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Заявка на разряд не найдена.'
            ], 404);
        }

        $application->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Статус заявки успешно изменен.',
            'data'    => $application
        ], 200);
    }
}