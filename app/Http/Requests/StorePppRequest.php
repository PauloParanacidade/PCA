<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            // 'cod_id_item' => 'required|integer', // essa linha precisa ficar comentada como lembrança de que nada virá do front para ela
            'categoria' => 'required|string|max:45',
            'nome_item' => 'required|string|max:100',
            'descricao' => 'required|string|max:100',
            'quantidade' => 'required|string|max:45',
            'justificativa_pedido' => 'required|string|max:100',
            'estimativa_valor' => 'required|string', // ele virá como string e depois será convertido para int na controller 
            'justificativa_valor' => 'required|string|max:45',
            'origem_recurso' => 'required|string|max:45',
            'grau_prioridade' => 'required|string|max:45',
            'data_ideal' => 'required|date',
            'vinculacao_item' => 'required|boolean',
            'justificativa_vinculacao' => 'nullable|string|max:100',
            'renov_contrato' => 'required|boolean',
            'valor_contrato' => 'nullable|string', // ele virá como string e depois será convertido para int na controller 
            'historico' => 'nullable|string|max:256',
            'data_temp' => 'required|date',
            'ate_partir_dia' => 'nullable|string|max:100',
        ];
    }
}
