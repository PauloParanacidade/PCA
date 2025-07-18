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
     * 
     * @param PcaPpp $ppp
     * @param string $acao
     * @param string|null $justificativa - Apenas comentários digitados pelo usuário
     * @param int|null $statusAnterior
     * @param int|null $statusAtual
     * @param int|null $userId
     * @return PppHistorico
     */
    public function registrarAcao(
        PcaPpp $ppp,
        string $acao,
        ?string $justificativa = null,
        ?int $statusAnterior = null,
        ?int $statusAtual = null,
        ?int $userId = null
    ): PppHistorico {
        $historico = PppHistorico::create([
            'ppp_id' => $ppp->id,
            'status_anterior' => $statusAnterior,
            'status_atual' => $statusAtual ?? $ppp->status_id,
            'acao' => $acao,
            'justificativa' => $justificativa, // Apenas comentários do usuário ou null
            'user_id' => $userId ?? Auth::id(),
        ]);
        
        Log::info('Histórico registrado', [
            'ppp_id' => $ppp->id,
            'acao' => $acao,
            'status_anterior' => $statusAnterior,
            'status_atual' => $statusAtual ?? $ppp->status_id,
            'user_id' => $userId ?? Auth::id(),
            'justificativa' => $justificativa ? 'Presente' : 'Vazia'
        ]);
        
        return $historico;
    }

    /**
     * Registra criação do rascunho (apenas card azul salvo)
     * Perspectiva do sistema: PPP criado como rascunho
     * Perspectiva do usuário: Ainda não "criou" o PPP completo
     */
    public function registrarRascunhoCriado(PcaPpp $ppp): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'rascunho_criado',
            null, // Ação automática - sem justificativa
            null, // Não há status anterior
            1     // Status: rascunho
        );
    }

    /**
     * Registra envio completo do PPP para aprovação
     * Perspectiva do usuário: Agora sim "criou" e enviou o PPP
     * Perspectiva do sistema: PPP foi editado e mudou de status
     */
    public function registrarPppEnviado(PcaPpp $ppp, ?string $justificativa = null): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'ppp_enviado',
            $justificativa, // Apenas se usuário digitou comentário na modal
            1, // Status anterior: rascunho
            2  // Status atual: aguardando_aprovacao
        );
    }

    /**
     * Registra aprovação intermediária (gestor aprovou, mas PPP continua no fluxo)
     * Perspectiva do usuário: Gestor aprovou
     * Perspectiva do sistema: Status continua "aguardando_aprovacao" para próximo gestor
     */
    public function registrarAprovacaoIntermediaria(PcaPpp $ppp, ?string $comentario = null): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'aprovacao_intermediaria',
            $comentario, // Apenas se gestor digitou comentário opcional
            $ppp->status_id, // Status anterior: aguardando_aprovacao
            2  // Status atual: aguardando_aprovacao (continua no fluxo)
        );
    }

    /**
     * Registra aprovação final (última aprovação - DAF)
     * PPP vai para tabela PCA com ID incremental
     */
    public function registrarAprovacaoFinal(PcaPpp $ppp, ?string $comentario = null): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'aprovacao_final',
            $comentario, // Apenas se gestor digitou comentário opcional
            2, // Status anterior: aguardando_aprovacao
            6  // Status atual: aprovado_final
        );
    }

    /**
     * Registra quando gestor solicita correção
     * Modal com comentário obrigatório
     */
    public function registrarCorrecaoSolicitada(PcaPpp $ppp, string $motivo): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'correcao_solicitada',
            $motivo, // Comentário obrigatório do gestor
            2, // Status anterior: aguardando_aprovacao (ou em_avaliacao)
            4  // Status atual: aguardando_correcao
        );
    }

    /**
     * Registra quando usuário inicia a correção
     * Ação automática quando usuário acessa formulário para corrigir
     */
    public function registrarCorrecaoIniciada(PcaPpp $ppp): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'correcao_iniciada',
            null, // Ação automática - sem justificativa
            4, // Status anterior: aguardando_correcao
            5  // Status atual: em_correcao
        );
    }

    /**
     * Registra quando usuário reenvia após correções
     * Modal com comentário opcional
     */
    public function registrarCorrecaoEnviada(PcaPpp $ppp, ?string $comentario = null): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'correcao_enviada',
            $comentario, // Opcional - apenas se usuário digitou
            5, // Status anterior: em_correcao
            2  // Status atual: aguardando_aprovacao (volta ao fluxo)
        );
    }

    /**
     * Registra reprovação do PPP
     * Modal com comentário obrigatório
     * PPP fica bloqueado para edições futuras
     */
    public function registrarReprovacao(PcaPpp $ppp, string $motivo): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'reprovacao',
            $motivo, // Comentário obrigatório do gestor
            2, // Status anterior: aguardando_aprovacao (ou em_avaliacao)
            7  // Status atual: cancelado/reprovado
        );
    }

    /**
     * Registra exclusão do PPP (soft delete)
     * Modal com comentário obrigatório
     */
    public function registrarExclusao(PcaPpp $ppp, string $motivo): PppHistorico
    {
        $statusAnterior = $ppp->status_id;
        
        return $this->registrarAcao(
            $ppp,
            'exclusao',
            $motivo, // Comentário obrigatório
            $statusAnterior, // Qualquer status que estava antes
            $statusAnterior  // Status não muda (apenas soft delete)
        );
    }

    /**
     * Registra quando PPP entra em avaliação
     * Ação automática quando gestor visualiza PPP que não criou
     */
    public function registrarEmAvaliacao(PcaPpp $ppp): PppHistorico
    {
        return $this->registrarAcao(
            $ppp,
            'em_avaliacao',
            null, // Ação automática - sem justificativa
            2, // Status anterior: aguardando_aprovacao
            3  // Status atual: em_avaliacao
        );
    }

    // ===== MÉTODOS LEGADOS (para compatibilidade) =====
    // Estes métodos mantêm compatibilidade com código existente
    // mas internamente usam os novos métodos

    /**
     * @deprecated Use registrarRascunhoCriado() instead
     */
    public function registrarCriacao(PcaPpp $ppp): PppHistorico
    {
        return $this->registrarRascunhoCriado($ppp);
    }

    /**
     * @deprecated Use registrarPppEnviado() instead
     */
    public function registrarEnvioAprovacao(PcaPpp $ppp, ?string $justificativa = null): PppHistorico
    {
        return $this->registrarPppEnviado($ppp, $justificativa);
    }

    /**
     * @deprecated Use registrarAprovacaoIntermediaria() or registrarAprovacaoFinal() instead
     */
    public function registrarAprovacao(PcaPpp $ppp, ?string $comentario = null, ?int $statusAnterior = null): PppHistorico
    {
        // Determinar se é aprovação final baseado no status atual
        if ($ppp->status_id == 6) { // aprovado_final
            return $this->registrarAprovacaoFinal($ppp, $comentario);
        } else {
            return $this->registrarAprovacaoIntermediaria($ppp, $comentario);
        }
    }

    /**
     * @deprecated Use registrarCorrecaoSolicitada() instead
     */
    public function registrarSolicitacaoCorrecao(PcaPpp $ppp, string $comentario, ?int $statusAnterior = null): PppHistorico
    {
        return $this->registrarCorrecaoSolicitada($ppp, $comentario);
    }

    /**
     * Obtém histórico completo do PPP ordenado cronologicamente
     */
    public function obterHistoricoCompleto(PcaPpp $ppp)
    {
        return PppHistorico::where('ppp_id', $ppp->id)
            ->with(['statusAnterior', 'statusAtual', 'usuario'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Obtém última ação registrada no PPP
     */
    public function obterUltimaAcao(PcaPpp $ppp): ?PppHistorico
    {
        return PppHistorico::where('ppp_id', $ppp->id)
            ->with(['statusAnterior', 'statusAtual', 'usuario'])
            ->latest('created_at')
            ->first();
    }

    /**
     * Verifica se PPP já foi enviado para aprovação
     */
    public function jaoiEnviado(PcaPpp $ppp): bool
    {
        return PppHistorico::where('ppp_id', $ppp->id)
            ->where('acao', 'ppp_enviado')
            ->exists();
    }

    /**
     * Conta quantas aprovações o PPP já recebeu
     */
    public function contarAprovacoesRecebidas(PcaPpp $ppp): int
    {
        return PppHistorico::where('ppp_id', $ppp->id)
            ->whereIn('acao', ['aprovacao_intermediaria', 'aprovacao_final'])
            ->count();
    }

    /**
     * Verifica se PPP já foi reprovado
     */
    public function foiReprovado(PcaPpp $ppp): bool
    {
        return PppHistorico::where('ppp_id', $ppp->id)
            ->where('acao', 'reprovacao')
            ->exists();
    }
}