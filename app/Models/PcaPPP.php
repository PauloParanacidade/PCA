<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcaPpp extends Model
{
    protected $table = 'pca_ppps'; // snake_case, padrão Laravel

    protected $fillable = [
        'area_solicitante',
        'area_responsavel',
        'data_status',
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
        'num_contrato',
        'valor_contrato_atualizado',
        'historico',
        
    ];

    public $timestamps = true;

    // Relacionamentos futuros (comente ou adicione conforme implementar)
    // public function contrato()
    // {
    //     return $this->belongsTo(PcaContrato::class, 'PCA_contrato_id');
    // }
}
