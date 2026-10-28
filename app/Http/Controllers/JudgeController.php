<?php

namespace App\Http\Controllers;

use App\DTO\Judge\JudgeAssignmentDTO;
use App\Http\Requests\Judge\StoreJudgeAssignmentRequest;
use App\Http\Requests\Judge\UpdateJudgeAssignmentRequest;
use App\Http\Resources\JudgeAssignmentResource;
use App\Http\Resources\JudgeResource;
use App\Models\Competition;
use App\Models\Judge;
use App\Models\JudgeAssignment;
use App\Services\JudgeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JudgeController extends Controller
{
    public function __construct(private JudgeService $judges) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate(['per_page' => ['sometimes', 'integer', 'between:1,100']]);

        return JudgeResource::collection(Judge::query()->with(['user:id,first_name,last_name,middle_name', 'categoryAwards.judgeCategory'])
            ->orderBy('id')->paginate($data['per_page'] ?? 15));
    }

    public function store(StoreJudgeAssignmentRequest $request): JudgeAssignmentResource
    {
        return new JudgeAssignmentResource($this->judges->assign(new JudgeAssignmentDTO(
            $request->validated('competition_id'), $request->validated('judge_id'), $request->validated('position')), $request->user()));
    }

    public function update(UpdateJudgeAssignmentRequest $request, JudgeAssignment $assignment): JudgeAssignmentResource
    {
        return new JudgeAssignmentResource($this->judges->update($assignment, $request->validated('position'), $request->user()));
    }

    public function getByCompetition(Request $request, Competition $competition): AnonymousResourceCollection
    {
        if (! $request->routeIs('admin.judges.*')) {
            abort_unless($competition->status === 'published', 404);
        }

        return JudgeAssignmentResource::collection($competition->judgeAssignments()->with(JudgeService::RELATIONS)->orderBy('id')->get());
    }

    public function mine(Request $request): AnonymousResourceCollection
    {
        $judge = $request->user()->judge;
        abort_unless($judge, 403, 'Профиль судьи не найден.');

        return JudgeAssignmentResource::collection($judge->assignments()->with(JudgeService::RELATIONS)->orderByDesc('id')->paginate(15));
    }
}
