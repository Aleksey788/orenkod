<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Judge;
use App\Models\JudgeAssignment;

class JudgeController extends Controller
{
    // 1. Посмотреть список судей и их категорий
    public function index(Request $request)
    {
        $judges = Judge::all();

        return response()->json([
            'success' => true,
            'data'    => $judges
        ], 200);
    }

    // 2. Назначить судью на соревнование (главный судья, секретарь, линейный судья)
    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'competition_id' => 'required|integer',
            'judge_id'       => 'required|integer',
            'position'       => 'required|string|max:100', // например: "Главный судья", "Секретарь"
        ]);

        $assignment = JudgeAssignment::create([
            'competition_id' => $validated['competition_id'],
            'judge_id'       => $validated['judge_id'],
            'position'       => $validated['position'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Судья успешно назначен на соревнование.',
            'data'    => $assignment
        ], 201);
    }

    // 3. Одобрить или отклонить заявку судьи на подтверждение/повышение категории (федерация)[cite: 1]
    public function updateJudgeApplication(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,pending',
        ]);

        $judge = Judge::find($id);

        if (!$judge) {
            return response()->json([
                'success' => false,
                'message' => 'Судья или заявка не найдены.'
            ], 404);
        }

        $judge->update([
            'category_status' => $validated['status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Статус судейской категории успешно обновлен.',
            'data'    => $judge
        ], 200);
    }
}