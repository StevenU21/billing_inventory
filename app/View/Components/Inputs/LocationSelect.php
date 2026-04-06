<?php

namespace App\View\Components\Inputs;

use App\Models\Department;
use App\Models\Municipality;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LocationSelect extends Component
{
    public array $departments;

    public array $municipalities;

    public array $departmentsByMunicipality;

    public function __construct(
        public ?string $departmentId = null,
        public ?string $municipalityId = null,
        public bool $required = true,
        public string $departmentName = 'department_id',
        public string $municipalityName = 'municipality_id',
        public string $departmentLabel = 'Departamento',
        public string $municipalityLabel = 'Municipio'
    ) {
        $this->departments = Department::orderBy('name')
            ->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => [(string) $id => $name])
            ->toArray();

        $this->municipalities = Municipality::orderBy('name')
            ->get(['id', 'name', 'department_id'])
            ->map(fn (Municipality $municipality) => [
                'id' => (string) $municipality->id,
                'name' => $municipality->name,
                'department_id' => (string) $municipality->department_id,
            ])
            ->toArray();

        $this->departmentsByMunicipality = Municipality::pluck('department_id', 'id')
            ->mapWithKeys(fn ($deptId, $id) => [(string) $id => (string) $deptId])
            ->toArray();
    }

    public function render(): View
    {
        return view('components.inputs.location-select');
    }
}
