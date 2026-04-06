<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuotationRequest;
use App\Models\Quotation;
use App\Services\QuotationService;
use App\DTOs\QuotationData;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class CuotationController extends Controller
{
    use AuthorizesRequests;

    public function store(QuotationRequest $request, QuotationService $service)
    {
        $this->authorize('create', Quotation::class);

        $validatedData = $request->validated();
        $validatedData['user_id'] = Auth::id(); // Ensure user_id is present for DTO
        $result = $service->calculateQuotation(QuotationData::fromRequest($validatedData));
        return $result['pdf']->stream('proforma.pdf');
    }
}
