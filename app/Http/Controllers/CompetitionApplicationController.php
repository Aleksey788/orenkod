<?php

namespace App\Http\Controllers;

use App\DTO\Competition\ApplicationDecisionDTO;
use App\Http\Requests\Competition\RejectCompetitionApplicationRequest;
use App\Http\Resources\CompetitionApplicationResource;
use App\Models\Athlete;
use App\Models\Competition;
use App\Models\CompetitionApplication;
use App\Services\Competition\CompetitionApplicationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompetitionApplicationController extends Controller
{
    public function __construct(
        private CompetitionApplicationService $service
    ) {}

    /**
     * Спортсмен подаёт заявку за себя.
     */
    public function store(
        Request $request,
        Competition $competition
    ) {
        $athlete = $request->user()->athlete;

        abort_unless(
            $athlete,
            403,
            'Профиль спортсмена не найден'
        );

        $application = $this->service->apply(
            $competition,
            $athlete,
            $request->user()
        );

        return (new CompetitionApplicationResource(
            $application->load([
                'competition',
                'athlete.user',
            ])
        ))->response()->setStatusCode(201);
    }

    /**
     * Спортсмен смотрит свои заявки.
     */
    public function athleteIndex(Request $request)
    {
        $athlete = $request->user()->athlete;

        abort_unless(
            $athlete,
            403,
            'Профиль спортсмена не найден'
        );

        $applications = CompetitionApplication::query()
            ->where('athlete_id', $athlete->id)
            ->with(['competition', 'athlete.user'])
            ->latest()
            ->paginate(15);

        return CompetitionApplicationResource::collection(
            $applications
        );
    }

    /**
     * Тренер подаёт заявку за спортсмена.
     */
    public function coachStore(
        Request $request,
        Athlete $athlete,
        Competition $competition
    ) {
        $coach = $request->user()->coach;

        abort_unless(
            $coach,
            403,
            'Профиль тренера не найден'
        );

        $application = $this->service->apply(
            $competition,
            $athlete,
            $request->user(),
            $coach
        );

        return (new CompetitionApplicationResource(
            $application->load([
                'competition',
                'athlete.user',
            ])
        ))->response()->setStatusCode(201);
    }

    /**
     * Заявки, отправленные текущим аккаунтом.
     */
    public function coachIndex(Request $request)
    {
        $applications = CompetitionApplication::query()
            ->where(
                'submitted_by_user_id',
                $request->user()->id
            )
            ->with(['competition', 'athlete.user'])
            ->latest()
            ->paginate(15);

        return CompetitionApplicationResource::collection(
            $applications
        );
    }

    /**
     * Администратор просматривает заявки.
     */
    public function adminIndex(
        Request $request,
        Competition $competition
    ) {
        $filters = $request->validate([
            'status' => [
                'sometimes',
                Rule::in([
                    'pending',
                    'approved',
                    'rejected',
                ]),
            ],
        ]);

        $query = $competition->applications()
            ->with([
                'competition',
                'athlete.user',
                'submittedBy',
                'reviewedBy',
            ]);

        if (isset($filters['status'])) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        return CompetitionApplicationResource::collection(
            $query->latest()
                ->paginate(20)
                ->withQueryString()
        );
    }

    /**
     * Одобрение заявки.
     */
    public function approve(
        Request $request,
        CompetitionApplication $application
    ) {
        $dto = ApplicationDecisionDTO::approve();

        $application = $this->service->decide(
            $application,
            $request->user(),
            $dto
        );

        return new CompetitionApplicationResource(
            $application->load([
                'competition',
                'athlete.user',
            ])
        );
    }

    /**
     * Отклонение заявки.
     */
    public function reject(
        RejectCompetitionApplicationRequest $request,
        CompetitionApplication $application
    ) {
        $dto = ApplicationDecisionDTO::reject(
            $request->validated('rejection_reason')
        );

        $application = $this->service->decide(
            $application,
            $request->user(),
            $dto
        );

        return new CompetitionApplicationResource(
            $application->load([
                'competition',
                'athlete.user',
            ])
        );
    }
}