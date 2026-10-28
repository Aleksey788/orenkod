<?php

namespace App\Http\Controllers;

use App\DTO\RegionalTeam\RegionalTeamDTO;
use App\Http\Requests\Rating\DecisionReasonRequest;
use App\Http\Requests\RegionalTeam\AddRegionalTeamMemberRequest;
use App\Http\Requests\RegionalTeam\SaveRegionalTeamRequest;
use App\Http\Resources\RatingResource;
use App\Http\Resources\RegionalTeamMemberResource;
use App\Http\Resources\RegionalTeamResource;
use App\Models\AuditLog;
use App\Models\RegionalTeam;
use App\Models\RegionalTeamMember;
use App\Services\RegionalTeam\RegionalTeamDocumentService;
use App\Services\RegionalTeam\RegionalTeamService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegionalTeamController extends Controller
{
    public function __construct(private RegionalTeamService $teams, private RegionalTeamDocumentService $documents) {}

    public function index(): AnonymousResourceCollection
    {
        return RegionalTeamResource::collection(RegionalTeam::query()->latest()->paginate(15));
    }

    public function publicIndex(): AnonymousResourceCollection
    {
        return RegionalTeamResource::collection(RegionalTeam::query()->where('status', 'published')->latest('published_at')->paginate(15));
    }

    public function show(RegionalTeam $team): RegionalTeamResource
    {
        return new RegionalTeamResource($team->load(['members' => fn ($query) => $query->whereNull('removed_at')->orderBy('id')]));
    }

    public function publicShow(RegionalTeam $team): RegionalTeamResource
    {
        abort_unless(in_array($team->status, ['published', 'superseded'], true), 404);

        return $this->show($team);
    }

    public function store(SaveRegionalTeamRequest $request): RegionalTeamResource
    {
        return new RegionalTeamResource($this->teams->save(null, new RegionalTeamDTO($request->validated()), $request->user()->id));
    }

    public function update(SaveRegionalTeamRequest $request, RegionalTeam $team): RegionalTeamResource
    {
        return new RegionalTeamResource($this->teams->save($team, new RegionalTeamDTO($request->validated()), $request->user()->id));
    }

    public function candidates(RegionalTeam $team): AnonymousResourceCollection
    {
        return RatingResource::collection($this->teams->candidates($team));
    }

    public function addMember(AddRegionalTeamMemberRequest $request, RegionalTeam $team): RegionalTeamMemberResource
    {
        return new RegionalTeamMemberResource($this->teams->add($team, $request->validated('athlete_id'), $request->validated('reason'), $request->user()->id));
    }

    public function removeMember(DecisionReasonRequest $request, RegionalTeam $team, RegionalTeamMember $member): Response
    {
        $this->teams->remove($team, $member, $request->validated('reason'), $request->user()->id);

        return response()->noContent();
    }

    public function publish(Request $request, RegionalTeam $team): RegionalTeamResource
    {
        return $this->show($this->teams->publish($team, $request->user()->id, $this->documents));
    }

    public function newVersion(DecisionReasonRequest $request, RegionalTeam $team): RegionalTeamResource
    {
        return $this->show($this->teams->newVersion($team, $request->user()->id, $request->validated('reason')));
    }

    public function document(Request $request, RegionalTeam $team): Response|StreamedResponse
    {
        return $this->adminDocument($request, $team, false);
    }

    public function orderDraft(Request $request, RegionalTeam $team): Response|StreamedResponse
    {
        return $this->adminDocument($request, $team, true);
    }

    public function publicDocument(RegionalTeam $team): StreamedResponse
    {
        abort_unless(in_array($team->status, ['published', 'superseded'], true), 404);

        return $this->download($team, false);
    }

    private function adminDocument(Request $request, RegionalTeam $team, bool $orderDraft): Response|StreamedResponse
    {
        if (in_array($team->status, ['published', 'superseded'], true)) {
            return $this->download($team, $orderDraft);
        }
        $contents = $this->documents->render($team, $orderDraft);
        AuditLog::recordAction($team, $request->user()->id, 'team.document_previewed', null, ['order_draft' => $orderDraft]);

        return response($contents, 200, ['Content-Type' => 'application/pdf', 'Cache-Control' => 'private, no-store',
            'Content-Disposition' => 'inline; filename="team-'.$team->id.($orderDraft ? '-order-draft' : '-list').'.pdf"',
            'X-Content-Type-Options' => 'nosniff']);
    }

    private function download(RegionalTeam $team, bool $orderDraft): StreamedResponse
    {
        $path = $orderDraft ? $team->order_path : $team->list_path;
        abort_unless($path && Storage::disk('team_documents')->exists($path), 404);

        return Storage::disk('team_documents')->download($path,
            'team-'.$team->id.'-v'.$team->version.($orderDraft ? '-order-draft' : '-list').'.pdf',
            ['Content-Type' => 'application/pdf', 'Cache-Control' => 'no-cache', 'X-Content-Type-Options' => 'nosniff']);
    }
}
