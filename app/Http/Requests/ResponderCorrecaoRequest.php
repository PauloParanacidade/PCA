<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResponderCorrecaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'justificativa' => 'required|string|max:1000|min:10',
        ];
    }

    public function messages(): array
    {
        return [
            'justificativa.required' => 'A justificativa é obrigatória.',
            'justificativa.min' => 'A justificativa deve ter pelo menos 10 caracteres.',
            'justificativa.max' => 'A justificativa não pode exceder 1000 caracteres.',
        ];
    }
}