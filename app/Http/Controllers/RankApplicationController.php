<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RankApplication;

class RankApplicationController extends Controller
{
    // 1. Получить список заявок на разряды
    public function index(Request $request)
    {
        $query = RankApplication::query();

        if ($request->has('athlete_id')) {
            $query->where('athlete_id', $request->athlete_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->with(['athlete', 'sportRank', 'discipline', 'competitionResult'])->get();

        return response()->json([
            'success' => true,
            'data'    => $applications
        ], 200);
    }

    // 2. Подать заявку на присвоение спортивного разряда
    public function store(Request $request)
    {
        $validated = $request->validate([
            'athlete_id'            => 'required|integer|exists:athletes,id',
            'sport_rank_id'         => 'required|integer|exists:sport_ranks,id',
            'discipline_id'         => 'required|integer|exists:disciplines,id',
            'competition_result_id' => 'required|integer|exists:competition_results,id',
            'request_type'          => 'nullable|string|max:30',
        ]);

        $rankApp = RankApplication::create([
            'athlete_id'            => $validated['athlete_id'],
            'sport_rank_id'         => $validated['sport_rank_id'],
            'discipline_id'         => $validated['discipline_id'],
            'competition_result_id' => $validated['competition_result_id'],
            'submitted_by_user_id'  => $request->user()->id,
            'request_type'          => $validated['request_type'] ?? 'assignment',
            'status'                => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Заявка на спортивный разряд успешно подана.',
            'data'    => $rankApp
        ], 201);
    }

    // 3. Изменить статус заявки (Федерация)
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
            'status'           => 'required|in:approved,rejected,pending',
            'rejection_reason' => 'nullable|string',
        ]);

        $rankApp->update([
            'status'               => $validated['status'],
            'rejection_reason'     => $validated['rejection_reason'] ?? null,
            'reviewed_by_user_id'  => $request->user()->id,
            'reviewed_at'          => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Статус заявки на разряд успешно обновлен.',
            'data'    => $rankApp
        ], 200);
    }
}