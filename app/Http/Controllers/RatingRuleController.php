<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rating\ClassifyCompetitionRequest;
use App\Http\Requests\Rating\SaveRatingRuleRequest;
use App\Http\Resources\RatingRuleResource;
use App\Models\Competition;
use App\Models\RatingRule;
use App\Models\SportRank;
use App\Services\Rating\RatingRuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RatingRuleController extends Controller
{
    public function __construct(private RatingRuleService $rules) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate(['season_id' => ['required', 'integer', 'exists:rating_seasons,id']]);

        return RatingRuleResource::collection(RatingRule::query()->where('rating_season_id', $data['season_id'])->orderBy('category')->orderBy('place')->get());
    }

    public function store(SaveRatingRuleRequest $request): RatingRuleResource
    {
        return new RatingRuleResource($this->rules->save(null, $request->validated(), $request->user()->id));
    }

    public function update(SaveRatingRuleRequest $request, RatingRule $rule): RatingRuleResource
    {
        return new RatingRuleResource($this->rules->save($rule, $request->validated(), $request->user()->id));
    }

    public function classify(ClassifyCompetitionRequest $request, Competition $competition): JsonResponse
    {
        $competition = $this->rules->classify($competition, $request->validated('rating_category'), $request->user()->id, $request->validated('reason'));

        return response()->json(['data' => ['competition_id' => $competition->id, 'rating_category' => $competition->rating_category]]);
    }

    public function rankOrder(Request $request, SportRank $rank): JsonResponse
    {
        $data = $request->validate(['sort_order' => ['required', 'integer', 'between:1,32767']]);
        $rank = $this->rules->rankOrder($rank, $data['sort_order'], $request->user()->id);

        return response()->json(['data' => ['id' => $rank->id, 'name' => $rank->name, 'sort_order' => $rank->sort_order]]);
    }
}
