<?php

namespace App\Services;

use App\Models\PcaPpp;
use App\Models\PppHistorico;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PppHistoricoService
{
    /**
     * Registra uma ação no histórico do PPP
     */
    public function registrarAcao(
        PcaPpp $ppp,
        string $acao,
        string $justificativa,
        ?int $statusAnterior = null,
        ?int $statusAtual = null,
        ?int $userId = null
    ): PppHistorico {
        $historico = PppHistorico::create([
            'ppp_id' => $ppp->id,
            'status_anterior' => $statusAnterior,
            'status_atual' => $statusAtual ?? $ppp->status_id, // Usa o status atual do PPP se não fornecido
            'acao' => $acao,
            'justificativa' => $justificativa,
            'user_id' => $userId ?? Auth::id(),
        ]);
        
        Log::info('Histórico registrado', [
            'ppp_id' => $ppp->id,
            'acao' => $acao,
            'status_anterior' => $statusAnterior,
            'status_atual' => $statusAtual ?? $ppp->status_id,
            'user_id' => $userId ?? Auth::id()
        ]);
        
        return $historico;
    }
    
    /**
     * Registra criação do PPP
     */
    public function registrarCriacao(PcaPpp $ppp): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'criacao',
            'PPP criado pelo usuário',
            null, // status_anterior é null na criação
            $ppp->status_id // status_atual é o status inicial do PPP
        );
    }
    
    /**
     * Registra envio para aprovação
     */
    public function registrarEnvioAprovacao(PcaPpp $ppp, string $justificativa = null): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'envio_aprovacao',
            $justificativa ?? 'PPP enviado para aprovação'
        );
    }
    
    /**
     * Registra aprovação
     */
    public function registrarAprovacao(PcaPpp $ppp, string $comentario = null, int $statusAnterior = null): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'aprovacao',
            $comentario ?? 'PPP aprovado',
            $statusAnterior,
            $ppp->status_id
        );
    }
    
    /**
     * Registra solicitação de correção
     */
    public function registrarSolicitacaoCorrecao(PcaPpp $ppp, string $comentario, int $statusAnterior = null): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'solicitacao_correcao',
            $comentario,
            $statusAnterior,
            $ppp->status_id
        );
    }
    
    /**
     * Registra reprovação
     */
    public function registrarReprovacao(PcaPpp $ppp, string $motivo, int $statusAnterior = null): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'reprovacao',
            $motivo,
            $statusAnterior,
            $ppp->status_id
        );
    }
}