<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()->can('create', Company::class);
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->user()->can('update', $this->route('company'));
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
            'name' => ['required', 'string', 'min:3', 'max:60'],
            'ruc' => ['nullable', 'string', 'min:11', 'max:30', Rule::unique('companies')->ignore($this->company)],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4048'],
            'description' => ['nullable', 'string', 'min:1', 'max:100'],
            'address' => ['required', 'string', 'min:10', 'max:100',],
            'phone' => ['nullable', 'string', 'min:7', 'max:20', Rule::unique('companies')->ignore($this->company)],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('companies')->ignore($this->company)]
        ];
    }

    /**
     * Mensajes personalizados de validación en español.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no debe exceder de :max caracteres.',
            'ruc.string' => 'El RUC debe ser una cadena de texto.',
            'ruc.min' => 'El RUC debe tener al menos :min caracteres.',
            'ruc.max' => 'El RUC no debe exceder de :max caracteres.',
            'ruc.unique' => 'El RUC ya está en uso.',
            'logo.image' => 'El logo debe ser un archivo de imagen.',
            'logo.mimes' => 'El logo debe ser de tipo: :values.',
            'logo.max' => 'El logo no debe superar los :max kilobytes.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.min' => 'La descripción debe tener al menos :min caracteres.',
            'description.max' => 'La descripción no debe exceder de :max caracteres.',
            'address.required' => 'La dirección es obligatoria.',
            'address.string' => 'La dirección debe ser una cadena de texto.',
            'address.min' => 'La dirección debe tener al menos :min caracteres.',
            'address.max' => 'La dirección no debe exceder de :max caracteres.',
            'phone.string' => 'El teléfono debe ser una cadena de texto.',
            'phone.min' => 'El teléfono debe tener al menos :min caracteres.',
            'phone.max' => 'El teléfono no debe exceder de :max caracteres.',
            'phone.unique' => 'El teléfono ya está en uso.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.string' => 'El correo electrónico debe ser una cadena de texto.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.max' => 'El correo electrónico no debe exceder de :max caracteres.',
            'email.unique' => 'El correo electrónico ya está en uso.',
        ];
    }

    /**
     * Atributos personalizados para los mensajes de validación.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'ruc' => 'RUC',
            'logo' => 'logo',
            'description' => 'descripción',
            'address' => 'dirección',
            'phone' => 'teléfono',
            'email' => 'correo electrónico',
        ];
    }
}
