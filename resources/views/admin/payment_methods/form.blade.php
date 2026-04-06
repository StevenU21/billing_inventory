<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
    <!-- Name Field -->
    <x-inputs.text name="name" label="Nombre" placeholder="Escribe un nombre..." :value="old('name', isset($paymentMethod) ? $paymentMethod->name : '')" required icon="fas fa-user" />

    <!-- Campo Descripción -->
    <div class="mt-4">
        <x-inputs.textarea name="description" label="Descripción" placeholder="Escribe una descripción..."
            :value="old('description', isset($paymentMethod) ? $paymentMethod->description : '')" rows="3"
            icon="fas fa-comment" />
    </div>

    <!-- Is Cash Field -->
    <div class="mt-4">
        <x-inputs.checkbox name="is_cash" label="Es efectivo" :checked="old('is_cash', isset($paymentMethod) ? $paymentMethod->is_cash : false)"
            help="Marca esta opción si este método de pago es efectivo (afecta el flujo de caja)" />
    </div>

    <!-- Is Active Field -->
    <div class="mt-4">
        <x-inputs.checkbox name="is_active" label="Está activo" :checked="old('is_active', isset($paymentMethod) ? $paymentMethod->is_active : true)"
            help="Marca esta opción si este método de pago está disponible para usar" />
    </div>

    <!-- Submit Button -->
    <div class="mt-6">
        <x-inputs.button type="submit">
            <i class="fas fa-paper-plane mr-2"></i> {{ isset($paymentMethod) ? 'Actualizar' : 'Guardar' }}
        </x-inputs.button>
    </div>
</div>