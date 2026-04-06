@extends('layouts.app')
@section('title', 'Cuentas por Pagar')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8" x-data="{
                                        isModalOpen: false,
                                        closeModal() { this.isModalOpen = false },
                                        paymentForm: { action: '', amount: '', payment_method_id: '1', currency: '', account_payable_id: '' },
                                        paymentContext: { supplier: '', ref: '', condition: '' },
                                        remainingValue: 0,
                                        formatCurrency(val) {
                                            const num = Number(val || 0);
                                            return Number.isFinite(num) ?
                                                num.toLocaleString('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) :
                                                '0.00';
                                        },
                                        payAll() {
                                            this.paymentForm.amount = this.remainingValue;
                                        },
                                        openPaymentModal(remaining, defaultMethodId, supplier, ref, condition, currency, accountPayableId) {
                                            this.paymentForm.action = route('admin.account_payables.payments.store', accountPayableId);
                                            this.paymentForm.amount = 0;
                                            this.paymentForm.payment_method_id = defaultMethodId || '1';
                                            this.paymentForm.currency = currency || '';
                                            this.paymentForm.account_payable_id = accountPayableId || '';
                                            this.paymentContext.supplier = supplier || '-';
                                            this.paymentContext.ref = ref || '-';
                                            this.paymentContext.condition = condition || '';
                                            this.remainingValue = Number(remaining) || 0;
                                            this.isModalOpen = true;
                                        }
                                    }">
        <x-breadcrumb :items="[
            ['label' => 'Finanzas', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Cuentas por Pagar'],
        ]" />

        <x-page-header title="Cuentas por Pagar" subtitle="Gestión de deudas con proveedores."
            icon="fa-file-invoice-dollar">
        </x-page-header>

        <x-modal :title="'Registrar pago a Proveedor'" @keydown.escape.window="closeModal()">
            <x-modal.form x-bind:action="paymentForm.action">
                <div class="mb-3 text-sm">
                    <span class="text-gray-400">Proveedor:</span>
                    <span class="text-gray-100 font-medium" x-text="paymentContext.supplier"></span>
                    <span class="mx-2 text-gray-600">•</span>
                    <span class="text-gray-400">Ref:</span>
                    <span class="text-gray-100 font-mono" x-text="paymentContext.ref"></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2 flex items-center justify-between">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500">Saldo pendiente</div>
                            <div class="text-2xl font-bold text-gray-100">
                                C$ <span x-text="formatCurrency(remainingValue)"></span>
                            </div>
                        </div>
                    </div>
                    <x-inputs.text name="amount" label="Monto a pagar" type="number" step="0.01" min="0" inputmode="decimal"
                        placeholder="0.00" x-model="paymentForm.amount" inputClass="text-right font-mono tabular-nums"
                        class="sm:col-span-2">
                        <x-slot:append>
                            <button type="button" @click="payAll()"
                                class="text-xs font-bold text-purple-400 hover:text-purple-300 px-2 py-1 rounded bg-gray-800 border border-gray-700">
                                TODO
                            </button>
                        </x-slot:append>
                    </x-inputs.text>
                    <input type="hidden" name="currency" :value="paymentForm.currency">
                    <input type="hidden" name="account_payable_id" :value="paymentForm.account_payable_id">
                    <x-inputs.select name="payment_method_id" label="Método de Pago" :options="$methods ?? []"
                        placeholder="Seleccione..." x-model="paymentForm.payment_method_id" />
                    <x-inputs.textarea name="notes" label="Notas" placeholder="Opcional: detalles del pago" rows="3"
                        class="sm:col-span-2" />
                </div>

                <x-slot:footer>
                    <x-modal.button type="button" @click="closeModal()" variant="secondary">
                        Cancelar
                    </x-modal.button>
                    <x-modal.button type="submit" variant="primary">
                        <i class="fas fa-paper-plane mr-2"></i> Registrar Pago
                    </x-modal.button>
                </x-slot:footer>
            </x-modal.form>
        </x-modal>

        <div class="mt-4">
            <x-session-message />
        </div>

        <x-filter-card action="{{ route('admin.account_payables.index') }}" class="gap-y-2">
            <div class="col-span-12 lg:col-span-3">
                <label for="search"
                    class="block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-1">Buscar</label>
                {{-- Ajustar ruta autocomplete de suppliers si existe --}}
                <x-autocomplete name="filter[supplier_id]" :value="request('filter.supplier_id')"
                    url="{{ route('admin.autocomplete.suppliers') }}" placeholder="Nombre del proveedor..." id="search"
                    class="placeholder-gray-500 dark:placeholder-gray-400" />
            </div>

            <div class="col-span-6 lg:col-span-2">
                <x-filter-card.select name="filter[status]" label="Estado" :options="$statuses ?? []"
                    :selected="request('filter.status')" placeholder="Todos" />
            </div>
            <x-inputs.range-datepicker name-from="filter[from]" name-to="filter[to]" label-from="Desde" label-to="Hasta"
                :value-from="request('filter.from')" :value-to="request('filter.to')" class="col-span-12 lg:col-span-4" />

            <div class="col-span-12 lg:col-span-1">
                <x-inputs.button type="submit" variant="secondary" icon="fas fa-search" icon-only
                    class="w-full h-[38px] mt-1" title="Filtrar Resultados" />
            </div>
        </x-filter-card>

        <div class="mt-4">
            <x-table :resource="$accounts">
                <x-slot name="thead">
                    <x-table.th>ID</x-table.th>
                    <x-table.th class="text-center">Fecha</x-table.th>
                    <x-table.th>Proveedor</x-table.th>
                    <x-table.th>Resumen</x-table.th>
                    <x-table.th>Estado</x-table.th>
                    <x-table.th class="text-right">Total</x-table.th>
                    <x-table.th class="text-right">Pagado</x-table.th>
                    <x-table.th class="text-right">Saldo</x-table.th>
                    <x-table.th>Acciones</x-table.th>
                </x-slot>
                <x-slot name="tbody">
                    @foreach ($accounts as $ap)
                        <x-table.tr>
                            <x-table.td-folio :id="$ap->id" />

                            <x-table.td-text variant="muted" align="right" size="sm">
                                {{ $ap->formatted_created_at }}
                            </x-table.td-text>

                            <x-table.td-stacked :top="$ap->supplier_label" :middle="'Registrado por: ' . $ap->attended_by"
                                top-class="text-gray-200 truncate max-w-[200px]" />

                            <x-table.td-summary :summary="$ap->summary" :count="$ap->purchase_details_count" />

                            <x-table.td-badge :color="$ap->status_color" :text="$ap->status_label"
                                class="text-center justify-center" />

                            <x-table.td-text align="right" font="mono" class="tabular-nums">
                                {{ $ap->formatted_total_amount }}
                            </x-table.td-text>

                            <x-table.td-text variant="muted" align="right" font="mono" class="tabular-nums">
                                {{ $ap->formatted_amount_paid }}
                            </x-table.td-text>

                            <x-table.td-text align="right" font="mono" class="tabular-nums {{ $ap->balance_text_class }}">
                                {{ $ap->formatted_balance }}
                            </x-table.td-text>
                            <x-table.actions>
                                @if (in_array($ap->status->value, ['pending', 'partially_paid']))
                                    <button type="button" title="Registrar pago"
                                        class="btn-register-payment inline-flex items-center justify-center h-9 px-3 text-white bg-purple-600 hover:bg-purple-700 rounded-lg focus:outline-none gap-2"
                                        @click="openPaymentModal(
                                                                  '{{ $ap->balance_decimal }}',
                                                                  '{{ $ap->default_payment_method_id ?? '' }}',
                                                                  '{{ $ap->supplier_label }}',
                                                                  '{{ $ap->ref }}',
                                                                  '{{ $ap->condition_label }}',
                                                                  '{{ $ap->currency }}',
                                                                  '{{ $ap->id }}'
                                                                  )">
                                        <i class="fas fa-cash-register"></i>
                                        <span class="hidden sm:inline">Pagar</span>
                                    </button>
                                @endif
                                <x-link :href="route('admin.account_payables.show', $ap)" variant="action" icon="fas fa-eye"
                                    title="Ver detalle">
                                </x-link>
                            </x-table.actions>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </div>
    </div>
@endsection