<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcaPpp extends Model
{
    protected $table = 'pca_ppps';

    protected $fillable = [
        'user_id',
        'status_id',
        'area_solicitante',
        'area_responsavel',
        'cod_id_item',
        'categoria',
        'nome_item',
        'descricao',
        'quantidade',
        'justificativa_pedido',
        'estimativa_valor',
        'justificativa_valor',
        'origem_recurso',
        'grau_prioridade',
        'ate_partir_dia',
        'data_ideal_aquisicao',
        'vinculacao_item',
        'justificativa_vinculacao',
        'renov_contrato',
        'previsao',
        'num_contrato',
        'valor_contrato_atualizado',
    ];

    protected $casts = [
        'estimativa_valor' => 'float',
        'valor_contrato_atualizado' => 'float',
        'data_ideal_aquisicao' => 'date',
        'previsao' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function historicos()
    {
        return $this->hasMany(\App\Models\PppHistorico::class, 'ppp_id');
    }

    public function status()
    {
        return $this->belongsTo(\App\Models\PppStatus::class, 'status_id');
    }
}
