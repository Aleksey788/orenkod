<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JudgeAssignment;

class JudgeController extends Controller
{
    // 1. Назначить судью на соревнование
    public function store(Request $request)
    {
        $validated = $request->validate([
            'competition_id' => 'required|integer|exists:competitions,id',
            'judge_id'       => 'required|integer|exists:users,id',
            'position'       => 'required|string|max:100', // например: "Главный судья", "Арбитр"
        ]);

        $assignment = JudgeAssignment::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Судья успешно назначен на соревнование.',
            'data'    => $assignment
        ], 201);
    }

    // 2. Посмотреть список судей конкретного турнира
    public function getByCompetition($competitionId)
    {
        $judges = JudgeAssignment::where('competition_id', $competitionId)
            ->with(['judge'])
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $judges
        ], 200);
    }
}