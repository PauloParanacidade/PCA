<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcaPpp extends Model
{
    protected $table = 'pca_ppps';

    protected $fillable = [
        'user_id',
        'status_id',
        'gestor_atual_id',
        'categoria',
        'nome_item',
        'descricao',
        'quantidade',
        'justificativa_pedido',
        'estimativa_valor',
        'justificativa_valor',
        'origem_recurso',
        'grau_prioridade',
        'vinculacao_item',
        'justificativa_vinculacao',
        'renov_contrato',
        'num_contrato',
        'valor_contrato_atualizado',
        'mes_vigencia_final',
        'contrato_prorrogavel',
        'tem_contrato_vigente',
        'natureza_objeto',
    ];

    protected $casts = [
        'estimativa_valor' => 'decimal:2',
        'valor_contrato_atualizado' => 'decimal:2',
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
}