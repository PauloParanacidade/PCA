<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ ADICIONAR

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
        'num_contrato',
        'mes_vigencia_final',
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