<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class UpdatePppRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ppp = $this->route('ppp');
        
        // Verificar se o usuário pode editar este PPP
        return $this->user()->can('update', $ppp);
    }

    public function rules(): array
    {
        $pppId = $this->route('ppp')->id;
        
        return [
            'area_solicitante' => 'sometimes|string|max:45',
            'area_responsavel' => 'sometimes|string|max:45',
            'categoria' => 'sometimes|string|max:45',
            'nome_item' => 'sometimes|string|max:100',
            'descricao' => 'sometimes|string|max:255',
            'quantidade' => 'sometimes|string|max:45',
            'justificativa_pedido' => 'sometimes|string|max:100',
            
            // Campos que podem ser atualizados opcionalmente
            'estimativa_valor' => 'sometimes|required',
            'justificativa_valor' => 'sometimes|string|max:100',
            'origem_recurso' => 'sometimes|string|max:20',
            'grau_prioridade' => 'sometimes|string|max:20',
            'ate_partir_dia' => 'nullable|string|max:20',
            'data_ideal_aquisicao' => 'sometimes|date',
            'vinculacao_item' => 'sometimes|string|in:Sim,Não',
            'justificativa_vinculacao' => 'nullable|string|max:100',
            'renov_contrato' => 'sometimes|string|in:Sim,Não',
            'previsao' => 'nullable|date',
            'num_contrato' => 'nullable|string|max:10',
            'valor_contrato_atualizado' => 'nullable|regex:/^\s*R?\$?\s?\d{1,3}(\.\d{3})*(,\d{2})?\s*$/',
        ];
    }

    public function messages(): array
    {
        return [
            'categoria.required' => 'A categoria é obrigatória.',
            'nome_item.required' => 'O nome do item é obrigatório.',
            // ... outras mensagens específicas para atualização
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('Validação falhou no UpdatePppRequest', [
            'ppp_id' => $this->route('ppp')->id,
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