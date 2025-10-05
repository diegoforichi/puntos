<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class ArchiveTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'confirm_rut' => 'required|string',
            'confirm_terms' => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_rut.required' => 'Debes escribir el RUT del tenant para confirmar.',
            'confirm_terms.accepted' => 'Debes confirmar que deseas archivar el tenant.',
        ];
    }
}
