<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Athlete;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    // 1. Получить актуальный рейтинг спортсменов
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);

        $ratings = Athlete::select(
                'athletes.*',
                DB::raw('SUM(competition_results.score) as total_points'),
                DB::raw('COUNT(competition_applications.id) as competitions_count')
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
            'data'    => $ratings
        ], 200);
    }

    // 2. Сформировать проект приказа о включении в состав сборной региона
    public function generateTeamOrder(Request $request)
    {
        $validated = $request->validate([
            'discipline_id' => 'required|integer',
            'limit'         => 'nullable|integer',
        ]);

        $limit = $validated['limit'] ?? 10;

        $topAthletes = Athlete::select(
                'athletes.*',
                DB::raw('SUM(competition_results.score) as total_points')
            )
            ->join('competition_applications', 'athletes.id', '=', 'competition_applications.athlete_id')
            ->join('competition_results', 'competition_applications.id', '=', 'competition_results.competition_application_id')
            ->join('competitions', 'competition_applications.competition_id', '=', 'competitions.id')
            ->where('competitions.discipline_id', $validated['discipline_id'])
            ->where('competition_applications.status', 'approved')
            ->groupBy('athletes.id')
            ->orderByDesc('total_points')
            ->with(['user', 'organization'])
            ->take($limit)
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