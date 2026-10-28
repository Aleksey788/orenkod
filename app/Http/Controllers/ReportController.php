<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Athlete;
use App\Models\Organization;

class ReportController extends Controller
{
    // Реестр занимающихся с фильтрами и статистикой (Функция №6)
    public function athletesRegistry(Request $request)
    {
        // Базовый запрос с подгрузкой пользователя и организации
        $query = Athlete::with(['user', 'organization']);

        // Фильтр по муниципалитету (если передан)
        if ($request->has('municipality')) {
            $query->where('municipality', $request->municipality);
        }

        // Фильтр по спортивной организации / школе
        if ($request->has('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // Фильтр по этапу подготовки (например, НП-1, УТГ-2)
        if ($request->has('training_stage')) {
            $query->where('training_stage', $request->training_stage);
        }

        $athletes = $query->get();

        // Считаем простую статистику численности
        $totalCount = $athletes->count();
        $byStage = $athletes->groupBy('training_stage')->map->count();

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_athletes' => $totalCount,
                'by_training_stage' => $byStage
            ],
            'data' => $athletes
        ], 200);
    }
}