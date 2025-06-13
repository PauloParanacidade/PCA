<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePcaPppRequest extends FormRequest
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
            'id_item' => 'required|integer',
            'PCA_categoria_id' => 'required|exists:PCA_categoria,id',
            'PCA_nome_item_id' => 'required|exists:PCA_nome_item,id',
            'descricao' => 'required|string|max:100',
            'quantidade' => 'required|string|max:45',
            'justificativa_pedido' => 'required|string|max:45',
            'estimativa_valor' => 'required|integer',
            'justificativa_valor' => 'required|string|max:45',
            'origem_recurso' => 'required|string|max:45',
            'grau_prioridade' => 'required|string|max:45',
            'data_ideal_aquisicao' => 'required|string|max:45',
            'vinculacao_item' => 'required|boolean',
            'PCA_solicitacao_id' => 'nullable|exists:PCA_solicitacao,id',
            'justificativa_vinculacao' => 'nullable|string|max:100',
            'dt_preenchimento' => 'required|date',
            'PCA_contrato_id' => 'nullable|exists:PCA_contrato,id', // se booleano for false, será null
        ];
    }
}
