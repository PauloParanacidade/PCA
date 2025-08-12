<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $ppp_id
 * @property int|null $status_anterior
 * @property int $status_atual
 * @property string|null $justificativa Comentário do usuário sobre a ação (null para ações automáticas)
 * @property int $user_id
 * @property string|null $acao
 * @property string|null $dados_adicionais Dados adicionais da ação em formato JSON
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PcaPpp $ppp
 * @property-read \App\Models\PppStatus|null $statusAnterior
 * @property-read \App\Models\PppStatus $statusAtual
 * @property-read \App\Models\User $usuario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico whereAcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico whereDadosAdicionais($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico whereJustificativa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico wherePppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico whereStatusAnterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico whereStatusAtual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppHistorico whereUserId($value)
 * @mixin \Eloquent
 */
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
