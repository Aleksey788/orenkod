<?php

namespace App\Http\Controllers;

use App\DTO\Rating\RatingSeasonDTO;
use App\Http\Requests\Rating\DecisionReasonRequest;
use App\Http\Requests\Rating\StoreRatingSeasonRequest;
use App\Http\Resources\RatingSeasonResource;
use App\Models\RatingSeason;
use App\Services\Rating\RatingSeasonService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RatingSeasonController extends Controller
{
    public function __construct(private RatingSeasonService $seasons) {}

    public function index(): AnonymousResourceCollection
    {
        return RatingSeasonResource::collection(RatingSeason::query()->orderByDesc('year')->paginate(15));
    }

    public function show(RatingSeason $season): RatingSeasonResource
    {
        return new RatingSeasonResource($season->load('rules'));
    }

    public function store(StoreRatingSeasonRequest $request): RatingSeasonResource
    {
        return new RatingSeasonResource($this->seasons->save(null, new RatingSeasonDTO($request->validated()), $request->user()->id)->load('rules'));
    }

    public function update(StoreRatingSeasonRequest $request, RatingSeason $season): RatingSeasonResource
    {
        return new RatingSeasonResource($this->seasons->save($season, new RatingSeasonDTO($request->validated()), $request->user()->id)->load('rules'));
    }

    public function recalculate(DecisionReasonRequest $request, RatingSeason $season): RatingSeasonResource
    {
        return new RatingSeasonResource($this->seasons->recalculate($season, $request->user()->id, $request->validated('reason')));
    }
}
