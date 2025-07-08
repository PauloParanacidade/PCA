<?php

namespace App\Services;

use App\Models\PcaPpp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\HierarquiaService;

class PppService
{
    protected $historicoService;
    protected $hierarquiaService;

    public function __construct(
        PppHistoricoService $historicoService,
        HierarquiaService $hierarquiaService
    ) {
        $this->historicoService = $historicoService;
        $this->hierarquiaService = $hierarquiaService;
    }

    /**
     * Cria um novo PPP
     */
    public function criarPpp(array $dados): PcaPpp
    {
        $ppp = PcaPpp::create([
            'user_id' => Auth::id(),
            'status_fluxo' => 'rascunho',
            ...$dados
        ]);

        // Registrar no histórico
        $this->historicoService->registrarCriacao($ppp);

        return $ppp;
    }

    /**
     * Envia PPP para aprovação
     */
    public function enviarParaAprovacao(PcaPpp $ppp, ?string $justificativa = null): bool
    {
        try {
            // Obter próximo gestor
            $proximoGestor = $this->hierarquiaService->obterProximoGestor($ppp->user_id);
            
            if (!$proximoGestor) {
                throw new \Exception('Não foi possível identificar o próximo gestor.');
            }

            // Garantir que o próximo gestor tenha o papel de gestor
            $proximoGestor->garantirPapelGestor();

            // Atualizar PPP
            $ppp->update([
                'status_fluxo' => 'aguardando_aprovacao',
                'gestor_atual_id' => $proximoGestor->id,
            ]);

            // Registrar no histórico
            $this->historicoService->registrarEnvioAprovacao(
                $ppp,
                $justificativa ?? 'PPP enviado para aprovação'
            );

            return true;

        } catch (\Throwable $ex) {
            Log::error('Erro ao enviar PPP para aprovação: ' . $ex->getMessage());
            throw $ex;
        }
    }

    /**
     * Aprova um PPP
     */
    public function aprovarPpp(PcaPpp $ppp, ?string $comentario = null): bool
    {
        try {
            $proximoGestor = $this->hierarquiaService->obterProximoGestor(
                User::find($ppp->gestor_atual_id)
            );

            if ($proximoGestor) {
                // Garantir que o próximo gestor tenha o papel de gestor
                $proximoGestor->garantirPapelGestor();
                
                // Ainda há níveis na hierarquia
                $ppp->update([
                    'status_fluxo' => 'aguardando_aprovacao',
                    'gestor_atual_id' => $proximoGestor->id,
                ]);
            } else {
                // Aprovação final
                $ppp->update([
                    'status_fluxo' => 'aprovado_final',
                    'gestor_atual_id' => null,
                ]);
            }

            // Registrar no histórico
            $this->historicoService->registrarAprovacao(
                $ppp,
                $comentario ?? 'PPP aprovado'
            );

            return true;

        } catch (\Throwable $ex) {
            Log::error('Erro ao aprovar PPP: ' . $ex->getMessage());
            throw $ex;
        }
    }

    /**
     * Solicita correção do PPP
     */
    public function solicitarCorrecao(PcaPpp $ppp, string $motivo): bool
    {
        try {
            $ppp->update([
                'status_fluxo' => 'correcao_solicitada',
                'gestor_atual_id' => null,
            ]);

            // Registrar no histórico
            $this->historicoService->registrarSolicitacaoCorrecao($ppp, $motivo);

            return true;

        } catch (\Throwable $ex) {
            Log::error('Erro ao solicitar correção: ' . $ex->getMessage());
            throw $ex;
        }
    }

    /**
     * Reprova um PPP
     */
    public function reprovarPpp(PcaPpp $ppp, string $motivo): bool
    {
        try {
            $ppp->update([
                'status_fluxo' => 'reprovado',
                'gestor_atual_id' => null,
            ]);

            // Registrar no histórico
            $this->historicoService->registrarReprovacao($ppp, $motivo);

            return true;

        } catch (\Throwable $ex) {
            Log::error('Erro ao reprovar PPP: ' . $ex->getMessage());
            throw $ex;
        }
    }
}