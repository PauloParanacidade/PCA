<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PppStatusDinamico extends Model
{
    protected $table = 'ppp_status_dinamicos';

    protected $fillable = [
        'ppp_id',
        'status_tipo_id',
        'remetente_nome',
        'remetente_sigla',
        'destinatario_nome',
        'destinatario_sigla',
        'status_formatado',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function ppp()
    {
        return $this->belongsTo(PcaPpp::class, 'ppp_id');
    }

    public function statusTipo()
    {
        return $this->belongsTo(PppStatus::class, 'status_tipo_id');
    }
}