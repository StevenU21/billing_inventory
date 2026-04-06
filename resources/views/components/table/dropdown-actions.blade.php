@props([])

<td class="px-4 py-3 whitespace-nowrap text-center">
    <div class="relative inline-block" x-data="{
        open: false,
        scrollHandler: null,
        resizeHandler: null,
        updatePosition() {
            if (this.open) {
                const button = this.$refs.button;
                const dropdown = this.$refs.dropdown;
                const rect = button.getBoundingClientRect();
                const dropdownHeight = dropdown.offsetHeight;
                const viewportHeight = window.innerHeight;

                // Posicionar el dropdown
                dropdown.style.left = (rect.right - 192) + 'px'; // 192px = w-48

                // Verificar si hay espacio abajo, si no, abrir hacia arriba
                if (rect.bottom + dropdownHeight > viewportHeight) {
                    dropdown.style.top = (rect.top - dropdownHeight - 8) + 'px';
                } else {
                    dropdown.style.top = (rect.bottom + 8) + 'px';
                }
            }
        },
        init() {
            // Crear handlers para scroll y resize
            this.scrollHandler = () => {
                if (this.open) {
                    this.open = false;
                }
            };

            this.resizeHandler = () => {
                if (this.open) {
                    this.open = false;
                }
            };

            // Agregar listeners
            window.addEventListener('scroll', this.scrollHandler, true);
            window.addEventListener('resize', this.resizeHandler);

            // Limpiar al destruir
            this.$watch('open', value => {
                if (value) {
                    this.$nextTick(() => this.updatePosition())
                }
            });
        },
        destroy() {
            window.removeEventListener('scroll', this.scrollHandler, true);
            window.removeEventListener('resize', this.resizeHandler);
        }
    }" @click.away="open = false" x-init="init()" x-destroy="destroy()">
        {{-- Trigger Button --}}
        <button @click="open = !open" x-ref="button"
            class="p-2 text-gray-400 hover:text-purple-400 hover:bg-gray-800/50 rounded-lg transition-colors"
            type="button" aria-label="Abrir menú de acciones" title="Acciones">
            <i class="fas fa-ellipsis-v"></i>
        </button>

        {{-- Dropdown Menu --}}
        <div x-show="open" x-ref="dropdown" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="fixed z-50 w-48 rounded-lg bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-700"
            role="menu" aria-orientation="vertical" style="display: none;">
            <div class="py-2 px-1" role="none">
                {{ $slot }}
            </div>
        </div>
    </div>
</td>