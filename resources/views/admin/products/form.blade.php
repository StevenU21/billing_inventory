<div class="space-y-6">
    @php
        $statusOptions = [
            'available' => 'Disponible',
            'archived' => 'Archivado',
            'draft' => 'Borrador',
        ];
        $currencyOptions = [
            'NIO' => 'Córdobas (NIO)',
            'USD' => 'Dólares (USD)',
        ];

        $selectedStatus = old('status', $product->status?->value ?? 'available');
        $selectedCurrency = old('currency', $product->exists ? ($product->variants->first()->currency ?? 'NIO') : 'NIO');

        // Prepare initial attributes for JS
        // If old input exists, use it. Otherwise derive from existing variants.
        $initialAttributes = old('attributes', []);

        if (empty($initialAttributes) && $product->exists) {
            // Extract unique attribute names from variant badges accessor we created
            $initialAttributes = array_keys($product->variant_badges['attributes'] ?? []);
        }

        // Prepare initial variants for JS
        // We need to map existing variants to the new JS structure: { id: ..., attributes: { 'Color': 'Red' }, ... }
        $initialVariants = [];
        if (old('variants')) {
            $initialVariants = old('variants');
        } elseif ($product->exists && $product->variants->count() > 0) {
            foreach ($product->variants as $v) {
                $attrs = [];
                foreach ($v->attributeValues as $av) {
                    $attrs[$av->attribute->name] = $av->value;
                }

                $initialVariants[] = [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'price' => $v->price?->getAmount()->toFloat(),
                    'credit_price' => $v->creditPrice?->getAmount()->toFloat(),
                    'currency' => $selectedCurrency,
                    'skuEditable' => false,
                    'attributes' => $attrs,
                ];
            }
        } else {
            // Default empty variant
            $initialVariants[] = [
                'attributes' => [],
                'price' => '',
                'currency' => $selectedCurrency,
                'sku' => '',
                'skuEditable' => false,
            ];
        }
    @endphp

    {{-- Basic Info Section --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Información Básica</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Name --}}
            <x-inputs.text name="name" label="Nombre del Producto" :value="old('name', $product->name ?? '')" placeholder="Ej. Silla de Oficina"
                required />

            {{-- Code --}}
            <x-inputs.text name="code" label="Código Principal" :value="old('code', $product->code ?? '')" placeholder="Ej. PROD-001" />

            {{-- Description --}}
            <div class="md:col-span-2">
                <x-inputs.textarea name="description" label="Descripción" :value="old('description', $product->description ?? '')"
                    placeholder="Detalles del producto..." />
            </div>
        </div>
    </div>

    {{-- Categorization Section --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Categorización y Precios</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Brand --}}
            <x-inputs.select name="brand_id" label="Marca" :options="$brands" :selected="old('brand_id', $product->brand_id ?? '')"
                placeholder="Seleccione Marca" required />

            {{-- Category (Visual helper) --}}
            <x-inputs.select name="category_id_filter" label="Categoría (Filtro Visual)" :options="$categories"
                placeholder="Todas" />

            {{-- Tax --}}
            <x-inputs.select name="tax_id" label="Impuesto" :options="$taxes" :selected="old('tax_id', $product->tax_id ?? '')" required />

            {{-- Unit Measure --}}
            <x-inputs.select name="unit_measure_id" label="Unidad de Medida" :options="$units" :selected="old('unit_measure_id', $product->unit_measure_id ?? '')"
                required />

            {{-- Status --}}
            <x-inputs.select name="status" label="Estado" :options="$statusOptions" :selected="$selectedStatus" required />
        </div>
    </div>

    {{-- Image Upload Section --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Imagen del Producto</h3>
        <div class="grid grid-cols-1 gap-4">
            @if ($product->image_url)
                <div class="mb-2">
                    <img src="{{ $product->image_url }}" alt="Imagen actual"
                        class="w-32 h-32 object-cover rounded-md border">
                </div>
            @endif
            <x-inputs.file name="image" label="Subir Imagen" accept="image/*" />
        </div>
    </div>

    {{-- Dynamic Variants Section --}}
    <div class="space-y-6" x-data="{
        attributes: {{ Illuminate\Support\Js::from($initialAttributes) }},
        // Stores input values for generation: ['S, M, L', 'Red, Blue']
        attributeInputValues: [],
        bulkPrice: '',
        productCurrency: '{{ $selectedCurrency }}',

        variants: {{ Illuminate\Support\Js::from($initialVariants) }},
        availableAttributes: {{ Illuminate\Support\Js::from($availableAttributes ?? []) }},

        init() {
            // Initialize input values to match attributes length
            this.syncInputs();
            this.normalizeVariants();
        },

        normalizeVariants() {
            this.variants = this.variants.map(variant => ({
                ...variant,
                currency: variant.currency || this.productCurrency,
                skuEditable: variant.skuEditable ?? false,
            }));
        },

        syncInputs() {
            while (this.attributeInputValues.length < this.attributes.length) {
                this.attributeInputValues.push('');
            }
        },

        addAttribute() {
            this.attributes.push('');
            this.attributeInputValues.push('');
        },

        removeAttribute(index) {
            // Capture the name needed to be removed from variants
            let nameToRemove = this.attributes[index];

            this.attributes.splice(index, 1);
            this.attributeInputValues.splice(index, 1);

            // Remove this key from all variants
            if (nameToRemove) {
                this.variants.forEach(v => {
                    if (v.attributes && v.attributes[nameToRemove]) {
                        delete v.attributes[nameToRemove];
                    }
                });
            }
        },

        generateVariants() {
            let pools = [];
            // 1. Prepare pools [ ['S','M'], ['Red','Blue'] ]
            for (let i = 0; i < this.attributes.length; i++) {
                let name = this.attributes[i];
                if (!name) continue;

                let raw = this.attributeInputValues[i] || '';
                let split = raw.split(',').map(s => s.trim()).filter(s => s.length > 0);

                if (split.length === 0) {
                    alert('Por favor ingrese valores para el atributo: ' + name);
                    return;
                }

                // Store as objects: { name: 'Size', value: 'S' }
                pools.push(split.map(val => ({ name: name, value: val })));
            }

            if (pools.length === 0) return;

            // 2. Cartesian Product
            let combos = pools.reduce((a, b) => a.flatMap(x => b.map(y => [...x, y])), [
                []
            ]);

            if (combos.length > 50) {
                if (!confirm('Se generarán ' + combos.length + ' variantes. ¿Continuar?')) return;
            }

            if (this.variants.length > 1 || (Object_keys(this.variants[0].attributes || {}).length > 0) || this.variants[0].sku) {
                if (!confirm('Esto reemplazará la lista actual de variantes. ¿Desea continuar?')) return;
            }

            // 3. Create Variants
            this.variants = combos.map(combo => {
                // combo is array of {name, value}
                let attrs = {};
                combo.forEach(item => {
                    attrs[item.name] = item.value;
                });

                return {
                    id: null,
                    attributes: attrs,
                    sku: '',
                    skuEditable: false,
                    price: this.bulkPrice || '',
                    credit_price: '',
                    currency: this.productCurrency
                };
            });
        },

        // Helper for checking object keys
        Object_keys(obj) {
            return obj ? Object.keys(obj) : [];
        },

        addVariant() {
            // create empty attributes object with keys from defined attributes (empty values)
            let emptyAttrs = {};
            this.attributes.forEach(a => {
                if (a) emptyAttrs[a] = '';
            });

            this.variants.push({
                attributes: emptyAttrs,
                price: this.bulkPrice || '',
                credit_price: '',
                currency: this.productCurrency,
                sku: '',
                skuEditable: false
            });
        },

        syncVariantCurrency() {
            this.variants = this.variants.map(variant => ({
                ...variant,
                currency: this.productCurrency,
            }));
        },

        toggleSkuEditable(index) {
            this.variants[index].skuEditable = ! this.variants[index].skuEditable;
        },

        removeVariant(index) {
            if (this.variants.length > 1) {
                this.variants.splice(index, 1);
            }
        }
    }">
        {{-- 1. Attributes Configuration --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Configuración de Atributos</h3>
                <button type="button" @click="addAttribute()" x-show="attributes.length < 5"
                    class="text-sm bg-purple-100 text-purple-700 px-3 py-1 rounded hover:bg-purple-200 transition">
                    + Agregar Atributo
                </button>
            </div>

            <p class="text-sm text-gray-500 mb-4 px-1" x-show="attributes.length === 0">
                Agrega atributos (ej. Talla, Color) para generar variantes. Si no agregas ninguno, se creará un producto
                simple.
            </p>

            <div class="space-y-3">
                <template x-for="(attr, index) in attributes" :key="index">
                    <div class="flex gap-2 items-end">
                        {{-- Attribute Name Input (with autocomplete logic baked in x-inputs or simplified) --}}
                        <div class="w-1/3">
                            <label class="block text-xs font-medium text-gray-500 mb-1"
                                x-text="'Atributo ' + (index + 1)"></label>
                            <x-inputs.select-autocomplete :label="null" name="attributes[]"
                                model="attributes[index]" suggestions-var="availableAttributes"
                                placeholder="Ej. Talla, Color" required="true" />
                        </div>

                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Valores (separados por coma,
                                para generar)</label>
                            <input type="text" x-model="attributeInputValues[index]" placeholder="Ej. S, M, L"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-800 dark:border-gray-700/50 dark:text-gray-300 text-sm h-[38px]">
                        </div>

                        <button type="button" @click="removeAttribute(index)"
                            class="mb-1.5 p-2 text-red-500 hover:bg-red-50 rounded-md">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </template>
            </div>

            {{-- Generator Actions --}}
            <div class="mt-6 pt-4 border-t border-gray-900/20 dark:border-gray-900/20 flex flex-wrap gap-4 items-end justify-between"
                x-show="attributes.length > 0">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Precio Base (Opcional)</label>
                    <input type="number" step="0.01" x-model="bulkPrice" placeholder="0.00"
                        class="w-40 rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-800 dark:border-gray-700/50 dark:text-white text-sm h-[38px]">
                </div>
                <button type="button" @click="generateVariants()"
                    class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition shadow-sm text-sm font-medium">
                    <i class="fas fa-magic mr-2"></i> Generar Combinaciones
                </button>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="currency"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Moneda del Producto <span class="text-red-500">*</span>
                    </label>
                    <select name="currency" id="currency" x-model="productCurrency" @change="syncVariantCurrency()" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-purple-500 focus:border-purple-500">
                        @foreach ($currencyOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Esta moneda se aplicará a todas las variantes.
                    </p>
                </div>
            </div>
        </div>

        {{-- 2. Variants Table/Grid --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Lista de Variantes</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Filas compactas para evitar scroll innecesario.
                    </p>
                </div>
                <button type="button" @click="addVariant()"
                    class="text-sm bg-blue-100 text-blue-600 px-3 py-2 rounded hover:bg-blue-200 transition">
                    + Agregar Variante
                </button>
            </div>

            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Atributos</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SKU</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Precio</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Precio Crédito</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        <template x-for="(variant, index) in variants" :key="index">
                            <tr class="align-top hover:bg-gray-50/70 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-4 w-[38%]">
                                    <input type="hidden" :name="`variants[${index}][id]`" :value="variant.id">
                                    <input type="hidden" :name="`variants[${index}][currency]`" :value="variant.currency">

                                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                        <template x-for="(attrName, attrIdx) in attributes" :key="attrIdx">
                                            <div>
                                                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                                                    x-text="attrName || 'Atributo ' + (attrIdx + 1)"></label>
                                                <input type="text"
                                                    :name="`variants[${index}][attributes][${attrName}]`"
                                                    x-model="variant.attributes[attrName]"
                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-purple-500 focus:ring-purple-500 text-sm h-9"
                                                    :placeholder="attrName" required>
                                            </div>
                                        </template>

                                        <div x-show="attributes.length === 0" class="sm:col-span-2 xl:col-span-3 rounded-md border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/40 px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            Producto general
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4 w-[18%]">
                                    <div class="flex items-start gap-2">
                                        <div class="min-w-0 flex-1">
                                            <input type="text" :name="`variants[${index}][sku]`" x-model="variant.sku"
                                                :readonly="!variant.skuEditable"
                                                :class="variant.skuEditable ? 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100' : 'bg-gray-100 dark:bg-gray-900/50 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed'"
                                                class="w-full rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm h-9"
                                                :placeholder="variant.skuEditable ? 'SKU manual' : 'SKU automático'">
                                            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400" x-text="variant.skuEditable ? 'Edición manual activa' : 'Auto hasta pulsar Editar'"></p>
                                        </div>
                                        <button type="button" @click="toggleSkuEditable(index)"
                                            class="mt-0.5 shrink-0 rounded-md border border-gray-300 dark:border-gray-600 px-3 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            <span x-text="variant.skuEditable ? 'Bloquear' : 'Editar'"></span>
                                        </button>
                                    </div>
                                </td>

                                <td class="px-4 py-4 w-[14%]">
                                    <input type="number" step="0.01" :name="`variants[${index}][price]`"
                                        x-model="variant.price" required
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-purple-500 focus:ring-purple-500 text-sm h-9">
                                </td>

                                <td class="px-4 py-4 w-[14%]">
                                    <input type="number" step="0.01" :name="`variants[${index}][credit_price]`"
                                        x-model="variant.credit_price"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-purple-500 focus:ring-purple-500 text-sm h-9"
                                        placeholder="Opcional">
                                </td>

                                <td class="px-4 py-4 text-right w-[8%]">
                                    <button type="button" @click="removeVariant(index)"
                                        class="inline-flex items-center justify-center rounded-md border border-red-200 dark:border-red-900/60 px-3 py-2 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                                        x-show="variants.length > 1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-gray-400 mt-4">
                * El SKU se genera automáticamente hasta que lo desbloquees.
            </p>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex justify-end pt-4">
        <x-inputs.button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white">
            Guardar Producto
        </x-inputs.button>
    </div>
</div>
