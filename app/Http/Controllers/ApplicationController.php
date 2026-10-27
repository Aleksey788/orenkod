<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class ApplicationController extends Controller
{
    // 1. Получить список заявок (с фильтрами по соревнованию, спортсмену или статусу)
    public function index(Request $request)
    {
        $query = Application::query();

        if ($request->has('competition_id')) {
            $query->where('competition_id', $request->competition_id);
        }

        if ($request->has('athlete_id')) {
            $query->where('athlete_id', $request->athlete_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->with(['competition', 'athlete'])->get();

        return response()->json([
            'success' => true,
            'data'    => $applications
        ], 200);
    }

    // 2. Подать заявку на соревнование (Спортсмен)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'competition_id' => 'required|integer|exists:competitions,id',
            'athlete_id'     => 'required|integer|exists:athletes,id',
        ]);

        // Проверяем, не подавал ли этот спортсмен заявку на этот же турнир ранее
        $exists = Application::where('competition_id', $validated['competition_id'])
            ->where('athlete_id', $validated['athlete_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Вы уже подали заявку на это соревнование.'
            ], 422);
        }

        $application = Application::create([
            'competition_id' => $validated['competition_id'],
            'athlete_id'     => $validated['athlete_id'],
            'status'         => 'pending', // Статус по умолчанию: на рассмотрении
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Заявка успешно подана и отправлена на модерацию.',
            'data'    => $application
        ], 201);
    }

    // 3. Изменить статус заявки (Одобрить или отклонить — для Федерации)
    public function updateStatus(Request $request, $id)
    {
        $application = Application::find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Заявка не найдена.'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,pending',
        ]);

        $application->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Статус заявки успешно обновлен.',
            'data'    => $application
        ], 200);
    }
}