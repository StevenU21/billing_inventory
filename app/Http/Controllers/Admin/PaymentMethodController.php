<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethodRequest;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PaymentMethodController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', PaymentMethod::class);

        $paymentMethods = QueryBuilder::for(PaymentMethod::class)
            ->allowedFilters([
                AllowedFilter::scope('search'),
            ])
            ->defaultSort('-created_at')
            ->paginate(request('per_page', 10))
            ->withQueryString();

        return view('admin.payment_methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        $this->authorize('create', PaymentMethod::class);

        return view('admin.payment_methods.create');
    }

    public function store(PaymentMethodRequest $request)
    {
        $data = $request->validated();
        $data['is_cash'] = $data['is_cash'] ?? false;
        $data['is_active'] = $data['is_active'] ?? true;
        PaymentMethod::create($data);

        return redirect()->route('payment_methods.index')->with('success', 'Método de pago creado correctamente.');
    }

    public function show(PaymentMethod $paymentMethod)
    {
        $this->authorize('view', $paymentMethod);

        return view('admin.payment_methods.show', compact('paymentMethod'));
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        $this->authorize('update', $paymentMethod);

        return view('admin.payment_methods.edit', compact('paymentMethod'));
    }

    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        $data = $request->validated();
        $data['is_cash'] = $data['is_cash'] ?? false;
        $data['is_active'] = $data['is_active'] ?? false;
        $paymentMethod->update($data);

        return redirect()->route('payment_methods.index')->with('updated', 'Método de pago actualizado correctamente.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $this->authorize('destroy', $paymentMethod);
        $paymentMethod->delete();

        return redirect()->route('payment_methods.index')->with('deleted', 'Método de pago eliminado correctamente.');
    }
}
