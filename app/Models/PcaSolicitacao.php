<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcaSolicitacao extends Model
{
    protected $table = 'PCA_solicitacao';

    protected $fillable = [
        'area_solicitante',
        'area_responsavel',
        'id_item',
        'PCA_categoria_id',
        'PCA_nome_item_id',
        'descricao',
        'quantidade',
        'justificativa_pedido',
        'estimativa_valor',
        'justificativa_valor',
        'origem_recurso',
        'grau_prioridade',
        'data_ideal_aquisicao',
        'vinculacao_item',
        'PCA_solicitacao_id',
        'justificativa_vinculacao',
        'dt_preenchimento',
        'PCA_contrato_id',
    ];

    public $timestamps = false;

    public function contrato()
    {
        return $this->belongsTo(PcaContrato::class, 'PCA_contrato_id');
    }

    public function nomeItem()
    {
        return $this->belongsTo(PcaNomeItem::class, 'PCA_nome_item_id');
    }

    public function categoria()
    {
        return $this->belongsTo(PcaCategoria::class, 'PCA_categoria_id');
    }

    public function solicitacaoPai()
    {
        return $this->belongsTo(self::class, 'PCA_solicitacao_id');
    }

    public function solicitacaoFilha()
    {
        return $this->hasOne(self::class, 'PCA_solicitacao_id');
    }
}
