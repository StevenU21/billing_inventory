<?php

namespace App\Http\Controllers\Cashier;

use App\DTOs\SaleData;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\SaleRequest;
use App\Services\SaleService;

class SaleController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Sale::class);
        return view('cashier.sales.index');
    }

    public function store(SaleRequest $request, SaleService $saleService)
    {
        $this->authorize('create', Sale::class);

        $saleService->createSale(SaleData::fromRequest($request->validated() + ['user_id' => $request->user()->id]));

        return redirect()->route('cashier.sales.index')->with('success', 'Venta realizada');
    }
}
