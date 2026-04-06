<?php

namespace App\Http\Requests;

use App\Models\UnitMeasure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitMeasureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()->can('create', UnitMeasure::class);
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->user()->can('update', $this->route('unit_measure'));
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('unit_measures')->ignore($this->unit_measure)],
            'abbreviation' => ['required', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:255'],
            'allows_decimals' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no debe exceder de :max caracteres.',
            'name.unique' => 'El nombre ya está en uso.',
            'abbreviation.required' => 'La abreviatura es obligatoria.',
            'abbreviation.string' => 'La abreviatura debe ser una cadena de texto.',
            'abbreviation.max' => 'La abreviatura no debe exceder de :max caracteres.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no debe exceder de :max caracteres.',
            'allows_decimals.boolean' => 'El campo permite decimales debe ser verdadero o falso.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'abbreviation' => 'abreviatura',
            'description' => 'descripción',
            'allows_decimals' => 'permite decimales',
        ];
    }
}
