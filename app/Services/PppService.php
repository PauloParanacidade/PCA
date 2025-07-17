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
            'user_id' => Auth::id(),'status_id' => 1, // rascunho - CORRIGIDO: usar status_id
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

        // Atualizar PPP - CORRIGIDO: usar status_id em vez de status_fluxo
        $ppp->update([
            'status_id' => 2, // aguardando_aprovacao
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
            $gestorAtual = User::find($ppp->gestor_atual_id);

            $proximoGestor = $this->hierarquiaService->obterProximoGestor($gestorAtual);

            if ($proximoGestor) {
                $proximoGestor->garantirPapelGestor();

                // Ainda há níveis na hierarquia
                $ppp->update([
                    'status_id' => 2, // aguardando_aprovacao
                    'gestor_atual_id' => $proximoGestor->id,
                ]);
            } else {
                // Aprovação final
                $ppp->update([
                    'status_id' => 6, // aprovado_final
                    'gestor_atual_id' => null,
                ]);
            }

            $this->historicoService->registrarAprovacao($ppp,$comentario ?? 'PPP aprovado');

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
            // ✅ CORREÇÃO: Capturar status anterior antes da mudança
            $statusAnterior = $ppp->status_id;
            
            // ✅ CORREÇÃO: Manter gestor_atual_id (não definir como null)
            $ppp->update([
                'status_id' => 4, // aguardando_correcao
                // gestor_atual_id mantido (não alterado)
            ]);

            // ✅ CORREÇÃO: Passar status_anterior para o histórico
            $this->historicoService->registrarSolicitacaoCorrecao($ppp, $motivo, $statusAnterior);

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
        // CORRIGIDO: usar status_id
        $ppp->update([
            'status_id' => 7, // cancelado/reprovado (conforme PPPStatusSeeder)
            //'gestor_atual_id' => null,   // gestor_atual_id mantido (não alterado)
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
