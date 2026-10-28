<?php

namespace App\Http\Controllers;

use App\DTO\Rank\RankApplicationDTO;
use App\DTO\Rank\RankDecisionDTO;
use App\Http\Requests\Rank\DecideRankApplicationRequest;
use App\Http\Requests\Rank\RankApplicationFilterRequest;
use App\Http\Requests\Rank\StoreRankApplicationRequest;
use App\Http\Resources\RankApplicationResource;
use App\Models\RankApplication;
use App\Services\RankApplicationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RankApplicationController extends Controller
{
    public function __construct(private RankApplicationService $applications) {}

    public function index(RankApplicationFilterRequest $request): AnonymousResourceCollection
    {
        $role = $request->routeIs('admin.rank-applications.*') ? 'admin'
            : ($request->routeIs('coach.rank-applications.*') ? 'coach' : 'athlete');
        $filters = $request->validated();

        return RankApplicationResource::collection($this->applications->query($filters, $request->user(), $role)
            ->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function store(StoreRankApplicationRequest $request): RankApplicationResource
    {
        $coach = $request->routeIs('coach.rank-applications.store');
        $athleteId = $coach ? $request->validated('athlete_id') : $request->user()->athlete?->id;
        abort_unless($athleteId, 403, 'Профиль спортсмена не найден.');

        return new RankApplicationResource($this->applications->submit(new RankApplicationDTO(
            $athleteId, $request->validated('sport_rank_id'), $request->validated('discipline_id'),
            $request->validated('competition_result_id'), $request->validated('request_type', 'assignment'),
        ), $request->user(), $coach ? 'coach' : 'athlete'));
    }

    public function updateStatus(DecideRankApplicationRequest $request, RankApplication $application): RankApplicationResource
    {
        return new RankApplicationResource($this->applications->decide($application,
            RankDecisionDTO::fromArray($request->validated()), $request->user()));
    }
}
