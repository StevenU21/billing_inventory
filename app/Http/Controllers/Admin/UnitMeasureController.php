<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitMeasureRequest;
use App\Models\UnitMeasure;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UnitMeasureController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', UnitMeasure::class);

        $unitMeasures = QueryBuilder::for(UnitMeasure::class)
            ->allowedFilters([
                AllowedFilter::scope('search'),
            ])
            ->defaultSort('-created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.unit_measures.index', compact('unitMeasures'));
    }

    public function create()
    {
        $this->authorize('create', UnitMeasure::class);

        return view('admin.unit_measures.create');
    }

    public function store(UnitMeasureRequest $request)
    {
        $data = $request->validated();
        $data['allows_decimals'] = $data['allows_decimals'] ?? false;
        UnitMeasure::create($data);

        return redirect()->route('unit_measures.index')->with('success', 'Unidad de medida creada correctamente.');
    }

    public function show(UnitMeasure $unitMeasure)
    {
        $this->authorize('view', $unitMeasure);

        return view('admin.unit_measures.show', compact('unitMeasure'));
    }

    public function edit(UnitMeasure $unitMeasure)
    {
        $this->authorize('update', $unitMeasure);

        return view('admin.unit_measures.edit', compact('unitMeasure'));
    }

    public function update(UnitMeasureRequest $request, UnitMeasure $unitMeasure)
    {
        $data = $request->validated();
        $data['allows_decimals'] = $data['allows_decimals'] ?? false;
        $unitMeasure->update($data);

        return redirect()->route('unit_measures.index')->with('updated', 'Unidad de medida actualizada correctamente.');
    }

    public function destroy(UnitMeasure $unitMeasure)
    {
        $this->authorize('destroy', $unitMeasure);
        $unitMeasure->delete();

        return redirect()->route('unit_measures.index')->with('deleted', 'Unidad de medida eliminada correctamente.');
    }
}
