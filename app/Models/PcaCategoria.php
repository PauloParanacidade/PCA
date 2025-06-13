<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcaCategoria extends Model
{
    protected $table = 'PCA_categoria';

    protected $fillable = ['nome'];

    public $timestamps = false;

    public function solicitacao()
    {
        return $this->hasOne(PcaSolicitacao::class, 'PCA_categoria_id');
    }
}
