<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountReceivable;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Enums\AccountReceivableStatus;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AccountReceivableController extends Controller
{
    use AuthorizesRequests;

    public const RELATIONS = [
        'entity',
        'sale.user',
        'sale.saleDetails.productVariant.product',
        'sale.paymentMethod',
        'payments.paymentMethod',
        'payments.user',
    ];

    public function index(Request $request)
    {
        $this->authorize('viewAny', AccountReceivable::class);

        $accounts = QueryBuilder::for(AccountReceivable::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('entity_id'),
                AllowedFilter::exact('sale_id'),
                AllowedFilter::exact('sale.user_id'),
                AllowedFilter::exact('sale.payment_method_id'),
                AllowedFilter::scope('search'),
                AllowedFilter::scope('from'),
                AllowedFilter::scope('to'),
                AllowedFilter::scope('min_balance'),
                AllowedFilter::scope('max_balance'),
            ])
            ->allowedSorts(['id', 'amount_due', 'amount_paid', 'created_at'])
            ->defaultSort('-id')
            ->with(self::RELATIONS)

            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        $statuses = AccountReceivableStatus::labels();

        $methods = PaymentMethod::pluck('name', 'id');

        return view('admin.accounts_receivable.index', compact('accounts', 'statuses', 'methods'));
    }

    public function show(AccountReceivable $accountReceivable)
    {
        $this->authorize('view', $accountReceivable);
        $accountReceivable->load(self::RELATIONS);

        return view('admin.accounts_receivable.show', [
            'ar' => $accountReceivable,
        ]);
    }
}
