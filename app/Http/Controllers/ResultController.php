<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompetitionResult;
use App\Models\Athlete;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    // 1. Получить результаты конкретного соревнования (для протокола)
    public function getByCompetition($competitionId)
    {
        $results = CompetitionResult::where('competition_id', $competitionId)
            ->with(['athlete.user'])
            ->orderBy('place', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $results
        ], 200);
    }

    // 2. Внести или обновить результаты соревнования (для судьи или члена федерации)
    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'competition_id' => 'required|integer|exists:competitions,id',
            'results'        => 'required|array',
            'results.*.athlete_id' => 'required|integer|exists:athletes,id',
            'results.*.place'      => 'required|integer|min:1',
            'results.*.points'     => 'required|numeric|min:0',
            'results.*.score'      => 'nullable|string|max:100', // например: "100 баллов", "10/10 задач"
        ]);

        $competitionId = $validated['competition_id'];

        DB::transaction(function () use ($competitionId, $validated) {
            foreach ($validated['results'] as $res) {
                CompetitionResult::updateOrCreate(
                    [
                        'competition_id' => $competitionId,
                        'athlete_id'     => $res['athlete_id'],
                    ],
                    [
                        'place'  => $res['place'],
                        'points' => $res['points'],
                        'score'  => $res['score'] ?? null,
                    ]
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Результаты соревнования успешно внесены и сохранены.'
        ], 200);
    }

    // 3. Автоматический расчет рейтинга спортсменов (Топ лучших по сумме баллов)
    public function getLeaderboard(Request $request)
    {
        $limit = $request->get('limit', 10);

        $leaderboard = CompetitionResult::select(
                'athlete_id',
                DB::raw('SUM(points) as total_points'),
                DB::raw('COUNT(competition_id) as total_competitions')
            )
            ->groupBy('athlete_id')
            ->orderByDesc('total_points')
            ->with(['athlete.user', 'athlete.organization'])
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $leaderboard
        ], 200);
    }
}