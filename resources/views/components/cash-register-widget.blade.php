@props(['class' => ''])

<div x-data="{
    hasSession: {{ $hasSession ? 'true' : 'false' }},
    expectedBalance: '{{ $expectedBalance }}',
    totalIncome: '{{ $totalIncome }}',
    totalExpense: '{{ $totalExpense }}',
    sessionId: {{ $sessionId ?? 'null' }},
    isOpen: false,
    showOpenModal: false,
    loading: false,

    // Open Form Data
    openForm: {
        opening_balance: '',
        notes: ''
    },

    async submitOpen() {
        this.loading = true;
        try {
            const response = await fetch('{{ route('admin.cash-register.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify(this.openForm)
            });

            const data = await response.json();

            if (response.ok) {
                // Reload page to get fresh data from View Composer
                window.location.reload(); 
            } else {
                alert(data.message || 'Error al abrir caja');
            }
        } catch (e) {
            console.error(e);
            alert('Error de conexión');
        } finally {
            this.loading = false;
        }
    }
}" class="{{ $class }}">

    {{-- Cash Register Indicator --}}
    <div class="relative">
        <button @click="isOpen = !isOpen" @click.outside="isOpen = false" class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200
                   hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
            :class="hasSession 
                ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' 
                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'">

            {{-- Icon --}}
            <i class="fas fa-cash-register text-sm"></i>

            {{-- Expected Balance Summary (hidden on mobile) --}}
            <span class="hidden sm:inline text-xs font-medium font-mono" x-show="hasSession">
                <span class="text-green-600 dark:text-green-400" x-text="expectedBalance"></span>
            </span>

            {{-- Status Dot --}}
            <span class="w-2 h-2 rounded-full animate-pulse"
                :class="hasSession ? 'bg-green-500' : 'bg-gray-400'"></span>
        </button>

        {{-- Configuration Dropdown --}}
        <div x-show="isOpen" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
            style="display: none;">

            <div class="p-4">
                {{-- Header --}}
                <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center transition-colors"
                        :class="hasSession ? 'bg-green-100 dark:bg-green-900/30' : 'bg-gray-100 dark:bg-gray-700'">
                        <i class="fas fa-cash-register transition-colors"
                            :class="hasSession ? 'text-green-600 dark:text-green-400' : 'text-gray-500'"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            Caja Registradora
                        </p>
                        <p class="text-xs transition-colors"
                            :class="hasSession ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                            <span x-text="hasSession ? 'Sesión Activa' : 'Sin Sesión'"></span>
                        </p>
                    </div>
                </div>

                {{-- Income/Expense Summary (when session active) --}}
                <template x-if="hasSession">
                    <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Ingresos</p>
                                <p class="text-lg font-bold text-green-600 dark:text-green-400 font-mono tabular-nums"
                                    x-text="totalIncome"></p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Egresos</p>
                                <p class="text-lg font-bold text-red-600 dark:text-red-400 font-mono tabular-nums"
                                    x-text="totalExpense"></p>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Actions --}}
                <div class="space-y-2">
                    <template x-if="hasSession">
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ $showUrl }}"
                                class="flex flex-col items-center justify-center gap-1 p-2 text-xs text-gray-700 dark:text-gray-300 
                                      bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors border border-gray-200 dark:border-gray-600">
                                <i class="fas fa-eye text-lg mb-1 text-purple-600 dark:text-purple-400"></i>
                                Ver Detalle
                            </a>

                            <a href="{{ $closeUrl }}"
                                class="flex flex-col items-center justify-center gap-1 p-2 text-xs text-red-700 dark:text-red-400 
                                           bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors border border-red-200 dark:border-red-900/50">
                                <i class="fas fa-store-slash text-lg mb-1"></i>
                                Cerrar Caja
                            </a>
                        </div>
                    </template>

                    <template x-if="!hasSession">
                        <button @click="showOpenModal = true; isOpen = false" class="w-full flex items-center justify-center gap-2 px-3 py-3 text-sm font-medium text-white 
                                       bg-green-600 hover:bg-green-700 active:bg-green-800
                                       rounded-lg transition-colors shadow-sm">
                            <i class="fas fa-door-open"></i>
                            Abrir Caja Ahora
                        </button>
                    </template>

                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ $hasSession ? $showUrl : route('admin.cash-register.index') }}" class="flex items-center justify-center gap-2 w-full px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400 
                                   hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                            <i class="fas fa-history"></i>
                            Ver Historial completo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- OPEN REGISTER MODAL --}}
    <x-modal showVar="showOpenModal" onClose="showOpenModal = false">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-lg text-green-600 dark:text-green-400">
                    <i class="fas fa-cash-register"></i>
                </div>
                Apertura de Caja
            </h2>

            <form @submit.prevent="submitOpen">
                <div class="space-y-4">
                    <div class="space-y-4">
                        <div x-data="{
                        isLoadingLast: false,
                        async useLastClosing() {
                            this.isLoadingLast = true;
                            try {
                                const response = await fetch('{{ route('api.cash-register.last-closing-balance') }}');
                                const data = await response.json();
                                if(data.amount > 0) {
                                    openForm.opening_balance = data.amount;
                                } else {
                                    alert('No se encontró un saldo de cierre anterior válido.');
                                }
                            } catch(e) {
                                console.error(e);
                                alert('Error al obtener el último saldo.');
                            } finally {
                                this.isLoadingLast = false;
                            }
                        }
                    }">
                            <div class="flex justify-between items-end mb-1">
                                <label for="opening_balance"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Saldo Inicial (C$)
                                </label>
                                <button type="button" @click="useLastClosing" :disabled="isLoadingLast"
                                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 underline focus:outline-none">
                                    <span x-show="!isLoadingLast">Usar cierre anterior</span>
                                    <span x-show="isLoadingLast"><i class="fas fa-spinner fa-spin"></i>
                                        Cargando...</span>
                                </button>
                            </div>
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">C$</span>
                                </div>
                                <input type="number" step="0.01" min="0" x-model="openForm.opening_balance" required
                                    autofocus
                                    class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm sm:text-lg font-mono">
                            </div>
                        </div>

                        <div>
                            <label for="open_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notas (Opcional)
                            </label>
                            <textarea x-model="openForm.notes" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm sm:text-sm"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button @click="showOpenModal = false" type="button"
                            class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="loading"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <span x-show="loading" class="mr-2"><i class="fas fa-spinner fa-spin"></i></span>
                            Abrir Caja
                        </button>
                    </div>
            </form>
        </div>
    </x-modal>

</div>