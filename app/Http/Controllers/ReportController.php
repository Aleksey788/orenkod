<?php

namespace App\Http\Controllers;

use App\DTO\Athlete\AthleteFilterDTO;
use App\Http\Requests\Athlete\AthleteFilterRequest;
use App\Http\Resources\AthleteResource;
use App\Services\AthleteService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportController extends Controller
{
    public function __construct(private AthleteService $athletes, private ReportService $reports) {}

    public function athletesRegistry(AthleteFilterRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();
        $filters = new AthleteFilterDTO($data);

        return AthleteResource::collection($this->athletes->query($filters)->with(AthleteService::RELATIONS)
            ->orderBy('id')->paginate($data['per_page'] ?? 15)->withQueryString())
            ->additional(['statistics' => $this->reports->statistics($filters)]);
    }

    public function statistics(AthleteFilterRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->reports->statistics(new AthleteFilterDTO($request->validated()))]);
    }
}
