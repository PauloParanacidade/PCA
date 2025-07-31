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
        $currentYear = date('Y');
        $minVigenciaYear = $currentYear + 1; // Ano do PCA + 1
        
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
    
            // Contrato vigente (Card Amarelo) - NOVOS CAMPOS ADICIONADOS
            'tem_contrato_vigente' => 'required|in:Sim,Não',
            'mes_inicio_prestacao' => 'required_if:tem_contrato_vigente,Não|nullable|string|max:10',
            'ano_pca' => [
                'required',
                'integer',
                'min:' . $minVigenciaYear,
                'max:' . $minVigenciaYear,
                function ($attribute, $value, $fail) use ($minVigenciaYear) {
                    if ($value != $minVigenciaYear) {
                        $fail("O ano do PCA deve ser {$minVigenciaYear}.");
                    }
                },
            ],
            'contrato_mais_um_exercicio' => 'required_if:tem_contrato_vigente,Não|nullable|in:Sim,Não',
            'num_contrato' => [
                'required_if:tem_contrato_vigente,Sim',
                'nullable',
                'string',
                'regex:/^[0-9]{4}$/', // Exatamente 4 dígitos numéricos
            ],
            'ano_contrato' => [
                'required_if:tem_contrato_vigente,Sim',
                'nullable',
                'integer',
                'min:2000',
                'max:' . ($currentYear + 10),
                function ($attribute, $value, $fail) {
                    if ($value && ($value < 2000 || $value > 9999)) {
                        $fail('O campo ano do contrato não contém um ano válido.');
                    }
                },
            ],
            'mes_vigencia_final' => 'required_if:tem_contrato_vigente,Sim|nullable|string|max:10',
            'ano_vigencia_final' => [
                'required_if:tem_contrato_vigente,Sim',
                'nullable',
                'integer',
                'min:' . $minVigenciaYear,
                'max:' . ($currentYear + 10),
                function ($attribute, $value, $fail) use ($minVigenciaYear) {
                    if ($value && ($value < 1000 || $value > 9999)) {
                        $fail('O campo ano de vigência final não contém um ano válido.');
                    } elseif ($value && $value < $minVigenciaYear) {
                        $fail("O ano de vigência final deve ser no mínimo {$minVigenciaYear}.");
                    }
                },
            ],
            'contrato_prorrogavel' => 'required_if:tem_contrato_vigente,Sim|nullable|in:Sim,Não',
            'renov_contrato' => 'required_if:contrato_prorrogavel,Sim|nullable|in:Sim,Não',

            // Informações financeiras (Card Verde) - Validação condicional
            'estimativa_valor' => 'required|numeric|min:0',
            'origem_recurso' => 'required|string|max:100',
            'valor_contrato_atualizado' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->shouldShowValorMaisUmExercicio() && empty($value)) {
                        $fail('O campo Valor se +1 exercício é obrigatório baseado nas condições do contrato.');
                    }
                },
            ],
            'justificativa_valor' => 'required|string|max:800',

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

    /**
     * Determina se o campo "Valor se +1 exercício" deve ser exibido
     */
    private function shouldShowValorMaisUmExercicio(): bool
    {
        $temContrato = $this->input('tem_contrato_vigente');
        
        // Se não tem contrato, verificar se é mais de um exercício
        if ($temContrato === 'Não') {
            $contratoMaisUmExercicio = $this->input('contrato_mais_um_exercicio');
            return $contratoMaisUmExercicio === 'Sim';
        }
        
        // Se tem contrato, verificar ano final
        if ($temContrato === 'Sim') {
            $anoVigencia = $this->input('ano_vigencia_final');
            $anoPCA = date('Y') + 1; // Usar ano dinâmico
            
            // Se ano final não é o mesmo do PCA, não mostrar campo
            if ($anoVigencia != $anoPCA) {
                return false;
            }
            
            // Se é prorrogável
            $prorrogavel = $this->input('contrato_prorrogavel');
            if ($prorrogavel === 'Não') {
                return false;
            }
            
            // Se vai prorrogar
            $vaiProrrogar = $this->input('renov_contrato');
            if ($vaiProrrogar === 'Não') {
                return false;
            }
            
            // Se vai prorrogar = Sim, mostrar campo
            if ($vaiProrrogar === 'Sim') {
                return true;
            }
        }
        
        return false;
    }

    public function messages(): array
    {
        $isRascunho = $this->input('acao') === 'salvar_rascunho';
        $minVigenciaYear = date('Y') + 1;
    
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
            'ano_contrato.required_if' => 'O ano do contrato é obrigatório quando há contrato vigente.',
            'ano_contrato.integer' => 'O ano do contrato deve ser um número válido.',
            'ano_contrato.min' => 'O ano do contrato deve ser maior que 2000.',
            'ano_contrato.max' => 'O ano do contrato não pode ser superior a ' . (date('Y') + 10) . '.',
            'mes_vigencia_final.required_if' => 'O mês de vigência final é obrigatório quando há contrato vigente.',
            'mes_vigencia_final.max' => 'O mês de vigência final não pode ter mais de 10 caracteres.',
            'ano_vigencia_final.required_if' => 'O ano de vigência final é obrigatório quando há contrato vigente.',
            'ano_vigencia_final.min' => 'O ano de vigência final deve ser no mínimo ' . (date('Y') + 1) . '.',
            'ano_vigencia_final.max' => 'O ano de vigência final não pode ser superior a ' . (date('Y') + 10) . '.',
            'contrato_prorrogavel.required_if' => 'A informação sobre prorrogação é obrigatória quando há contrato vigente.',
            'contrato_prorrogavel.in' => 'A prorrogação do contrato deve ser Sim ou Não.',
            'renov_contrato.required_if' => 'A pretensão de renovação é obrigatória quando há contrato vigente.',
            'renov_contrato.in' => 'A renovação do contrato deve ser Sim ou Não.',
            
            // Card Verde - Mensagens atualizadas para validação numérica
            'estimativa_valor.required' => 'O valor estimado é obrigatório.',
            'estimativa_valor.numeric' => 'O valor estimado deve ser um número válido.',
            'estimativa_valor.min' => 'O valor estimado deve ser maior que zero.',
            'justificativa_valor.required' => 'A justificativa do valor é obrigatória.',
            'justificativa_valor.max' => 'A justificativa do valor não pode ter mais de 800 caracteres.',
            'origem_recurso.required' => 'A origem do recurso é obrigatória.',
            'origem_recurso.max' => 'A origem do recurso não pode ter mais de 100 caracteres.',
            'valor_contrato_atualizado.numeric' => 'O valor do contrato deve ser um número válido.',
            'valor_contrato_atualizado.min' => 'O valor do contrato deve ser maior que zero.',
            
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

    /**
     * Preparar dados para validação
     * Converter valores monetários formatados para numéricos
     */
    protected function prepareForValidation()
    {
        $data = $this->all();
        
        // Campos monetários que precisam ser convertidos
        $moneyFields = ['estimativa_valor', 'valor_contrato_atualizado'];
        
        foreach ($moneyFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                // Converter R$ 1.234,56 para 1234.56
                $numericValue = $this->convertMoneyToNumeric($data[$field]);
                $data[$field] = $numericValue;
            }
        }
        
        $this->replace($data);
    }

    /**
     * Converter valor monetário formatado para numérico
     */
    private function convertMoneyToNumeric($value)
    {
        if (empty($value)) {
            return null;
        }
        
        // Remover R$, espaços, pontos (separadores de milhares) e converter vírgula para ponto
        $numericValue = preg_replace('/R\$\s?/', '', $value);
        $numericValue = str_replace('.', '', $numericValue);
        $numericValue = str_replace(',', '.', $numericValue);
        
        return (float) $numericValue;
    }
}