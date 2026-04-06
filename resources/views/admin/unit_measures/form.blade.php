<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
    <!-- Name Field -->
    <x-inputs.text name="name" label="Nombre" placeholder="Escribe un nombre..." :value="old('name', isset($unitMeasure) ? $unitMeasure->name : '')" required icon="fas fa-user" />

    <!-- Abbreviation Field -->
    <div class="mt-4">
        <x-inputs.text name="abbreviation" label="Abreviatura" placeholder="Ej: kg, m, l..." :value="old('abbreviation', isset($unitMeasure) ? $unitMeasure->abbreviation : '')" required maxlength="10" icon="fas fa-ruler" />
    </div>

    <!-- Campo Descripción -->
    <div class="mt-4">
        <x-inputs.textarea name="description" label="Descripción" placeholder="Escribe una descripción..."
            :value="old('description', isset($unitMeasure) ? $unitMeasure->description : '')" rows="3"
            icon="fas fa-comment" />
    </div>

    <!-- Allows Decimals Field -->
    <div class="mt-4">
        <x-inputs.checkbox name="allows_decimals" label="Permite decimales" :checked="old('allows_decimals', isset($unitMeasure) ? $unitMeasure->allows_decimals : false)"
            help="Marca esta opción si la unidad de medida permite cantidades con decimales (ej: 1.5 kg)" />
    </div>

    <!-- Submit Button -->
    <div class="mt-6">
        <x-inputs.button type="submit">
            <i class="fas fa-paper-plane mr-2"></i> {{ isset($unitMeasure) ? 'Actualizar' : 'Guardar' }}
        </x-inputs.button>
    </div>
</div>