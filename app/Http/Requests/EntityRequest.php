<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$user = $this->user()) {
            return false;
        }

        $permissionsMap = [
            'is_client' => ['create' => 'create clients', 'update' => 'update clients'],
            'is_supplier' => ['create' => 'create suppliers', 'update' => 'update suppliers'],
        ];

        $action = $this->isMethod('post') ? 'create' : 'update';

        foreach ($permissionsMap as $field => $perms) {
            if ($this->boolean($field) && !$user->can($perms[$action])) {
                return false;
            }
        }

        return true;
    }

    public function rules(): array
    {
        $entityId = $this->entity ? $this->entity->id : null;

        return [
            'first_name' => ['required', 'string', 'min:2', 'max:60'],
            'last_name' => ['required', 'string', 'min:2', 'max:60'],

            'identity_card' => ['required', 'string', 'max:30', Rule::unique('entities')->ignore($entityId)],
            'ruc' => ['required', 'string', 'max:20', Rule::unique('entities')->ignore($entityId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('entities')->ignore($entityId)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('entities')->ignore($entityId)],

            'address' => ['nullable', 'string', 'min:5', 'max:255'],
            'description' => ['nullable', 'string', 'max:120'],

            'is_client' => ['required', 'boolean'],
            'is_supplier' => ['required', 'boolean'],

            'is_active' => ['required', 'boolean'],
            'municipality_id' => ['required', 'integer', 'exists:municipalities,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->boolean('is_client') && !$this->boolean('is_supplier')) {
                $validator->errors()->add(
                    'is_client',
                    'La entidad debe ser al menos un Cliente o un Proveedor.'
                );
            }
        });
    }
}
