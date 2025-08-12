<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ ADICIONAR

/**
 * @property int $id
 * @property int $user_id
 * @property int $status_id
 * @property int|null $gestor_atual_id
 * @property string|null $data_ultima_aprovacao Data da última ação de aprovação
 * @property string $nome_item Nome/título do item
 * @property string $quantidade Quantidade solicitada
 * @property string $grau_prioridade Grau de prioridade
 * @property string $descricao Descrição detalhada do item
 * @property string $natureza_objeto Natureza do objeto (bem/serviço)
 * @property string $justificativa_pedido Justificativa para o pedido
 * @property string $categoria Categoria do item/serviço
 * @property string $tem_contrato_vigente Possui contrato vigente
 * @property string|null $mes_inicio_prestacao Mês de início da prestação do serviço quando não tiver contrato vigente
 * @property int|null $ano_pca Ano do PCA (preenchido automaticamente como ano atual + 1) para objeto novo
 * @property string|null $contrato_mais_um_exercicio O contrato é mais de um exercício
 * @property int|null $num_contrato Número do contrato atual (1-9999) - obrigatório se tem_contrato_vigente = Sim
 * @property int|null $ano_contrato Ano do contrato atual (obrigatório se tem_contrato_vigente = Sim)
 * @property string|null $mes_vigencia_final Mês de vigência final do contrato
 * @property int|null $ano_vigencia_final Ano de vigência final do contrato para comparação com PCA
 * @property string|null $contrato_prorrogavel Contrato é prorrogável (obrigatório se tem_contrato_vigente = Sim)
 * @property string|null $renov_contrato Pretensão de prorrogação (obrigatório se tem_contrato_vigente = Sim)
 * @property numeric $estimativa_valor Valor estimado da aquisição
 * @property string $origem_recurso Origem do recurso financeiro
 * @property string $justificativa_valor Justificativa do valor estimado
 * @property numeric|null $valor_contrato_atualizado Valor atualizado do contrato (se aplicável)
 * @property string $vinculacao_item Item possui vinculação/dependência
 * @property string|null $justificativa_vinculacao Justificativa da vinculação (obrigatória se vinculacao_item = Sim)
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $gestorAtual
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PppGestorHistorico> $gestoresHistorico
 * @property-read int|null $gestores_historico_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PppHistorico> $historicos
 * @property-read int|null $historicos_count
 * @property-read \App\Models\PppStatus $status
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereAnoContrato($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereAnoPca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereAnoVigenciaFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereCategoria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereContratoMaisUmExercicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereContratoProrrogavel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereDataUltimaAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereEstimativaValor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereGestorAtualId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereGrauPrioridade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereJustificativaPedido($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereJustificativaValor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereJustificativaVinculacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereMesInicioPrestacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereMesVigenciaFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereNaturezaObjeto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereNomeItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereNumContrato($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereOrigemRecurso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereQuantidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereRenovContrato($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereTemContratoVigente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereValorContratoAtualizado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp whereVinculacaoItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PcaPpp withoutTrashed()
 * @mixin \Eloquent
 */
class PcaPpp extends Model
{
    use SoftDeletes;
    
    protected $table = 'pca_ppps';

    protected $fillable = [
        'user_id',
        'status_id',
        'gestor_atual_id',
        
        //card azul
        'nome_item',
        'quantidade',
        'grau_prioridade',
        'descricao',
        'natureza_objeto',
        'justificativa_pedido',
        'categoria',
        
        //card amarelo
        'tem_contrato_vigente',
        'mes_inicio_prestacao',
        'ano_pca',                    
        'contrato_mais_um_exercicio', 
        'num_contrato',
        'ano_contrato',               
        'mes_vigencia_final',
        'ano_vigencia_final',         
        'contrato_prorrogavel',
        'renov_contrato',
        
        //card verde
        'estimativa_valor',
        'origem_recurso',
        'valor_contrato_atualizado',
        'justificativa_valor',

        //card ciano
        'vinculacao_item',
        'justificativa_vinculacao',
    ];

    protected $casts = [
        'estimativa_valor' => 'decimal:2',
        'valor_contrato_atualizado' => 'decimal:2',
        'ano_contrato' => 'integer',        
        'ano_vigencia_final' => 'integer',  
        'ano_pca' => 'integer',             
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gestorAtual()
    {
        return $this->belongsTo(User::class, 'gestor_atual_id');
    }

    public function status()
    {
        return $this->belongsTo(\App\Models\PppStatus::class, 'status_id');
    }

    public function historicos()
    {
        return $this->hasMany(\App\Models\PppHistorico::class, 'ppp_id');
    }

    public function gestoresHistorico()
    {
        return $this->hasMany(PppGestorHistorico::class, 'ppp_id');
    }
}