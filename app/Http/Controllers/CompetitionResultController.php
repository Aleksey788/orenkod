<?php

namespace App\Http\Controllers;

use App\DTO\Competition\SaveCompetitionResultsDTO;
use App\Http\Requests\Competition\SaveCompetitionResultsRequest;
use App\Http\Resources\AthleteResultResource;
use App\Http\Resources\CompetitionParticipantResource;
use App\Http\Resources\CompetitionResultResource;
use App\Models\Competition;
use App\Services\Competition\CompetitionProtocolService;
use App\Services\Competition\CompetitionResultService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompetitionResultController extends Controller
{
    public function __construct(private CompetitionResultService $results) {}

    public function participants(Competition $competition): AnonymousResourceCollection
    {
        return CompetitionParticipantResource::collection($this->results->standings($competition));
    }

    public function index(Competition $competition): AnonymousResourceCollection
    {
        return $this->table($competition);
    }

    public function update(SaveCompetitionResultsRequest $request, Competition $competition): AnonymousResourceCollection
    {
        $this->results->save($competition, SaveCompetitionResultsDTO::fromArray($request->validated()), $request->user()->id);

        return $this->table($competition->refresh());
    }

    public function publish(Request $request, Competition $competition, CompetitionProtocolService $protocols): AnonymousResourceCollection
    {
        return $this->table($protocols->publish($competition, $request->user()->id));
    }

    public function publicIndex(Competition $competition): AnonymousResourceCollection
    {
        abort_unless($competition->status === 'published' && $competition->results_published_at !== null, 404);

        return $this->table($competition);
    }

    public function athleteIndex(Request $request): AnonymousResourceCollection
    {
        return AthleteResultResource::collection($this->results->history($request->user()->athlete?->id));
    }

    private function table(Competition $competition): AnonymousResourceCollection
    {
        return CompetitionResultResource::collection($this->results->standings($competition))->additional([
            'meta' => [
                'competition_id' => $competition->id,
                'results_published_at' => $competition->results_published_at?->toIso8601String(),
                'protocol_url' => $competition->results_published_at !== null
                    ? route('competitions.protocol', $competition) : null,
            ],
        ]);
    }
}
