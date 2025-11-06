<?php

namespace App\Http\Requests\Campanas;

use Illuminate\Foundation\Http\FormRequest;

class EnviarPruebaCampanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'telefono_prueba' => ['nullable', 'string', 'max:30'],
            'email_prueba' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'telefono_prueba.max' => 'El teléfono de prueba no puede superar los 30 caracteres.',
            'email_prueba.email' => 'Ingresa un email válido para la prueba.',
            'email_prueba.max' => 'El email de prueba no puede superar los 255 caracteres.',
        ];
    }
}
