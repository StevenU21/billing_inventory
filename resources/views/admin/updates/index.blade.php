@extends('layouts.app')
@section('title', 'Actualizaciones')

@section('content')
@php($state = $updaterState)
<div class="px-4 sm:px-6 lg:px-8 mx-auto">
    <x-breadcrumb :parents="[
        ['label' => 'Módulo de Administración', 'href' => route('dashboard.index'), 'icon' => 'fa-cogs'],
    ]" :current="'Actualizaciones'" />

    <x-page-header title="Actualizaciones"
        subtitle="Supervisa el estado del actualizador y dispara acciones manuales cuando lo necesites."
        icon="fa-sync-alt">
        <div class="flex flex-col gap-2 bg-white/10 backdrop-blur rounded-xl px-4 py-3 text-white min-w-[220px]">
            <p class="text-xs uppercase tracking-wide text-white/70">Versión instalada</p>
            <p id="installed-version" class="text-lg font-semibold">
                {{ $currentVersion }}
            </p>
            <p id="updater-provider" class="text-xs text-white/80">
                Proveedor: {{ $updaterProvider }}
            </p>
            <p id="updater-available-info"
                class="text-xs text-emerald-200 mt-1 {{ $state['availableVersion'] ? '' : 'hidden' }}">
                {{ $state['availableVersion'] ? 'Actualización disponible: ' . $state['availableVersion'] : '' }}
            </p>
        </div>
    </x-page-header>

    <!-- Success Messages -->
    <div class="mt-4">
        <x-session-message />
    </div>
    <!-- End Success Messages -->

    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Validación manual</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Dispara las acciones del updater cuando lo
                        necesites.</p>
                </div>
                <span
                    class="inline-flex items-center text-xs px-3 py-1 rounded-full {{ $updaterEnabled ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-300' }}">
                    {{ $updaterEnabled ? 'Disponible' : 'Fuera de servicio' }}
                </span>
            </div>
            <div class="mt-5 space-y-3">
                @can('check', \App\Services\NativeUpdaterStatusStore::class)
                    <button data-updater-action="check" @disabled(!$updaterEnabled)
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:hover:bg-indigo-500 hover:text-white dark:hover:text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="fas fa-search"></i>
                        Validar actualización
                    </button>
                @endcan
                @can('download', \App\Services\NativeUpdaterStatusStore::class)
                    <button data-updater-action="download" @disabled(!$updaterEnabled || empty($state['canDownload']))
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-semibold text-indigo-700 bg-indigo-100 hover:bg-indigo-600 hover:text-white dark:bg-indigo-900/30 dark:text-indigo-200 dark:hover:bg-indigo-700 dark:hover:text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="fas fa-download"></i>
                        Descargar actualización
                    </button>
                @endcan
                @can('install', \App\Services\NativeUpdaterStatusStore::class)
                    <button data-updater-action="install" @disabled(!$updaterEnabled || empty($state['canInstall']))
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-semibold text-rose-600 bg-rose-100 hover:bg-rose-500 dark:bg-rose-900/30 dark:text-rose-200 hover:text-white dark:hover:text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="fas fa-power-off"></i>
                        Actualizar y cerrar
                    </button>
                @endcan
            </div>
            <div id="download-progress" class="mt-4 {{ ($state['stage'] ?? null) === 'downloading' ? '' : 'hidden' }}">
                <div class="h-3 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                    <div id="download-progress-bar" class="h-full bg-indigo-500 transition-all"
                        style="width: {{ $state['downloadPercent'] ?? 0 }}%"></div>
                </div>
                <p id="download-progress-text" class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    {{ ($state['downloadPercent'] ?? 0) > 0 ? round($state['downloadPercent']) . '% en curso' : '' }}
                </p>
            </div>
            <p id="updater-feedback" class="mt-4 text-sm text-gray-500 dark:text-gray-400 min-h-[1.5rem]">
                {{ $state['lastMessage'] ?? '' }}
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Eventos recientes</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Mantén esta vista abierta para ver los cambios
                        de estado.</p>
                </div>
                @can('viewAny', \App\Services\NativeUpdaterStatusStore::class)
                    <button id="refresh-history"
                        class="text-xs inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-500">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                @endcan
            </div>
            <ul id="updater-history" class="space-y-3">
                @forelse ($statusHistory as $entry)
                    <li class="p-3 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $entry['message'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ \Carbon\Carbon::parse($entry['timestamp'])->format('d/m/Y H:i:s') }}
                            @if (!empty($entry['meta']))
                                ·
                                {{ collect($entry['meta'])->filter()->map(fn($value, $key) => "$key: $value")->implode(' · ') }}
                            @endif
                        </p>
                    </li>
                @empty
                    <li class="text-sm text-gray-500 dark:text-gray-400">Sin eventos todavía.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const feedback = document.getElementById('updater-feedback');
            const historyList = document.getElementById('updater-history');
            const refreshBtn = document.getElementById('refresh-history');
            const actions = {
                check: '{{ route('native-app.updates.check') }}',
                download: '{{ route('native-app.updates.download') }}',
                install: '{{ route('native-app.updates.install') }}',
                history: '{{ route('native-app.updates.history') }}',
            };
            const installedVersion = document.getElementById('installed-version');
            const availableInfo = document.getElementById('updater-available-info');
            const stageLabel = document.getElementById('updater-stage-label');
            const lastMessage = document.getElementById('updater-last-message');
            const progressWrapper = document.getElementById('download-progress');
            const progressBar = document.getElementById('download-progress-bar');
            const progressText = document.getElementById('download-progress-text');
            let updaterState = @json($state);

            async function callAction(action) {
                const buttons = document.querySelectorAll('[data-updater-action]');
                buttons.forEach(btn => btn.disabled = true);
                feedback.textContent = 'Procesando...';
                try {
                    const response = await fetch(actions[action], {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await response.json();
                    feedback.textContent = data.message || 'Operación enviada.';
                } catch (error) {
                    console.error(error);
                    feedback.textContent = 'Ocurrió un error inesperado.';
                } finally {
                    buttons.forEach(btn => btn.disabled = false);
                    setTimeout(() => refreshHistory(), 1000);
                }
            }

            async function refreshHistory() {
                try {
                    const response = await fetch(actions.history, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });
                    const data = await response.json();
                    renderHistory(data.history || []);
                    syncState(data.state || updaterState);
                } catch (error) {
                    console.error(error);
                }
            }

            function renderHistory(entries) {
                if (!entries.length) {
                    historyList.innerHTML =
                        '<li class="text-sm text-gray-500 dark:text-gray-400">Sin eventos todavía.</li>';
                    return;
                }
                historyList.innerHTML = '';
                entries.forEach(entry => {
                    const li = document.createElement('li');
                    li.className =
                        'p-3 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40';
                    const title = document.createElement('p');
                    title.className = 'text-sm font-semibold text-gray-800 dark:text-gray-100';
                    title.textContent = entry.message;
                    const meta = document.createElement('p');
                    meta.className = 'text-xs text-gray-500 dark:text-gray-400 mt-1';
                    const timestamp = entry.timestamp ? new Date(entry.timestamp).toLocaleString() : '';
                    const extra = entry.meta ? Object.entries(entry.meta)
                        .filter(([, value]) => value !== null && value !== '')
                        .map(([key, value]) => `${key}: ${value}`)
                        .join(' · ') : '';
                    meta.textContent = extra ? `${timestamp} · ${extra}` : timestamp;
                    li.appendChild(title);
                    li.appendChild(meta);
                    historyList.appendChild(li);
                });
            }

            function formatBytes(bytes = 0) {
                if (!bytes) return '0 B';
                const units = ['B', 'KB', 'MB', 'GB'];
                const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
                const value = bytes / Math.pow(1024, index);
                return `${value.toFixed(1)} ${units[index]}`;
            }

            function stageLabelText(stage) {
                const labels = {
                    idle: 'Sin verificar',
                    checking: 'Comprobando',
                    available: 'Actualización encontrada',
                    download_requested: 'Conectando',
                    downloading: 'Descargando',
                    downloaded: 'Lista para instalar',
                    cancelled: 'Descarga cancelada',
                    error: 'Error',
                };
                return labels[stage] || 'Sin estado';
            }

            function syncState(state) {
                updaterState = state;
                if (lastMessage && state.lastMessage) {
                    lastMessage.textContent = state.lastMessage;
                }
                if (stageLabel) {
                    stageLabel.textContent = stageLabelText(state.stage);
                }
                const canCheck = {{ $updaterEnabled ? 'true' : 'false' }};
                const downloadBtn = document.querySelector('[data-updater-action="download"]');
                const installBtn = document.querySelector('[data-updater-action="install"]');
                const checkBtn = document.querySelector('[data-updater-action="check"]');
                if (downloadBtn) downloadBtn.disabled = !({{ $updaterEnabled ? 'true' : 'false' }} && state
                    .canDownload);
                if (installBtn) installBtn.disabled = !({{ $updaterEnabled ? 'true' : 'false' }} && state
                    .canInstall);
                if (checkBtn) checkBtn.disabled = !canCheck;

                if (state.availableVersion && availableInfo) {
                    availableInfo.classList.remove('hidden');
                    availableInfo.textContent = `Actualización disponible: ${state.availableVersion}`;
                } else if (availableInfo) {
                    availableInfo.classList.add('hidden');
                    availableInfo.textContent = '';
                }

                if (state.stage === 'downloading') {
                    progressWrapper?.classList.remove('hidden');
                    const percent = Math.max(0, Math.min(100, state.downloadPercent || 0));
                    if (progressBar) progressBar.style.width = `${percent}%`;
                    if (progressText) {
                        const transferred = formatBytes(state.downloadTransferred || 0);
                        const total = formatBytes(state.downloadTotal || 0);
                        progressText.textContent = `${percent.toFixed(0)}% (${transferred} / ${total})`;
                    }
                } else {
                    progressWrapper?.classList.add('hidden');
                }

                if (feedback && state.lastMessage) {
                    feedback.textContent = state.lastMessage;
                }
            }

            document.querySelectorAll('[data-updater-action]').forEach(button => {
                button.addEventListener('click', () => callAction(button.dataset.updaterAction));
            });
            if (refreshBtn) {
                refreshBtn.addEventListener('click', refreshHistory);
            }
            setInterval(refreshHistory, 5000);
            syncState(updaterState);
        });
    </script>
@endpush