<!-- Name Field -->
<x-inputs.text name="name" label="Nombre" placeholder="Escribe un nombre..." :value="old('name', isset($category) ? $category->name : '')" required icon="fas fa-user" />

<!-- Campo Descripción -->
<div class="mt-4">
    <x-inputs.textarea name="description" label="Descripción" placeholder="Escribe una descripción..."
        :value="old('description', isset($category) ? $category->description : '')" rows="3" icon="fas fa-comment" />
</div>

<!-- Submit Button -->
<div class="mt-6 flex space-x-4">
    <x-inputs.button type="submit">
        <i class="fas fa-paper-plane mr-2"></i> {{ isset($category) ? 'Actualizar' : 'Guardar' }}
    </x-inputs.button>
</div>