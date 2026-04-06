<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">

    <div class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
        <span class="block text-base font-semibold text-gray-800 dark:text-gray-200 mb-4">Estado del Usuario</span>
        <div class="flex flex-row justify-between w-full">
            <label
                class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-transparent hover:border-purple-300 transition-colors cursor-pointer flex-1 basis-1/3 max-w-none">
                <input type="hidden" name="is_client" value="0" />
                <x-inputs.checkbox name="is_client" value="1" :checked="old('is_client', isset($entity) ? $entity->is_client : false)" />
                <div class="ml-3 flex-1">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user text-gray-500 dark:text-gray-400"></i>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Cliente</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Marcar si es cliente</p>
                </div>
            </label>

            <label
                class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-transparent hover:border-purple-300 transition-colors cursor-pointer flex-1 basis-1/3 max-w-none">
                <input type="hidden" name="is_supplier" value="0" />
                <x-inputs.checkbox name="is_supplier" value="1" :checked="old('is_supplier', isset($entity) ? $entity->is_supplier : false)" />
                <div class="ml-3 flex-1">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-truck text-gray-500 dark:text-gray-400"></i>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Proveedor</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Marcar si es proveedor</p>
                </div>
            </label>

            <label
                class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-md border border-transparent hover:border-purple-300 transition-colors cursor-pointer flex-1 basis-1/3 max-w-none">
                <input type="hidden" name="is_active" value="0" />
                <x-inputs.checkbox name="is_active" value="1" :checked="old('is_active', isset($entity) ? $entity->is_active : true)" />
                <div class="ml-3 flex-1">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-gray-500"></i>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Activo</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Estado activo del usuario</p>
                </div>
            </label>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-4">
        <x-inputs.text-icon name="first_name" label="Nombres" icon="fas fa-user" placeholder="Escribe los nombres..."
            :value="old('first_name', isset($entity) ? $entity->first_name : '')" required />
        <x-inputs.text-icon name="last_name" label="Apellidos" icon="fas fa-user-tag"
            placeholder="Escribe los apellidos..." :value="old('last_name', isset($entity) ? $entity->last_name : '')" required />
    </div>

    <div class="flex flex-col md:flex-row gap-4 mt-4">
        <x-inputs.text-icon name="identity_card" label="Cédula de Identidad" icon="fas fa-id-card"
            placeholder="Cédula de Identidad..." :value="old('identity_card', isset($entity) ? $entity->identity_card : '')" required />
        <x-inputs.text-icon name="ruc" label="RUC" icon="fas fa-id-card" placeholder="RUC..."
            :value="old('ruc', isset($entity) ? $entity->ruc : '')" required />
        <x-inputs.text-icon name="phone" label="Teléfono" icon="fas fa-phone" placeholder="Teléfono..."
            :value="old('phone', isset($entity) ? $entity->phone : '')" required />
    </div>

    <x-inputs.text-icon class="mt-4" name="email" type="email" label="Email" icon="fas fa-envelope"
        placeholder="Correo electrónico..." :value="old('email', isset($entity) ? $entity->email : '')" />

    <div class="mt-4">
        <x-inputs.location-select
            :department-id="isset($entity) && isset($entity->municipality) ? (string) $entity->municipality->department_id : null"
            :municipality-id="isset($entity) ? (string) $entity->municipality_id : null"
        />
    </div>

    <x-inputs.text-icon class="mt-4" name="address" label="Dirección" icon="fas fa-map-marker-alt"
        placeholder="Dirección..." :value="old('address', isset($entity) ? $entity->address : '')" />

    <x-inputs.textarea class="mt-4" name="description" label="Descripción" placeholder="Escribe una descripción..."
        :value="old('description', isset($entity) ? $entity->description : '')" rows="3" />

    <div class="mt-6">
        <x-inputs.button type="submit" variant="primary" icon="fas fa-paper-plane">
            {{ isset($entity) ? 'Actualizar' : 'Guardar' }}
        </x-inputs.button>
    </div>
</div>
