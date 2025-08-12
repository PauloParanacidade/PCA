<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $ppp_id
 * @property int $gestor_id
 * @property \Illuminate\Support\Carbon|null $data_envio
 * @property \Illuminate\Support\Carbon|null $data_acao
 * @property string|null $acao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $gestor
 * @property-read \App\Models\PcaPpp $ppp
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppGestorHistorico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppGestorHistorico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppGestorHistorico query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppGestorHistorico whereAcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppGestorHistorico whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppGestorHistorico whereDataAcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppGestorHistorico whereDataEnvio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppGestorHistorico whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppGestorHistorico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppGestorHistorico wherePppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppGestorHistorico whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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