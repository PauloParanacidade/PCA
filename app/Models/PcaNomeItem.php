<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcaNomeItem extends Model
{
    protected $table = 'PCA_nome_item';

    protected $fillable = ['nome'];

    public $timestamps = false;

    public function solicitacao()
    {
        return $this->hasOne(PcaSolicitacao::class, 'PCA_nome_item_id');
    }
}
