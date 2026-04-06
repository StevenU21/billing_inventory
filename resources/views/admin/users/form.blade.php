<div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">

    {{-- Nombres | Apellidos --}}
    <div class="flex flex-col md:flex-row gap-4">
        <x-inputs.text-icon 
            name="first_name" 
            label="Nombres" 
            icon="fas fa-user"
            placeholder="Nombre..."
            :alpine="isset($alpine) && $alpine"
            alpineModel="editUser.first_name"
            :value="isset($user) ? $user->first_name ?? '' : ''"
        />
        
        <x-inputs.text-icon 
            name="last_name" 
            label="Apellidos" 
            icon="fas fa-user-tag"
            placeholder="Apellido..."
            :alpine="isset($alpine) && $alpine"
            alpineModel="editUser.last_name"
            :value="isset($user) ? $user->last_name ?? '' : ''"
        />
    </div>

    {{-- Cédula | Teléfono | Género --}}
    <div class="flex flex-col md:flex-row gap-4 mt-4">
        <x-inputs.text-icon 
            name="identity_card" 
            label="Cédula de Identidad" 
            icon="fas fa-id-card"
            placeholder="Cédula..."
            :alpine="isset($alpine) && $alpine"
            alpineModel="editUser.identity_card"
            :value="isset($user) && isset($user->profile) ? $user->profile->identity_card ?? '' : ''"
        />
        
        <x-inputs.text-icon 
            name="phone" 
            label="Teléfono" 
            icon="fas fa-phone"
            type="tel"
            placeholder="Teléfono..."
            :alpine="isset($alpine) && $alpine"
            alpineModel="editUser.phone"
            :value="isset($user) && isset($user->profile) ? $user->profile->phone ?? '' : ''"
        />
        
        <x-inputs.select-icon 
            name="gender" 
            label="Género" 
            icon="fas fa-venus-mars"
            placeholder="Selecciona un género"
            :alpine="isset($alpine) && $alpine"
            alpineModel="editUser.gender"
        >
            <option value="male" {{ old('gender', isset($user) && isset($user->profile) ? $user->profile->gender ?? '' : '') == 'male' ? 'selected' : '' }}>
                Masculino
            </option>
            <option value="female" {{ old('gender', isset($user) && isset($user->profile) ? $user->profile->gender ?? '' : '') == 'female' ? 'selected' : '' }}>
                Femenino
            </option>
        </x-inputs.select-icon>
    </div>

    {{-- Correo | Rol --}}
    <div class="flex flex-col md:flex-row gap-4 mt-4">
        <x-inputs.text-icon 
            name="email" 
            label="Email" 
            icon="fas fa-envelope"
            type="email"
            placeholder="Correo electrónico..."
            :alpine="isset($alpine) && $alpine"
            alpineModel="editUser.email"
            :value="isset($user) ? $user->email ?? '' : ''"
        />
        
        <x-inputs.select-icon 
            name="role" 
            label="Rol" 
            icon="fas fa-user-shield"
            placeholder="Selecciona un rol"
            :alpine="isset($alpine) && $alpine"
            alpineModel="editUser.role"
        >
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" {{ old('role', isset($user) ? optional($user->roles->first())->name ?? '' : '') == $role->name ? 'selected' : '' }}>
                    @if ($role->name === 'admin')
                        Administrador
                    @elseif($role->name === 'cashier')
                        Cajero
                    @else
                        {{ $role->name }}
                    @endif
                </option>
            @endforeach
        </x-inputs.select-icon>
    </div>

    {{-- Avatar --}}
    <div class="mt-4">
        <x-inputs.file 
            name="avatar" 
            label="Avatar (imagen)" 
            icon="fas fa-image"
            accept="image/*"
            :previewId="isset($alpine) && $alpine ? 'avatarPreviewEdit' : 'avatarPreviewCreate'"
            :previewSrc="isset($user) && isset($user->profile) && $user->profile->avatar ? asset('storage/' . $user->profile->avatar) : ''"
            :previewShow="isset($user) && isset($user->profile) && $user->profile->avatar"
            :alpine="isset($alpine) && $alpine"
            alpinePreviewSrc="editUser.avatar_url"
        />
    </div>

    {{-- Dirección --}}
    <div class="mt-4">
        <x-inputs.text-icon 
            name="address" 
            label="Dirección" 
            icon="fas fa-map-marker-alt"
            placeholder="Dirección..."
            :alpine="isset($alpine) && $alpine"
            alpineModel="editUser.address"
            :value="isset($user) && isset($user->profile) ? $user->profile->address ?? '' : ''"
        />
    </div>

    {{-- Contraseña --}}
    <div class="mt-4">
        <x-inputs.text-icon 
            name="password" 
            label="Contraseña" 
            icon="fas fa-lock"
            type="password"
            placeholder="Contraseña..."
        />
    </div>

    {{-- Confirmar Contraseña --}}
    <div class="mt-4">
        <x-inputs.text-icon 
            name="password_confirmation" 
            label="Confirmar Contraseña" 
            icon="fas fa-lock"
            type="password"
            placeholder="Confirmar Contraseña..."
        />
    </div>

    {{-- Botón enviar --}}
    <div class="mt-6">
        <x-inputs.button type="submit" variant="primary" icon="fas fa-paper-plane">
            {{ isset($user) && $user->exists ? 'Actualizar' : 'Guardar' }}
        </x-inputs.button>
    </div>
</div>
