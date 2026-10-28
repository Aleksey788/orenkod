<?php

namespace App\Http\Controllers;

use App\DTO\Rating\RatingFilterDTO;
use App\Http\Requests\Rating\RatingFilterRequest;
use App\Http\Resources\RatingDetailResource;
use App\Http\Resources\RatingResource;
use App\Models\Athlete;
use App\Models\Discipline;
use App\Models\RatingSeason;
use App\Services\Rating\RatingCalculationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RatingController extends Controller
{
    public function __construct(private RatingCalculationService $ratings) {}

    public function index(RatingFilterRequest $request, ?RatingSeason $season = null, ?Discipline $discipline = null): AnonymousResourceCollection
    {
        $data = $request->validated();
        $season ??= RatingSeason::findOrFail($data['season_id']);
        $discipline ??= Discipline::findOrFail($data['discipline_id']);
        abort_if($season->status === 'draft', 404);
        $rows = $this->ratings->calculate($season, $discipline, RatingFilterDTO::fromArray($data));

        return RatingResource::collection($rows)->additional(['meta' => [
            'season_id' => $season->id, 'discipline_id' => $discipline->id, 'rating_version' => $season->rating_version,
            'mode' => $season->status === 'closed' ? 'snapshot' : 'live',
        ]]);
    }

    public function detail(RatingFilterRequest $request, RatingSeason $season, Discipline $discipline, Athlete $athlete): RatingDetailResource
    {
        abort_if($season->status === 'draft', 404);
        $row = $this->ratings->calculate($season, $discipline, RatingFilterDTO::fromArray($request->validated()))->firstWhere('athlete_id', $athlete->id);
        abort_if($row === null, 404);

        return new RatingDetailResource($row);
    }

    public function mine(RatingFilterRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();
        $season = RatingSeason::findOrFail($data['season_id']);
        abort_if($season->status === 'draft', 404);
        $rows = $this->ratings->calculate($season, Discipline::findOrFail($data['discipline_id']), RatingFilterDTO::fromArray($data));
        $athleteId = $request->user()->athlete?->id;

        return RatingDetailResource::collection($rows->where('athlete_id', $athleteId)->values());
    }
}
