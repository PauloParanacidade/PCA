<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PppGestorHistorico extends Model
{
    protected $table = 'ppp_gestores_historico';
    
    protected $fillable = [
        'ppp_id',
        'gestor_id', 
        'data_envio',
        'data_acao',
        'acao'
    ];
    
    protected $casts = [
        'data_envio' => 'datetime',
        'data_acao' => 'datetime'
    ];
    
    public function ppp()
    {
        return $this->belongsTo(PcaPpp::class, 'ppp_id');
    }
    
    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }
}