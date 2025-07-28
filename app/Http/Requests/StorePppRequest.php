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
        // Verificar se é rascunho (botão Avançar) ou envio completo (botão Salvar e Enviar)
        $isRascunho = $this->input('acao') === 'salvar_rascunho';

        
        if ($isRascunho) {
            // Regras apenas para card azul (rascunho)
            return [
                'nome_item' => 'required|string|max:200',
                'quantidade' => 'required|string|max:50',
                'grau_prioridade' => 'required|string|in:Baixa,Média,Alta,Urgente',
                'descricao' => 'required|string|max:1000',
                'natureza_objeto' =>'required|string|max:100',
                'justificativa_pedido' => 'required|string|max:1000',
                'categoria' => 'required|string|max:100',
            ];
        }
        
        // Regras completas para envio (todos os cards)
        return [
            // Informações do item (Card Azul)
            'nome_item' => 'required|string|max:200',
            'quantidade' => 'required|string|max:50',
            'grau_prioridade' => 'required|string|in:Baixa,Média,Alta,Urgente',
            'descricao' => 'required|string|max:1000',
            'natureza_objeto' =>'required|string|max:100',
            'justificativa_pedido' => 'required|string|max:1000',
            'categoria' => 'required|string|max:100',

            // Contrato vigente (Card Amarelo)
            'tem_contrato_vigente' => 'required|in:Sim,Não',
            'mes_inicio_prestacao' => 'required_if:tem_contrato_vigente,Não|nullable|string|max:10',
            'num_contrato' => 'required_if:tem_contrato_vigente,Sim|nullable|string|max:20',
            'mes_vigencia_final' => 'required_if:tem_contrato_vigente,Sim|nullable|string|max:10',
            'contrato_prorrogavel' => 'required_if:tem_contrato_vigente,Sim|nullable|in:Sim,Não',
            'renov_contrato' => 'required_if:contrato_prorrogavel,Sim|nullable|in:Sim,Não',

            // Informações financeiras (Card Verde) - REGEX corrigido para PCRE2
            'estimativa_valor' => ['required', 'regex:/^\\s*R\\$\\s?\\d{1,3}(\\.\\d{3})*(,\\d{2})?\\s*$/'],
            'justificativa_valor' => 'required|string|max:800',
            'origem_recurso' => 'required|string|max:100',
            'valor_contrato_atualizado' => ['nullable', 'regex:/^\\s*R\\$\\s?\\d{1,3}(\\.\\d{3})*(,\\d{2})?\\s*$/'],

            // Vinculação/Dependência (Card Ciano)
            'vinculacao_item' => 'required|in:Sim,Não',
            'justificativa_vinculacao' => 'required_if:vinculacao_item,Sim|nullable|string|max:600',

            // Cronograma
            'cronograma_jan' => 'nullable|in:Sim,Não',
            'cronograma_fev' => 'nullable|in:Sim,Não',
            'cronograma_mar' => 'nullable|in:Sim,Não',
            'cronograma_abr' => 'nullable|in:Sim,Não',
            'cronograma_mai' => 'nullable|in:Sim,Não',
            'cronograma_jun' => 'nullable|in:Sim,Não',
            'cronograma_jul' => 'nullable|in:Sim,Não',
            'cronograma_ago' => 'nullable|in:Sim,Não',
            'cronograma_set' => 'nullable|in:Sim,Não',
            'cronograma_out' => 'nullable|in:Sim,Não',
            'cronograma_nov' => 'nullable|in:Sim,Não',
            'cronograma_dez' => 'nullable|in:Sim,Não',
        ];
    }

    public function messages(): array
    {
        $isRascunho = $this->input('acao') === 'salvar_rascunho';
        
        if ($isRascunho) {
            // Mensagens específicas para rascunho (card azul)
            return [
                'nome_item.required' => 'O nome do item é obrigatório.',
                'nome_item.max' => 'O nome do item não pode ter mais de 200 caracteres.',
                'quantidade.required' => 'A quantidade é obrigatória.',
                'quantidade.max' => 'A quantidade não pode ter mais de 50 caracteres.',
                'categoria.required' => 'A categoria é obrigatória.',
                'categoria.max' => 'A categoria não pode ter mais de 100 caracteres.',
                'grau_prioridade.required' => 'O grau de prioridade é obrigatório.',
                'grau_prioridade.in' => 'O grau de prioridade deve ser: Baixa, Média, Alta ou Urgente.',
                'descricao.required' => 'A descrição é obrigatória.',
                'descricao.max' => 'A descrição não pode ter mais de 1000 caracteres.',
                'justificativa_pedido.required' => 'A justificativa do pedido é obrigatória.',
                'justificativa_pedido.max' => 'A justificativa do pedido não pode ter mais de 1000 caracteres.',
                'area_solicitante.max' => 'A área solicitante não pode ter mais de 100 caracteres.',
            ];
        }
        
        // Mensagens completas para envio
        return [
            // Card Azul
            'categoria.required' => 'A categoria é obrigatória.',
            'categoria.max' => 'A categoria não pode ter mais de 100 caracteres.',
            'nome_item.required' => 'O nome do item é obrigatório.',
            'nome_item.max' => 'O nome do item não pode ter mais de 200 caracteres.',
            'descricao.required' => 'A descrição é obrigatória.',
            'descricao.max' => 'A descrição não pode ter mais de 1000 caracteres.',
            'quantidade.required' => 'A quantidade é obrigatória.',
            'quantidade.max' => 'A quantidade não pode ter mais de 50 caracteres.',
            'justificativa_pedido.required' => 'A justificativa do pedido é obrigatória.',
            'justificativa_pedido.max' => 'A justificativa do pedido não pode ter mais de 1000 caracteres.',
            'natureza_objeto.required' => 'A natureza do objeto é obrigatória.',
            'natureza_objeto.max' => 'A natureza do objeto não pode ter mais de 100 caracteres.',
            'grau_prioridade.required' => 'O grau de prioridade é obrigatório.',
            'grau_prioridade.in' => 'O grau de prioridade deve ser: Baixa, Média, Alta ou Urgente.',
            'area_solicitante.required' => 'A área solicitante é obrigatória.',
            'area_solicitante.max' => 'A área solicitante não pode ter mais de 100 caracteres.',

            // Card Amarelo
            'tem_contrato_vigente.required' => 'A informação sobre contrato vigente é obrigatória.',
            'tem_contrato_vigente.in' => 'O contrato vigente deve ser Sim ou Não.',
            'mes_inicio_prestacao.required_if' => 'O mês pretendido para início é obrigatório quando não há contrato vigente.',
            'mes_inicio_prestacao.string' => 'O mês pretendido deve ser um texto válido.',
            'mes_inicio_prestacao.max' => 'O mês pretendido não pode ter mais de 10 caracteres.',
            'num_contrato.required_if' => 'O número do contrato é obrigatório quando há contrato vigente.',
            'num_contrato.max' => 'O número do contrato não pode ter mais de 20 caracteres.',
            'mes_vigencia_final.required_if' => 'O mês de vigência final é obrigatório quando há contrato vigente.',
            'mes_vigencia_final.max' => 'O mês de vigência final não pode ter mais de 10 caracteres.',
            'contrato_prorrogavel.required_if' => 'A informação sobre prorrogação é obrigatória quando há contrato vigente.',
            'contrato_prorrogavel.in' => 'A prorrogação do contrato deve ser Sim ou Não.',
            'renov_contrato.required_if' => 'A pretensão de renovação é obrigatória quando há contrato vigente.',
            'renov_contrato.in' => 'A renovação do contrato deve ser Sim ou Não.',
            
            // Card Verde
            'estimativa_valor.required' => 'O valor estimado é obrigatório.',
            'estimativa_valor.regex' => 'O valor estimado deve estar no formato: R$ 1.000,00',
            'justificativa_valor.required' => 'A justificativa do valor é obrigatória.',
            'justificativa_valor.max' => 'A justificativa do valor não pode ter mais de 800 caracteres.',
            'origem_recurso.required' => 'A origem do recurso é obrigatória.',
            'origem_recurso.max' => 'A origem do recurso não pode ter mais de 100 caracteres.',
            'valor_contrato_atualizado.regex' => 'O valor do contrato deve estar no formato: R$ 1.000,00',
            
            // Card Ciano
            'vinculacao_item.required' => 'A vinculação do item é obrigatória.',
            'vinculacao_item.in' => 'A vinculação do item deve ser Sim ou Não.',
            'justificativa_vinculacao.required_if' => 'A justificativa da vinculação é obrigatória quando a vinculação for Sim.',
            'justificativa_vinculacao.max' => 'A justificativa da vinculação não pode ter mais de 600 caracteres.',
            'dependencia_item.required' => 'A dependência do item é obrigatória.',
            'dependencia_item.in' => 'A dependência do item deve ser Sim ou Não.',
            'justificativa_dependencia.required_if' => 'A justificativa da dependência é obrigatória quando a dependência for Sim.',
            'justificativa_dependencia.max' => 'A justificativa da dependência não pode ter mais de 600 caracteres.',
            
            // Cronograma
            'cronograma_jan.in' => 'Janeiro deve ser Sim ou Não.',
            'cronograma_fev.in' => 'Fevereiro deve ser Sim ou Não.',
            'cronograma_mar.in' => 'Março deve ser Sim ou Não.',
            'cronograma_abr.in' => 'Abril deve ser Sim ou Não.',
            'cronograma_mai.in' => 'Maio deve ser Sim ou Não.',
            'cronograma_jun.in' => 'Junho deve ser Sim ou Não.',
            'cronograma_jul.in' => 'Julho deve ser Sim ou Não.',
            'cronograma_ago.in' => 'Agosto deve ser Sim ou Não.',
            'cronograma_set.in' => 'Setembro deve ser Sim ou Não.',
            'cronograma_out.in' => 'Outubro deve ser Sim ou Não.',
            'cronograma_nov.in' => 'Novembro deve ser Sim ou Não.',
            'cronograma_dez.in' => 'Dezembro deve ser Sim ou Não.',
            
            // Outros
            'previsao.date' => 'A previsão deve ser uma data válida.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $isRascunho = !$this->has('acao') || $this->input('acao') !== 'enviar_aprovacao';
        
        Log::error('Validação falhou no StorePppRequest', [
            'tipo_validacao' => $isRascunho ? 'rascunho' : 'envio_completo',
            'dados_recebidos' => $this->all(),
            'erros_de_validacao' => $validator->errors()->all(),
        ]);

        $errorMessage = $isRascunho 
            ? 'Por favor, preencha todos os campos obrigatórios do card azul antes de continuar.'
            : 'Por favor, preencha todos os campos obrigatórios antes de enviar para aprovação.';

        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $errorMessage)
        );
    }
}