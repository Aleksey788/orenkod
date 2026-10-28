<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rating\DecisionReasonRequest;
use App\Http\Requests\Rating\SaveAthleteRestrictionRequest;
use App\Http\Resources\AthleteRestrictionResource;
use App\Models\AthleteRestriction;
use App\Services\Rating\AthleteRestrictionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AthleteRestrictionController extends Controller
{
    public function __construct(private AthleteRestrictionService $restrictions) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate(['athlete_id' => ['sometimes', 'integer', 'exists:athletes,id']]);

        return AthleteRestrictionResource::collection(AthleteRestriction::query()
            ->when(isset($data['athlete_id']), fn ($query) => $query->where('athlete_id', $data['athlete_id']))->latest()->paginate(20));
    }

    public function store(SaveAthleteRestrictionRequest $request): AthleteRestrictionResource
    {
        return new AthleteRestrictionResource($this->restrictions->save(null, $request->validated(), $request->user()->id));
    }

    public function update(SaveAthleteRestrictionRequest $request, AthleteRestriction $restriction): AthleteRestrictionResource
    {
        return new AthleteRestrictionResource($this->restrictions->save($restriction, $request->validated(), $request->user()->id));
    }

    public function revoke(DecisionReasonRequest $request, AthleteRestriction $restriction): AthleteRestrictionResource
    {
        return new AthleteRestrictionResource($this->restrictions->revoke($restriction, $request->user()->id, $request->validated('reason')));
    }
}
