<?php

namespace App\Http\Requests;

use App\Models\Tax;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()->can('create', Tax::class);
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->user()->can('update', $this->route('tax'));
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
            'name' => ['required', 'string', 'max:255', Rule::unique('taxes')->ignore($this->tax)],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no debe exceder de :max caracteres.',
            'name.unique' => 'El nombre ya está en uso.',
            'percentage.required' => 'El porcentaje es obligatorio.',
            'percentage.numeric' => 'El porcentaje debe ser un valor numérico.',
            'percentage.min' => 'El porcentaje no puede ser menor que :min.',
            'percentage.max' => 'El porcentaje no puede ser mayor que :max.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'percentage' => 'porcentaje',
            'is_default' => 'por defecto',
        ];
    }
}
