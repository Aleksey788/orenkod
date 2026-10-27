<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class ApplicationController extends Controller
{
    // 1. Посмотреть список всех заявок 
    public function index(Request $request)
    {
        $query = Application::query();

        // Если передан фильтр по спортсмену (?athlete_id=5)
        if ($request->has('athlete_id')) {
            $query->where('athlete_id', $request->athlete_id);
        }

        // Если передан фильтр по статусу (?status=pending)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->get();

        return response()->json([
            'success' => true,
            'data' => $applications
        ], 200);
    }

    // 2. Подать новую заявку на соревнование 
    public function store(Request $request)
    {
        $validated = $request->validate([
            'athlete_id'     => 'required|integer',
            'competition_id' => 'required|integer',
        ]);

        // Проверяем, не была ли заявка подана ранее
        $existing = Application::where('athlete_id', $validated['athlete_id'])
            ->where('competition_id', $validated['competition_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Заявка на это соревнование уже подана.'
            ], 422);
        }

        $application = Application::create([
            'athlete_id'     => $validated['athlete_id'],
            'competition_id' => $validated['competition_id'],
            'status'         => 'pending', // Статус по умолчанию: на рассмотрении
            'submitted_date' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Заявка успешно создана.',
            'data'    => $application
        ], 201);
    }

    // 3. Изменить статус заявки
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,pending',
        ]);

        $application = Application::find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Заявка не найдена.'
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