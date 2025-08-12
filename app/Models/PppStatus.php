<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nome Nome do status
 * @property string $slug Identificador único para o status
 * @property string|null $descricao Descrição detalhada do status
 * @property int $ordem Ordem de exibição/processamento
 * @property bool $ativo Status ativo no sistema
 * @property string $cor Cor hexadecimal para exibição
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus ativos()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus ordenados()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus whereCor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PppStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PppStatus extends Model
{
    protected $table = 'ppp_statuses';

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'ordem',
        'ativo',
        'cor',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /**
     * Scope para buscar apenas status ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para ordenar por ordem
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem');
    }
}
