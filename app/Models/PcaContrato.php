<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcaContrato extends Model
{
    protected $table = 'PCA_contrato';

    protected $fillable = ['nome', 'valor', 'aditivo'];

    public $timestamps = false;

    public function solicitacao()
    {
        return $this->hasOne(PcaSolicitacao::class, 'PCA_contrato_id');
    }
}
