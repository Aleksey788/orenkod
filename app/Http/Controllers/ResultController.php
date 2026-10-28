<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompetitionResult;
use App\Models\Athlete;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    // 1. Получить результаты конкретного соревнования (официальный протокол)
    public function getByCompetition($competitionId)
    {
        $results = CompetitionResult::whereHas('application', function ($query) use ($competitionId) {
            $query->where('competition_id', $competitionId);
        })->with(['application.athlete.user', 'application.athlete.organization'])
          ->orderBy('place', 'asc')
          ->get();

        return response()->json([
            'success' => true,
            'data'    => $results
        ], 200);
    }

    // 2. Внести или обновить результат участника (Федерация / Судья)
    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'competition_application_id' => 'required|integer|exists:competition_applications,id',
            'place'                      => 'nullable|integer|min:1',
            'score'                      => 'nullable|numeric',
            'status'                     => 'nullable|string|max:30',
            'notes'                      => 'nullable|string',
        ]);

        $result = CompetitionResult::updateOrCreate(
            ['competition_application_id' => $validated['competition_application_id']],
            [
                'place'              => $validated['place'] ?? null,
                'score'              => $validated['score'] ?? null,
                'status'             => $validated['status'] ?? 'ranked',
                'notes'              => $validated['notes'] ?? null,
                'entered_by_user_id' => $request->user()?->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Результат успешно сохранен.',
            'data'    => $result
        ], 200);
    }

    // 3. Автоматический расчет общего лидерборда (Топ лучших по сумме баллов)
    public function getLeaderboard(Request $request)
    {
        $limit = $request->get('limit', 10);

        $leaderboard = Athlete::select(
                'athletes.*',
                DB::raw('SUM(competition_results.score) as total_points'),
                DB::raw('COUNT(competition_applications.id) as total_competitions')
            )
            ->join('competition_applications', 'athletes.id', '=', 'competition_applications.athlete_id')
            ->join('competition_results', 'competition_applications.id', '=', 'competition_results.competition_application_id')
            ->where('competition_applications.status', 'approved')
            ->groupBy('athletes.id')
            ->orderByDesc('total_points')
            ->with(['user', 'organization'])
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $leaderboard
        ], 200);
    }
}