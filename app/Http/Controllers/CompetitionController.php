<?php

namespace App\Http\Controllers;

use App\DTO\Competition\CompetitionDTO;
use App\Http\Requests\Competition\StoreCompetitionRequest;
use App\Http\Requests\Competition\UploadRegulationRequest;
use App\Http\Resources\CompetitionResource;
use App\Models\Competition;
use App\Services\Competition\CompetitionService;
use Illuminate\Http\Request;

class CompetitionController extends Controller
{
    public function __construct(
        private CompetitionService $service
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'discipline_id' => ['sometimes', 'integer', 'exists:disciplines,id'],
            'age_group_id' => ['sometimes', 'integer', 'exists:age_groups,id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Competition::query()
            ->with(['discipline', 'competitionType', 'ageGroup'])
            ->where('status', 'published');

        if (isset($filters['discipline_id'])) {
            $query->where(
                'discipline_id',
                $filters['discipline_id']
            );
        }

        if (isset($filters['age_group_id'])) {
            $query->where(
                'age_group_id',
                $filters['age_group_id']
            );
        }

        return CompetitionResource::collection(
            $query->orderBy('starts_at')
                ->paginate($filters['per_page'] ?? 15)
                ->withQueryString()
        );
    }

    public function show(Competition $competition)
    {
        abort_unless(
            $competition->status === 'published',
            404
        );

        return new CompetitionResource(
            $competition->load([
                'discipline',
                'competitionType',
                'ageGroup',
            ])
        );
    }

    public function adminShow(Competition $competition)
    {
        return new CompetitionResource(
            $competition->load([
                'discipline',
                'competitionType',
                'ageGroup',
            ])
        );
    }

    public function store(StoreCompetitionRequest $request)
    {
        $dto = CompetitionDTO::fromArray(
            $request->validated()
        );

        $competition = $this->service->create(
            $dto,
            $request->user()->id
        );

        return (new CompetitionResource(
            $competition->load([
                'discipline',
                'competitionType',
                'ageGroup',
            ])
        ))->response()->setStatusCode(201);
    }

    public function update(
        StoreCompetitionRequest $request,
        Competition $competition
    ) {
        $dto = CompetitionDTO::fromArray(
            $request->validated()
        );

        $competition = $this->service->update(
            $competition,
            $dto
        );

        return new CompetitionResource(
            $competition->load([
                'discipline',
                'competitionType',
                'ageGroup',
            ])
        );
    }

    public function uploadRegulation(
        UploadRegulationRequest $request,
        Competition $competition
    ) {
        $competition = $this->service->uploadRegulation(
            $competition,
            $request->file('regulation')
        );

        return new CompetitionResource(
            $competition->load([
                'discipline',
                'competitionType',
                'ageGroup',
            ])
        );
    }

    public function publish(Competition $competition)
    {
        $competition = $this->service->publish(
            $competition
        );

        return new CompetitionResource(
            $competition->load([
                'discipline',
                'competitionType',
                'ageGroup',
            ])
        );
    }
}