<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Permission;

class RevokePermissionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', Permission::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'permission' => ['required', 'array'],
            'permission.*' => ['exists:permissions,name'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'permission.required' => 'Debe seleccionar al menos un permiso para revocar.',
            'permission.array' => 'Los permisos deben ser un array.',
            'permission.*.exists' => 'Uno o más permisos seleccionados no existen.',
        ];
    }
}
