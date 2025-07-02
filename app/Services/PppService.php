<?php

namespace App\Services;

use App\Models\PcaPpp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\HierarquiaService;

class PppService
{
    protected $statusService;
    protected $historicoService;
    protected $hierarquiaService;

    public function __construct(
        PppStatusService $statusService,
        PppHistoricoService $historicoService,
        HierarquiaService $hierarquiaService
    ) {
        $this->statusService = $statusService;
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

        // Criar status inicial
        $statusDinamico = $this->statusService->criarStatusCustomizado($ppp, 'Rascunho');

        // Registrar no histórico
        $this->historicoService->registrarCriacao($ppp, $statusDinamico);

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

            // ✅ NOVA REGRA: Garantir que o próximo gestor tenha o papel de gestor
            $proximoGestor->garantirPapelGestor();

            // Atualizar PPP
            $ppp->update([
                'status_fluxo' => 'aguardando_aprovacao',
                'gestor_atual_id' => $proximoGestor->id,
            ]);

            // Criar status dinâmico
            $statusDinamico = $this->statusService->criarStatusComTemplate(
                $ppp, 
                'enviou_para_avaliacao', 
                Auth::id(), 
                $proximoGestor->id
            );

            // Registrar no histórico
            $this->historicoService->registrarEnvioAprovacao(
                $ppp, 
                $statusDinamico,
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
                // ✅ NOVA REGRA: Garantir que o próximo gestor tenha o papel de gestor
                $proximoGestor->garantirPapelGestor();
                
                // Ainda há níveis na hierarquia
                $ppp->update([
                    'status_fluxo' => 'aguardando_aprovacao',
                    'gestor_atual_id' => $proximoGestor->id,
                ]);
                
                $statusTipo = 'aprovado_proximo_nivel';
            } else {
                // Aprovação final
                $ppp->update([
                    'status_fluxo' => 'aprovado_final',
                    'gestor_atual_id' => null,
                ]);
                
                $statusTipo = 'aprovado_final';
            }

            // Criar status dinâmico
            $statusDinamico = $this->statusService->criarStatusComTemplate(
                $ppp, 
                $statusTipo, 
                Auth::id(), 
                $proximoGestor?->id
            );

            // Registrar no histórico
            $this->historicoService->registrarAprovacao(
                $ppp, 
                $statusDinamico,
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

            // Criar status dinâmico
            $statusDinamico = $this->statusService->criarStatusComTemplate(
                $ppp, 
                'solicitou_correcao', 
                Auth::id(), 
                $ppp->user_id
            );

            // Registrar no histórico
            $this->historicoService->registrarSolicitacaoCorrecao($ppp, $statusDinamico, $motivo);

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

            // Criar status dinâmico
            $statusDinamico = $this->statusService->criarStatusComTemplate(
                $ppp, 
                'reprovado', 
                Auth::id(), 
                $ppp->user_id
            );

            // Registrar no histórico
            $this->historicoService->registrarReprovacao($ppp, $statusDinamico, $motivo);

            return true;

        } catch (\Throwable $ex) {
            Log::error('Erro ao reprovar PPP: ' . $ex->getMessage());
            throw $ex;
        }
    }
}