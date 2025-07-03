<?php

namespace App\Services;

use App\Models\PcaPpp;
use App\Models\PppHistorico;
use App\Models\PppStatusDinamico;
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
        ?PppStatusDinamico $statusDinamico = null,
        ?int $userId = null
    ): PppHistorico {
        $historico = PppHistorico::create([
            'ppp_id' => $ppp->id,
            'status_dinamico_id' => $statusDinamico?->id,
            'status_atual' =>$statusDinamico?->status_tipo_id,
            'acao' => $acao,
            'justificativa' => $justificativa,
            'user_id' => $userId ?? Auth::id(),
        ]);
        
        Log::info('Histórico registrado', [
            'ppp_id' => $ppp->id,
            'acao' => $acao,
            'user_id' => $userId ?? Auth::id()
        ]);
        
        return $historico;
    }
    
    /**
     * Registra criação do PPP
     */
    public function registrarCriacao(PcaPpp $ppp, PppStatusDinamico $statusDinamico): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'criacao',
            'PPP criado pelo usuário',
            $statusDinamico
        );
    }
    
    /**
     * Registra envio para aprovação
     */
    public function registrarEnvioAprovacao(PcaPpp $ppp, PppStatusDinamico $statusDinamico, string $justificativa = null): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'envio_aprovacao',
            $justificativa ?? 'PPP enviado para aprovação',
            $statusDinamico
        );
    }
    
    /**
     * Registra aprovação
     */
    public function registrarAprovacao(PcaPpp $ppp, PppStatusDinamico $statusDinamico, string $comentario = null): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'aprovacao',
            $comentario ?? 'PPP aprovado',
            $statusDinamico
        );
    }
    
    /**
     * Registra solicitação de correção
     */
    public function registrarSolicitacaoCorrecao(PcaPpp $ppp, PppStatusDinamico $statusDinamico, string $comentario): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'solicitacao_correcao',
            $comentario,
            $statusDinamico
        );
    }
    
    /**
     * Registra reprovação
     */
    public function registrarReprovacao(PcaPpp $ppp, PppStatusDinamico $statusDinamico, string $motivo): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'reprovacao',
            $motivo,
            $statusDinamico
        );
    }
}