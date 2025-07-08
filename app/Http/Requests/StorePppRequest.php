<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StorePppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ou usar lógica de permissão
    }

    public function rules(): array
    {
        return [
            // Informações básicas
            'categoria' => 'required|string|max:100',
            'nome_item' => 'required|string|max:200',
            'descricao' => 'required|string|max:500',
            'quantidade' => 'required|string|max:100',
            'justificativa_pedido' => 'required|string|max:1000',
            'natureza_objeto' => 'required|string|max:100',
            'grau_prioridade' => 'required|string|max:50',

            // Informações financeiras
            'estimativa_valor' => 'required|string',
            'justificativa_valor' => 'required|string|max:800',
            'origem_recurso' => 'required|string|max:50',
            'valor_contrato_atualizado' => 'nullable|string',

            // Cronograma
            //'ate_partir_dia' => 'required|string|max:200',
            // 'data_ideal_aquisicao' => 'required|date',

            // Vinculação/Dependência
            'vinculacao_item' => 'required|string|in:Sim,Não',
            'justificativa_vinculacao' => 'required_if:vinculacao_item,Sim|nullable|string|max:600',

            // Contrato vigente
            'tem_contrato_vigente' => 'required|string|in:Sim,Não',
            'num_contrato' => 'required_if:tem_contrato_vigente,Sim|nullable|string|max:20',
            'mes_vigencia_final' => 'nullable|string|max:10',
            'contrato_prorrogavel' => 'required_if:tem_contrato_vigente,Sim|nullable|string|in:Sim,Não',
            'renov_contrato' => 'required_if:tem_contrato_vigente,Sim|nullable|string|in:Sim,Não',

            // Outros
            'previsao' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            // Informações básicas
            'categoria.required' => 'A categoria é obrigatória.',
            'nome_item.required' => 'O nome do item é obrigatório.',
            'descricao.required' => 'A descrição é obrigatória.',
            'descricao.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'quantidade.required' => 'A quantidade é obrigatória.',
            'justificativa_pedido.required' => 'A justificativa do pedido é obrigatória.',
            'justificativa_pedido.max' => 'A justificativa do pedido não pode ter mais de 1000 caracteres.',
            'natureza_objeto.required' => 'A natureza do objeto é obrigatória.',
            'grau_prioridade.required' => 'O grau de prioridade é obrigatório.',

            // Informações financeiras
            'estimativa_valor.required' => 'O valor estimado é obrigatório.',
            'justificativa_valor.required' => 'A justificativa do valor é obrigatória.',
            'justificativa_valor.max' => 'A justificativa do valor não pode ter mais de 800 caracteres.',
            'origem_recurso.required' => 'A origem do recurso é obrigatória.',

            // Cronograma
            //'ate_partir_dia.required' => 'O campo "a partir de quando" é obrigatório.',
            'data_ideal_aquisicao.required' => 'A data ideal para aquisição é obrigatória.',
            'data_ideal_aquisicao.date' => 'A data ideal deve ser uma data válida.',

            // Vinculação/Dependência
            'vinculacao_item.required' => 'É obrigatório informar se há vinculação/dependência.',
            'vinculacao_item.in' => 'O campo vinculação deve ser "Sim" ou "Não".',
            'justificativa_vinculacao.required_if' => 'A justificativa da vinculação é obrigatória quando há vinculação.',
            'justificativa_vinculacao.max' => 'A justificativa da vinculação não pode ter mais de 600 caracteres.',

            // Contrato vigente
            'tem_contrato_vigente.required' => 'É obrigatório informar se há contrato vigente.',
            'tem_contrato_vigente.in' => 'O campo contrato vigente deve ser "Sim" ou "Não".',
            'num_contrato.required_if' => 'O número do contrato é obrigatório quando há contrato vigente.',
            'contrato_prorrogavel.required_if' => 'É obrigatório informar se o contrato é prorrogável quando há contrato vigente.',
            'contrato_prorrogavel.in' => 'O campo prorrogável deve ser "Sim" ou "Não".',
            'renov_contrato.required_if' => 'É obrigatório informar a pretensão de prorrogação quando há contrato vigente.',
            'renov_contrato.in' => 'O campo pretensão de prorrogação deve ser "Sim" ou "Não".',
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
