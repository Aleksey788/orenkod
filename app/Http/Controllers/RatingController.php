<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Result;

class RatingController extends Controller
{
    // 1. Получить актуальный рейтинг спортсменов (для таблицы на фронтенде)
    public function index(Request $request)
    {
        // Суммируем баллы спортсменов по всем результатам и сортируем по убыванию
        $ratings = Result::select('athlete_id')
            ->selectRaw('SUM(points) as total_points, COUNT(competition_id) as competitions_count')
            ->groupBy('athlete_id')
            ->orderByDesc('total_points')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $ratings
        ], 200);
    }

    // 2. Сформировать проект приказа о включении в состав сборной региона
    public function generateTeamOrder(Request $request)
    {
        $validated = $request->validate([
            'discipline_id' => 'required|integer',
            'limit'         => 'nullable|integer', // количество человек в сборной (по умолчанию топ-10)
        ]);

        $limit = $validated['limit'] ?? 10;

        // Выбираем топ спортсменов с наибольшей суммой баллов в выбранной дисциплине
        $topAthletes = Result::where('discipline_id', $validated['discipline_id'])
            ->select('athlete_id')
            ->selectRaw('SUM(points) as total_points')
            ->groupBy('athlete_id')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Проект приказа о составе сборной успешно сформирован.',
            'data'    => [
                'discipline_id' => $validated['discipline_id'],
                'candidates'    => $topAthletes,
                'generated_at'  => now()->toDateTimeString(),
            ]
        ], 200);
    }
}