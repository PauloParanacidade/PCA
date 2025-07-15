<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PppHistorico extends Model
{
    protected $table = 'ppp_historicos';

    protected $fillable = [
        'ppp_id',
        'status_anterior',
        'status_atual',
        'justificativa',
        'user_id',
        'acao',
    ];

    // Relacionamento com o PPP
    public function ppp(): BelongsTo
    {
        return $this->belongsTo(PcaPpp::class, 'ppp_id');
    }

    // Relacionamento com o status anterior
    public function statusAnterior(): BelongsTo
    {
        return $this->belongsTo(PppStatus::class, 'status_anterior');
    }

    // Relacionamento com o status atual
    public function statusAtual(): BelongsTo
    {
        return $this->belongsTo(PppStatus::class, 'status_atual');
    }

    // Relacionamento com o usuário que realizou a ação
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
