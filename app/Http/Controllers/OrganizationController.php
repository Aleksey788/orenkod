<?php

namespace App\Http\Controllers;

use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OrganizationController extends Controller
{
    public function __construct(
        private OrganizationService $service
    ) {}

    /**
     * Получение списка организаций.
     * Поддерживаются поиск, фильтры и пагинация.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'type' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'municipality' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $query = Organization::query();

        // Поиск по названию и муниципалитету
        if (! empty($filters['search'])) {
            $search = '%'.trim($filters['search']).'%';

            $query->where(function ($q) use ($search) {
                $q->whereRaw(
                    'LOWER(name) LIKE LOWER(?)',
                    [$search]
                )->orWhereRaw(
                    'LOWER(municipality) LIKE LOWER(?)',
                    [$search]
                );
            });
        }

        // Фильтр по типу организации
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Фильтр по муниципалитету
        if (! empty($filters['municipality'])) {
            $query->where(
                'municipality',
                $filters['municipality']
            );
        }

        // Пагинация
        $organizations = $query
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return OrganizationResource::collection(
            $organizations
        );
    }

    /**
     * Получение конкретной организации.
     */
    public function show(
        Organization $organization
    ): OrganizationResource {
        return new OrganizationResource(
            $organization
        );
    }

    /**
     * Создание организации.
     */
    public function store(
        StoreOrganizationRequest $request
    ): JsonResponse {
        $organization = $this->service->create(
            $request->validated(),
            $request->user()->id
        );

        return (new OrganizationResource($organization))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Редактирование организации.
     */
    public function update(
        UpdateOrganizationRequest $request,
        Organization $organization
    ): OrganizationResource {
        $organization = $this->service->update(
            $organization,
            $request->validated(),
            $request->user()->id
        );

        return new OrganizationResource(
            $organization
        );
    }

    /**
     * Удаление организации.
     */
    public function destroy(
        Request $request,
        Organization $organization
    ): Response {
        $this->service->delete(
            $organization,
            $request->user()->id
        );

        return response()->noContent();
    }
}
