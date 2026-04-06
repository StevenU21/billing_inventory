<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
    <!-- Name Field -->
    <x-inputs.text name="name" label="Nombre" placeholder="Escribe un nombre..." :value="old('name', isset($brand) ? $brand->name : '')" required icon="fas fa-user" />

    <!-- Category Field -->
    <div class="mt-4">
        <x-inputs.select name="category_id" label="Categoría" :options="$categories->pluck('name', 'id')->toArray()"
            :selected="old('category_id', isset($brand) ? $brand->category_id : '')"
            placeholder="Seleccione una categoría" required />
    </div>

    <!-- Campo Descripción -->
    <div class="mt-4">
        <x-inputs.textarea name="description" label="Descripción" placeholder="Escribe una descripción..."
            :value="old('description', isset($brand) ? $brand->description : '')" rows="3" icon="fas fa-comment" />
    </div>

    <!-- Submit Button -->
    <div class="mt-6">
        <x-inputs.button type="submit">
            <i class="fas fa-paper-plane mr-2"></i> {{ isset($brand) ? 'Actualizar' : 'Guardar' }}
        </x-inputs.button>
    </div>
</div>