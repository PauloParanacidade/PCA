<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Nullable;

class StorePppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ou usar lógica de permissão
    }

    public function rules(): array
    {
        return [
            'area_solicitante' => 'required|string|max:45',
            'area_responsavel' => 'required|string|max:45',
            'categoria' => 'required|string|max:45',
            'nome_item' => 'required|string|max:100',
            'descricao' => 'required|string|max:255', // ajustado para 255 (conforme migration)
            'quantidade' => 'required|string|max:45',
            'justificativa_pedido' => 'required|string|max:100',

            // Para valores, validar como string que contenha números e formatação monetária possível
            'estimativa_valor' => ['required', 'regex:/^\s*R?\$?\s?\d{1,3}(\.\d{3})*(,\d{2})?\s*$/'],
            'justificativa_valor' => 'required|string|max:100',
            'origem_recurso' => 'required|string|max:45',
            'grau_prioridade' => 'required|string|max:45',
            'ate_partir_dia' => 'nullable|string|max:100',
            'data_ideal_aquisicao' => 'required|date',
            'vinculacao_item' => ['required', 'string', 'in:Sim,Não'],
            'justificativa_vinculacao' => 'nullable|string|max:100',
            'renov_contrato' => ['required', 'string', 'in:Sim,Não'],
            'previsao' => 'nullable|date',
            'num_contrato' => 'nullable|string|max:10',

            'valor_contrato_atualizado' => ['nullable', 'regex:/^\s*R?\$?\s?\d{1,3}(\.\d{3})*(,\d{2})?\s*$/'],
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        Log::error('Validação falhou no StorePppRequest', [
            'dados_recebidos' => $this->all(),
            'erros_de_validacao' => $validator->errors()->all(),
        ]);

        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
        );
    }
}
