<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Services\AuditIndexService;
use App\Services\AuditShowService;
use Deifhelt\ActivityPresenter\Facades\ActivityPresenter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AuditController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, AuditIndexService $auditIndexService)
    {
        $this->authorize('viewAny', Audit::class);

        $groupedQuery = QueryBuilder::for(Audit::class)
            ->select(
                'subject_type',
                'subject_id',
                DB::raw('MAX(id) as id'),
                DB::raw('COUNT(*) as count'),
            )
            ->groupBy('subject_type', 'subject_id')
            ->allowedFilters(...[
                AllowedFilter::exact('causer_id'),
                AllowedFilter::exact('event'),
                AllowedFilter::exact('subject_type'),
                AllowedFilter::scope('range'),
            ])
            ->allowedSorts(...['id', 'count'])
            ->defaultSort('-id');

        [$activities, $allCausers, $modelOptions] = $auditIndexService->build(
            $groupedQuery->getEloquentBuilder()->toBase(),
            (int) $request->get('per_page', 10)
        );

        return view('admin.audits.index', compact('activities', 'allCausers', 'modelOptions'));
    }

    public function show(Request $request, $subjectType, $subjectId, AuditShowService $auditShowService)
    {
        $this->authorize('viewAny', Audit::class);

        $decodedType = ActivityPresenter::decodeSubjectType($subjectType);

        $filters = $request->input('filter', []);

        $viewData = $auditShowService->build($decodedType, $subjectId, $filters);

        return view('admin.audits.show', [
            'activities' => $viewData['activities'],
            'subjectType' => $decodedType,
            'subjectId' => $subjectId,
            'encodedSubjectType' => $viewData['encodedSubjectType'],
            'subjectLabel' => $viewData['subjectLabel'],
        ]);
    }
}
