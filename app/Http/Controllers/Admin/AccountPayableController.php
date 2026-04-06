<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountPayableStatus;
use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AccountPayableController extends Controller
{
    use AuthorizesRequests;

    public const RELATIONS = [
        'entity',
        'purchase.user',
        'purchase.details.productVariant.product',
        'purchase.paymentMethod',
        'payments.paymentMethod',
        'payments.user',
    ];

    public function index(Request $request)
    {
        $this->authorize('viewAny', AccountPayable::class);

        $accounts = QueryBuilder::for(AccountPayable::class)
            ->allowedFilters(...[
                AllowedFilter::exact('status'),
                AllowedFilter::exact('supplier_id'),
                AllowedFilter::exact('purchase_id'),
                AllowedFilter::scope('from'), // Asumiendo scope similar a AR
                AllowedFilter::scope('to'),
            ])
            ->allowedSorts(...['id', 'total_amount', 'balance', 'created_at'])
            ->defaultSort('-id')
            ->with(self::RELATIONS)
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        $statuses = AccountPayableStatus::labels();
        $methods = PaymentMethod::pluck('name', 'id');

        return view('admin.account_payables.index', compact('accounts', 'statuses', 'methods'));
    }

    public function show(AccountPayable $accountPayable)
    {
        $this->authorize('view', $accountPayable);
        $accountPayable->load(self::RELATIONS);

        return view('admin.account_payables.show', [
            'ap' => $accountPayable,
        ]);
    }
}
