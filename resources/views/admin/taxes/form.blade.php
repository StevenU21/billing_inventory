<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
    <!-- Name Field -->
    <x-inputs.text
        name="name"
        label="Nombre"
        placeholder="Escribe un nombre..."
        :value="old('name', isset($tax) ? $tax->name : '')"
        required
        icon="fas fa-user"
    />

    <!-- Campo Percentaje -->
    <div class="mt-4">
        <x-inputs.text
            name="percentage"
            label="Porcentaje"
            type="number"
            placeholder="Escribe un porcentaje..."
            :value="old('percentage', isset($tax) ? $tax->percentage : '')"
            required
            step="0.01"
            inputmode="decimal"
            pattern="[0-9]+([\.,][0-9]{1,2})?"
            icon="fas fa-percent"
            min="0"
            max="100"
        />
    </div>

    <!-- Submit Button -->
    <div class="mt-6">
        <x-inputs.button type="submit">
            <i class="fas fa-paper-plane mr-2"></i> {{ isset($tax) ? 'Actualizar' : 'Guardar' }}
        </x-inputs.button>
    </div>
</div>
