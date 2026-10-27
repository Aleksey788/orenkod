<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;

class OrganizationController extends Controller
{
    // 1. Получить список всех спортивных организаций (для справочников и выпадающих списков)
    public function index(Request $request)
    {
        $organizations = Organization::all();

        return response()->json([
            'success' => true,
            'data'    => $organizations
        ], 200);
    }

    // 2. Создать новую организацию (добавление членом федерации)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'type'         => 'required|string|max:100', // например: "СШОР", "Клуб", "Федерация"
            'municipality' => 'required|string|max:150',
            'address'      => 'nullable|string|max:255',
        ]);

        $organization = Organization::create([
            'name'         => $validated['name'],
            'type'         => $validated['type'],
            'municipality' => $validated['municipality'],
            'address'      => $validated['address'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Спортивная организация успешно добавлена.',
            'data'    => $organization
        ], 201);
    }

    // 3. Посмотреть информацию о конкретной организации по ID
    public function show($id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Организация не найдена.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $organization
        ], 200);
    }
}