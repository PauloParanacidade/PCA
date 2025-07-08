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
        return true;
    }

    public function rules(): array
    {
        return [
            // Informações do item
            'categoria' => 'required|string|max:100',
            'nome_item' => 'required|string|max:200',
            'descricao' => 'required|string|max:500',
            'quantidade' => 'required|string|max:50',
            'justificativa_pedido' => 'required|string|max:1000',
            'natureza_objeto' => 'required|string|max:100',
            'grau_prioridade' => 'required|string|in:Baixa,Média,Alta,Urgente',

            // Informações financeiras - REGEX corrigido para PCRE2
            'estimativa_valor' => ['required', 'regex:/^\s*R\$\s?\d{1,3}(\.\d{3})*(,\d{2})?\s*$/'],
            'justificativa_valor' => 'required|string|max:800',
            'origem_recurso' => 'required|string|max:100',
            'valor_contrato_atualizado' => ['nullable', 'regex:/^\s*R\$\s?\d{1,3}(\.\d{3})*(,\d{2})?\s*$/'],

            // Vinculação/Dependência
            'vinculacao_item' => 'required|in:Sim,Não',
            'justificativa_vinculacao' => 'required_if:vinculacao_item,Sim|nullable|string|max:600',

            // Contrato vigente
            'tem_contrato_vigente' => 'required|in:Sim,Não',
            'num_contrato' => 'required_if:tem_contrato_vigente,Sim|nullable|string|max:20',
            'mes_vigencia_final' => 'nullable|string|max:10',
            'contrato_prorrogavel' => 'required_if:tem_contrato_vigente,Sim|nullable|in:Sim,Não',
            'renov_contrato' => 'required_if:tem_contrato_vigente,Sim|nullable|in:Sim,Não',

            // Cronograma
            'previsao' => 'nullable|date',
        ];
    }
    
    public function messages(): array
    {
        // dd([
        // 'estimativa_valor' => $this->input('estimativa_valor'),
        // 'valor_contrato_atualizado' => $this->input('valor_contrato_atualizado'),
        // 'raw_data' => $this->all()
        // ]);
        return [
            'categoria.required' => 'A categoria é obrigatória.',
            'nome_item.required' => 'O nome do item é obrigatório.',
            'descricao.required' => 'A descrição é obrigatória.',
            'descricao.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'quantidade.required' => 'A quantidade é obrigatória.',
            'quantidade.max' => 'A quantidade não pode ter mais de 50 caracteres.',
            'justificativa_pedido.required' => 'A justificativa do pedido é obrigatória.',
            'justificativa_pedido.max' => 'A justificativa não pode ter mais de 1000 caracteres.',
            'natureza_objeto.required' => 'A natureza do objeto é obrigatória.',
            'grau_prioridade.required' => 'O grau de prioridade é obrigatório.',
            'grau_prioridade.in' => 'O grau de prioridade deve ser uma das opções válidas.',

            'estimativa_valor.required' => 'O valor estimado é obrigatório.',
            'estimativa_valor.regex' => 'O valor estimado deve estar no formato correto (R$ 0,00).',
            'justificativa_valor.required' => 'A justificativa do valor é obrigatória.',
            'justificativa_valor.max' => 'A justificativa do valor não pode ter mais de 800 caracteres.',
            'origem_recurso.required' => 'A origem do recurso é obrigatória.',
            'origem_recurso.max' => 'A origem do recurso não pode ter mais de 100 caracteres.',
            'valor_contrato_atualizado.regex' => 'O valor do contrato atualizado deve estar no formato correto (R$ 0,00).',

            'vinculacao_item.required' => 'É obrigatório informar se há vinculação/dependência.',
            'vinculacao_item.in' => 'O campo vinculação deve ser "Sim" ou "Não".',
            'justificativa_vinculacao.required_if' => 'A justificativa da vinculação é obrigatória quando há vinculação.',
            'justificativa_vinculacao.max' => 'A justificativa da vinculação não pode ter mais de 600 caracteres.',

            'tem_contrato_vigente.required' => 'É obrigatório informar se há contrato vigente.',
            'tem_contrato_vigente.in' => 'O campo contrato vigente deve ser "Sim" ou "Não".',
            'num_contrato.required_if' => 'O número do contrato é obrigatório quando há contrato vigente.',
            'num_contrato.max' => 'O número do contrato não pode ter mais de 20 caracteres.',
            'mes_vigencia_final.max' => 'O campo mês da vigência final não pode ter mais de 10 caracteres.',
            'contrato_prorrogavel.required_if' => 'É obrigatório informar se o contrato é prorrogável.',
            'contrato_prorrogavel.in' => 'O campo prorrogável deve ser "Sim" ou "Não".',
            'renov_contrato.required_if' => 'É obrigatório informar a pretensão de prorrogação.',
            'renov_contrato.in' => 'O campo pretensão de prorrogação deve ser "Sim" ou "Não".',

            'previsao.date' => 'A previsão deve ser uma data válida.',
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
