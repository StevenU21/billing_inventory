<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\EntityData;
use App\Http\Controllers\Controller;
use App\Http\Requests\EntityRequest;
use App\Models\Department;
use App\Models\Entity;
use App\Models\Municipality;
use App\Services\EntityFinancialService;
use App\Services\EntityService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EntityController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected EntityService $entityService,
        protected EntityFinancialService $financialService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Entity::class);

        $user = auth()->user();
        $perPage = request('per_page', 10);

        $baseQuery = Entity::query()
            ->with('municipality')
            ->visibleFor($user);

        $entities = QueryBuilder::for($baseQuery)
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::exact('municipality_id'),
                AllowedFilter::callback('department_id', function ($query, $value) {
                    if (! is_numeric($value)) {
                        return $query;
                    }

                    return $query->whereHas('municipality', function ($municipalityQuery) use ($value) {
                        $municipalityQuery->where('department_id', (int) $value);
                    });
                }),
                AllowedFilter::callback('entity_type', function ($query, $value) {
                    $type = is_string($value) ? $value : null;

                    return match ($type) {
                        'clients' => $query->where('is_client', true)->where('is_supplier', false),
                        'suppliers' => $query->where('is_supplier', true)->where('is_client', false),
                        'both' => $query->where('is_client', true)->where('is_supplier', true),
                        default => $query,
                    };
                }),
            ])
            ->defaultSort('-created_at')
            ->allowedSorts(...['id', 'first_name', 'last_name', 'identity_card', 'ruc', 'email', 'phone', 'municipality_id', 'is_active', 'created_at', 'is_client', 'is_supplier'])
            ->paginate($perPage)
            ->withQueryString();

        $departments = Department::orderBy('name')->pluck('name', 'id');
        $municipalitiesQuery = Municipality::orderBy('name');
        if (request()->filled('filter.department_id') && is_numeric(request('filter.department_id'))) {
            $municipalitiesQuery->where('department_id', (int) request('filter.department_id'));
        }
        $municipalities = $municipalitiesQuery->pluck('name', 'id');

        return view('admin.entities.index', [
            'entities' => $entities,
            'departments' => $departments,
            'municipalities' => $municipalities,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Entity::class);

        return view('admin.entities.create');
    }

    public function store(EntityRequest $request)
    {
        $dto = EntityData::fromRequest($request->validated());
        $this->entityService->createEntity($dto);

        return redirect()->route('entities.index')->with('success', 'Entidad creada correctamente.');
    }

    public function show(Entity $entity)
    {
        $this->authorize('view', $entity);
        $financial = $this->financialService->forShow($entity);

        return view('admin.entities.show', [
            'entity' => $entity,
            ...$financial->toArray(),
        ]);
    }

    public function edit(Entity $entity)
    {
        $this->authorize('update', $entity);

        return view('admin.entities.edit', [
            'entity' => $entity,
        ]);
    }

    public function update(EntityRequest $request, Entity $entity)
    {
        $dto = EntityData::fromRequest($request->validated());
        $this->entityService->updateEntity($entity, $dto);

        return redirect()->route('entities.index')->with('updated', 'Entidad actualizada correctamente.');
    }

    public function destroy(Entity $entity)
    {
        $this->authorize('destroy', $entity);

        $entity = $this->entityService->toggleActive($entity);

        $msg = $entity->is_active ? 'Entidad habilitada.' : 'Entidad deshabilitada.';

        return redirect()->route('entities.index')->with('success', $msg);
    }
}
