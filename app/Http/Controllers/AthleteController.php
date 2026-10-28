<?php

namespace App\Http\Controllers;

use App\DTO\Athlete\AthleteFilterDTO;
use App\DTO\Athlete\AthleteProfileDTO;
use App\Http\Requests\Athlete\AthleteFilterRequest;
use App\Http\Requests\Athlete\StoreAthleteRequest;
use App\Http\Requests\Athlete\UpdateAthleteRequest;
use App\Http\Resources\AthleteResource;
use App\Models\Athlete;
use App\Services\AthleteService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AthleteController extends Controller
{
    public function __construct(private AthleteService $athletes) {}

    public function index(AthleteFilterRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        return AthleteResource::collection($this->athletes->query(new AthleteFilterDTO($filters))
            ->with(AthleteService::RELATIONS)->orderBy('id')->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function show(Athlete $athlete): AthleteResource
    {
        return new AthleteResource($athlete->load(AthleteService::RELATIONS));
    }

    public function store(StoreAthleteRequest $request): AthleteResource
    {
        return new AthleteResource($this->athletes->create($request->validated('user_id'),
            AthleteProfileDTO::fromArray($request->validated()), $request->user()));
    }

    public function update(UpdateAthleteRequest $request, Athlete $athlete): AthleteResource
    {
        return new AthleteResource($this->athletes->update($athlete,
            AthleteProfileDTO::fromArray($request->validated()), $request->user(), 'admin'));
    }

    public function profile(Request $request): AthleteResource
    {
        return new AthleteResource($request->user()->athlete()->with(AthleteService::RELATIONS)->firstOrFail());
    }

    public function updateProfile(UpdateAthleteRequest $request): AthleteResource
    {
        $athlete = $request->user()->athlete()->firstOrFail();

        return new AthleteResource($this->athletes->update($athlete,
            AthleteProfileDTO::fromArray($request->validated()), $request->user(), 'athlete'));
    }

    public function coachIndex(AthleteFilterRequest $request): AnonymousResourceCollection
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403, 'Профиль тренера не найден.');
        $filters = $request->validated();

        return AthleteResource::collection($this->athletes->query(new AthleteFilterDTO($filters), $coach)
            ->with(AthleteService::RELATIONS)->orderBy('id')->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function coachShow(Request $request, Athlete $athlete): AthleteResource
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403, 'Профиль тренера не найден.');

        return new AthleteResource($this->athletes->query(new AthleteFilterDTO([]), $coach)
            ->with(AthleteService::RELATIONS)->findOrFail($athlete->id));
    }
}
