<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()->can('create', User::class);
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->user()->can('update', $this->route('user'));
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
        $rules = [
            'first_name' => ['required', 'string', 'min:3', 'max:60'],
            'last_name' => ['required', 'string', 'min:3', 'max:60'],
            'email' => ['required', 'string', 'email', 'max:30', Rule::unique('users')->ignore($this->user)],
        ];

        if ($this->isMethod('post')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['password'] = ['nullable', 'confirmed', Rules\Password::defaults()];
        }

        return $rules;
    }

    /**
     * Mensajes personalizados de validación en español.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'El nombre es obligatorio.',
            'first_name.string' => 'El nombre debe ser una cadena de texto.',
            'first_name.min' => 'El nombre debe tener al menos :min caracteres.',
            'first_name.max' => 'El nombre no debe exceder de :max caracteres.',
            'last_name.required' => 'El apellido es obligatorio.',
            'last_name.string' => 'El apellido debe ser una cadena de texto.',
            'last_name.min' => 'El apellido debe tener al menos :min caracteres.',
            'last_name.max' => 'El apellido no debe exceder de :max caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.string' => 'El correo electrónico debe ser una cadena de texto.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.max' => 'El correo electrónico no debe exceder de :max caracteres.',
            'email.unique' => 'El correo electrónico ya está en uso.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
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
            'first_name' => 'nombre',
            'last_name' => 'apellido',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
        ];
    }
}
