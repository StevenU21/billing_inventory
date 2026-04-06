@php
    $initialDepartment = old($departmentName, $departmentId ?? '');
    $initialMunicipality = old($municipalityName, $municipalityId ?? '');
@endphp
<div class="flex flex-col md:flex-row gap-4" x-data='{
    form: {
        departmentId: @json((string) $initialDepartment),
        municipalityId: @json((string) $initialMunicipality),
    },
    municipalities: @json($municipalities)
}'>
    <x-inputs.select-icon
        name="{{ $departmentName }}"
        label="{{ $departmentLabel }}"
        icon="fas fa-map"
        placeholder="Seleccione un departamento..."
        alpine="true"
        alpineModel="form.departmentId"
        :required="$required"
        x-on:change="form.municipalityId = ''"
    >
        <option value="">Seleccione un departamento...</option>
        @foreach ($departments as $id => $name)
            <option value="{{ $id }}" @selected((string) $id === (string) $initialDepartment)>{{ $name }}</option>
        @endforeach
    </x-inputs.select-icon>

    <x-inputs.select-icon
        name="{{ $municipalityName }}"
        label="{{ $municipalityLabel }}"
        icon="fas fa-map-marked-alt"
        placeholder="Seleccione un municipio..."
        alpine="true"
        alpineModel="form.municipalityId"
        :required="$required"
    >
        <option value="">Seleccione un municipio...</option>
        <template x-for="opt in municipalities.filter(o => !form.departmentId || o.department_id === form.departmentId)" :key="opt.id">
            <option :value="opt.id" x-text="opt.name" :selected="opt.id === form.municipalityId"></option>
        </template>
    </x-inputs.select-icon>
</div>
