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
                    'currency' => $v->currency,
                    'attributes' => $attrs,
                ];
            }
        } else {
            // Default empty variant
            $initialVariants[] = [
                'attributes' => [],
                'price' => '',
                'currency' => 'NIO',
                'sku' => '',
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

        variants: {{ Illuminate\Support\Js::from($initialVariants) }},
        availableAttributes: {{ Illuminate\Support\Js::from($availableAttributes ?? []) }},

        init() {
            // Initialize input values to match attributes length
            this.syncInputs();
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
                    price: this.bulkPrice || '',
                    credit_price: '',
                    currency: 'NIO'
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
                currency: 'NIO',
                sku: ''
            });
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
        </div>

        {{-- 2. Variants Table/Grid --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Lista de Variantes</h3>
                <button type="button" @click="addVariant()"
                    class="text-sm bg-blue-100 text-blue-600 px-3 py-1 rounded hover:bg-blue-200">
                    + Agregar Variante
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(variant, index) in variants" :key="index">
                    <div
                        class="border border-gray-800/50 dark:border-gray-700/30 rounded-md p-4 relative bg-gray-50 dark:bg-gray-700">
                        <button type="button" @click="removeVariant(index)"
                            class="absolute top-2 right-2 text-red-500 hover:text-red-700" x-show="variants.length > 1">
                            <i class="fas fa-trash"></i>
                        </button>

                        {{-- Hidden ID --}}
                        <input type="hidden" :name="`variants[${index}][id]`" :value="variant.id">

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                            {{-- Dynamic Attribute Inputs --}}
                            <template x-for="(attrName, attrIdx) in attributes" :key="attrIdx">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 mb-1"
                                        x-text="attrName || 'Atributo '+(attrIdx+1)"></label>
                                    <input type="text" {{-- Name format: variants[0][attributes][Color] --}}
                                        :name="`variants[${index}][attributes][${attrName}]`"
                                        x-model="variant.attributes[attrName]"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-800 dark:border-gray-700/50 dark:text-white"
                                        :placeholder="attrName" required>
                                </div>
                            </template>

                            {{-- Fallback for no attributes --}}
                            <div class="md:col-span-3" x-show="attributes.length === 0">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Nombre / Referencia</label>
                                {{-- For simple products, we might still send empty attributes or a default one?
                                     Actually, Request expects variants.*.attributes. It can be empty.
                                     But we usually want a label.
                                     Let's just not send attributes here, so it is a simple product.
                                     But wait, Request validation might expect something.
                                     Actually, existing logic used option1 for name if no options.
                                     We can't easily replicate that if we enforce attributes.
                                     Let's assume simple products have NO attributes.
                                --}}
                                <p class="text-sm text-gray-400 py-2">Producto General</p>
                            </div>

                            {{-- SKU --}}
                            <div class="md:col-span-3">
                                <label class="block text-xs font-medium text-gray-500 mb-1">SKU (Auto)</label>
                                <input type="text" :name="`variants[${index}][sku]`" x-model="variant.sku"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-800 dark:border-gray-700/50 dark:text-white"
                                    placeholder="Dejar vacío para auto">
                            </div>

                            {{-- Price --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Precio *</label>
                                <input type="number" step="0.01" :name="`variants[${index}][price]`"
                                    x-model="variant.price" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-800 dark:border-gray-700/50 dark:text-white">
                            </div>

                            {{-- Credit Price --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Precio Crédito</label>
                                <input type="number" step="0.01" :name="`variants[${index}][credit_price]`"
                                    x-model="variant.credit_price"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-800 dark:border-gray-700/50 dark:text-white"
                                    placeholder="(Opcional)">
                            </div>

                            {{-- Currency --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Moneda *</label>
                                <select :name="`variants[${index}][currency]`" x-model="variant.currency" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-800 dark:border-gray-700/50 dark:text-white">
                                    @foreach ($currencyOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <p class="text-xs text-gray-400 mt-4">
                * Los campos de SKU se generarán automáticamente si se dejan vacíos.
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
