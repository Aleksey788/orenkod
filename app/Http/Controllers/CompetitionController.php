<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Competition;

class CompetitionController extends Controller
{
    // 1. Получить список всех соревнований с фильтрацией (по дисциплине или формату)
    public function index(Request $request)
    {
        $query = Competition::query();

        if ($request->has('discipline_id')) {
            $query->where('discipline_id', $request->discipline_id);
        }

        if ($request->has('format')) {
            $query->where('format', $request->format);
        }

        $competitions = $query->with(['discipline'])->get();

        return response()->json([
            'success' => true,
            'data'    => $competitions
        ], 200);
    }

    // 2. Посмотреть подробную информацию о конкретном соревновании
    public function show($id)
    {
        $competition = Competition::with(['discipline'])->find($id);

        if (!$competition) {
            return response()->json([
                'success' => false,
                'message' => 'Соревнование не найдено.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $competition
        ], 200);
    }

    // 3. Создать новое соревнование (для членов федерации)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'discipline_id'          => 'required|integer|exists:disciplines,id',
            'competition_type_id'    => 'required|integer|exists:competition_types,id',
            'age_group_id'           => 'required|integer|exists:age_groups,id',
            'level'                  => 'required|string',
            'format'                 => 'required|string',
            'venue'                  => 'required|string',
            'starts_at'              => 'required|date',
            'ends_at'                => 'required|date|after_or_equal:starts_at',
            'registration_starts_at' => 'nullable|date',
            'registration_ends_at'   => 'nullable|date',
            'calendar_plan'          => 'nullable|string',
            'calendar_plan_number'   => 'nullable|string',
        ]);

        $validated['created_by_user_id'] = $request->user()->id;
        $validated['status'] = 'published';

        $competition = Competition::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Соревнование успешно создано.',
            'data'    => $competition
        ], 201);
    }

    // 4. Обновить соревнование
    public function update(Request $request, $id)
    {
        $competition = Competition::find($id);

        if (!$competition) {
            return response()->json([
                'success' => false,
                'message' => 'Соревнование не найдено.'
            ], 404);
        }

        $validated = $request->validate([
            'title'           => 'sometimes|string|max:255',
            'discipline_id'   => 'sometimes|integer|exists:disciplines,id',
            'format'          => 'sometimes|in:очно,онлайн,смешанно',
            'start_date'      => 'sometimes|date',
            'end_date'        => 'nullable|date',
            'venue'           => 'sometimes|string|max:255',
            'calendar_number' => 'nullable|string|max:100',
        ]);

        $competition->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Соревнование успешно обновлено.',
            'data'    => $competition
        ], 200);
    }

    // 5. Удалить соревнование
    public function destroy($id)
    {
        $competition = Competition::find($id);

        if (!$competition) {
            return response()->json([
                'success' => false,
                'message' => 'Соревнование не найдено.'
            ], 404);
        }

        $competition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Соревнование успешно удалено.'
        ], 200);
    }
}