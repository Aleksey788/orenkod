<?php

namespace App\Http\Controllers;

use App\DTO\Coach\CoachLinkDTO;
use App\Http\Requests\Coach\DecideCoachLinkRequest;
use App\Http\Requests\Coach\EndCoachLinkRequest;
use App\Http\Requests\Coach\StoreCoachLinkRequest;
use App\Http\Resources\CoachLinkResource;
use App\Models\CoachAthlete;
use App\Services\CoachLinkService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CoachLinkController extends Controller
{
    public function __construct(private CoachLinkService $links) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate(['status' => ['sometimes', Rule::in(['pending', 'active', 'rejected', 'ended'])],
            'per_page' => ['sometimes', 'integer', 'between:1,100']]);
        $query = CoachAthlete::query()->with(CoachLinkService::RELATIONS);
        if ($request->routeIs('coach.links.*')) {
            $coach = $request->user()->coach;
            abort_unless($coach, 403);
            $query->where('coach_id', $coach->id);
        } elseif ($request->routeIs('athlete.coach-links.*')) {
            $athlete = $request->user()->athlete;
            abort_unless($athlete, 403);
            $query->where('athlete_id', $athlete->id);
        } else {
            abort_unless($request->routeIs('admin.coach-links.*'), 403);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return CoachLinkResource::collection($query->orderByDesc('id')->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function store(StoreCoachLinkRequest $request): CoachLinkResource
    {
        return new CoachLinkResource($this->links->request(new CoachLinkDTO(
            $request->validated('athlete_id'), $request->validated('discipline_id')), $request->user()));
    }

    public function updateStatus(DecideCoachLinkRequest $request, CoachAthlete $link): CoachLinkResource
    {
        return new CoachLinkResource($this->links->decide($link, $request->validated('status'),
            $request->validated('reason'), $request->user()));
    }

    public function end(EndCoachLinkRequest $request, CoachAthlete $link): CoachLinkResource
    {
        $role = $request->routeIs('coach.links.*') ? 'coach' : 'athlete';

        return new CoachLinkResource($this->links->end($link, $request->validated('reason'), $request->user(), $role));
    }
}
