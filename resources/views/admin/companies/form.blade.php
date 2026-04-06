<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">

    <!-- Nombre -->
    <div class="flex flex-col md:flex-row gap-4">
        <label class="block text-sm w-full">
            <span class="text-gray-700 dark:text-gray-400">Nombre</span>
            <div class="relative text-gray-500 focus-within:text-purple-600 dark:focus-within:text-purple-400">
                <input name="name" type="text"
                    class="block w-full pl-10 mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:shadow-outline-purple dark:focus:shadow-outline-gray @error('name') border-red-600 @enderror"
                    placeholder="Nombre..." value="{{ old('name', $company->name ?? '') }}" required />
                <div class="absolute inset-y-0 flex items-center ml-3 pointer-events-none">
                    <i class="fas fa-building w-5 h-5"></i>
                </div>
            </div>
            @error('name')
                <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <!-- Email | Teléfono | RUC -->
    <div class="flex flex-col md:flex-row gap-4 mt-4">
        <!-- Email -->
        <label class="block text-sm w-full">
            <span class="text-gray-700 dark:text-gray-400">Email</span>
            <div class="relative text-gray-500 focus-within:text-purple-600 dark:focus-within:text-purple-400">
                <input name="email" type="email"
                    class="block w-full pl-10 mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:shadow-outline-purple dark:focus:shadow-outline-gray @error('email') border-red-600 @enderror"
                    placeholder="Correo electrónico..." value="{{ old('email', $company->email ?? '') }}" required />
                <div class="absolute inset-y-0 flex items-center ml-3 pointer-events-none">
                    <i class="fas fa-envelope w-5 h-5"></i>
                </div>
            </div>
            @error('email')
                <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
            @enderror
        </label>
        <!-- Teléfono -->
        <label class="block text-sm w-full">
            <span class="text-gray-700 dark:text-gray-400">Teléfono</span>
            <div class="relative text-gray-500 focus-within:text-purple-600 dark:focus-within:text-purple-400">
                <input name="phone" type="text"
                    class="block w-full pl-10 mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:shadow-outline-purple dark:focus:shadow-outline-gray @error('phone') border-red-600 @enderror"
                    placeholder="Teléfono..." value="{{ old('phone', $company->phone ?? '') }}" />
                <div class="absolute inset-y-0 flex items-center ml-3 pointer-events-none">
                    <i class="fas fa-phone w-5 h-5"></i>
                </div>
            </div>
            @error('phone')
                <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
            @enderror
        </label>
        <!-- RUC -->
        <label class="block text-sm w-full">
            <span class="text-gray-700 dark:text-gray-400">RUC</span>
            <div class="relative text-gray-500 focus-within:text-purple-600 dark:focus-within:text-purple-400">
                <input name="ruc" type="text"
                    class="block w-full pl-10 mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:shadow-outline-purple dark:focus:shadow-outline-gray @error('ruc') border-red-600 @enderror"
                    placeholder="RUC..." value="{{ old('ruc', $company->ruc ?? '') }}" required />
                <div class="absolute inset-y-0 flex items-center ml-3 pointer-events-none">
                    <i class="fas fa-id-card w-5 h-5"></i>
                </div>
            </div>
            @error('ruc')
                <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <!-- Logo -->
    <label class="block mt-4 text-sm w-full">
        <span class="text-gray-700 dark:text-gray-400">Logo</span>
        <div class="relative flex items-center text-gray-500 focus-within:text-purple-600 dark:focus-within:text-purple-400 mt-1">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="fas fa-image w-5 h-5"></i>
            </div>
            <input name="logo" type="file" accept="image/*"
                class="block w-full pl-10 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 focus:border-purple-400 focus:shadow-outline-purple dark:focus:shadow-outline-gray @error('logo') border-red-600 @enderror"
                id="logoInput" />
        </div>
        <div class="mt-2">
            <img id="logoPreview" src="{{ isset($company) && $company->logo ? asset('storage/' . $company->logo) : '' }}" alt="Vista previa" width="80" class="rounded" style="display: {{ isset($company) && $company->logo ? 'block' : 'none' }};">
        </div>
        @error('logo')
            <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
        @enderror
    </label>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('logoInput');
            const preview = document.getElementById('logoPreview');
            if (input) {
                input.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (ev) {
                            preview.src = ev.target.result;
                            preview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.src = '';
                        preview.style.display = 'none';
                    }
                });
            }
        });
    </script>

    <!-- Dirección -->
    <label class="block mt-4 text-sm w-full">
        <span class="text-gray-700 dark:text-gray-400">Dirección</span>
        <div class="relative text-gray-500 focus-within:text-purple-600 dark:focus-within:text-purple-400">
            <input name="address" type="text"
                class="block w-full pl-10 mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-input focus:border-purple-400 focus:shadow-outline-purple dark:focus:shadow-outline-gray @error('address') border-red-600 @enderror"
                placeholder="Dirección..." value="{{ old('address', $company->address ?? '') }}" required />
            <div class="absolute inset-y-0 flex items-center ml-3 pointer-events-none">
                <i class="fas fa-map-marker-alt w-5 h-5"></i>
            </div>
        </div>
        @error('address')
            <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
        @enderror
    </label>

    <!-- Descripción -->
    <label class="block mt-4 text-sm w-full">
        <span class="text-gray-700 dark:text-gray-400">Descripción</span>
        <div class="relative text-gray-500 focus-within:text-purple-600 dark:focus-within:text-purple-400">
            <textarea name="description"
                class="block w-full pl-10 mt-1 text-sm dark:text-gray-300 dark:border-gray-600 dark:bg-gray-700 form-textarea focus:border-purple-400 focus:shadow-outline-purple dark:focus:shadow-outline-gray @error('description') border-red-600 @enderror"
                rows="3" placeholder="Descripción...">{{ old('description', $company->description ?? '') }}</textarea>
            <div class="absolute inset-y-0 left-0 flex items-center ml-3 pointer-events-none">
                <i class="fas fa-comment w-5 h-5"></i>
            </div>
        </div>
        @error('description')
            <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span>
        @enderror
    </label>

    <!-- Botón enviar -->
    <div class="mt-6">
        <button type="submit"
            class="flex items-center px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple active:bg-purple-600">
            <i class="fas fa-paper-plane mr-2"></i> {{ isset($company) ? 'Actualizar' : 'Guardar' }}
        </button>
    </div>
</div>
