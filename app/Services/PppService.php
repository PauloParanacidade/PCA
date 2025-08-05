<?php

namespace App\Services;

use App\Models\PcaPpp;
use App\Models\User; // Adicionar este import
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
                'status_id' => 1, // rascunho - CORRIGIDO: usar status_id
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
                $proximoGestor = null;
                $gestorAtual = User::find($ppp->gestor_atual_id);
                if(!$gestorAtual) {
                    $proximoGestor = $this->hierarquiaService->obterProximoGestor(Auth::user());
                } else {
                    $departamento = strtoupper($gestorAtual->department ?? '');
                    $areasEspeciais = ['SUPEX', 'DOE', 'DOM'];
                    
                    if(in_array($departamento, $areasEspeciais)) {
                        $proximoGestor = $this->hierarquiaService->obterGestorComTratamentoEspecial($ppp->user_id);
                        
                    } else if($gestorAtual->hasRole('daf')) {
                        $secretaria = $this->hierarquiaService->obterSecretaria();
                        if ($secretaria) {
                            $ppp->update([
                                'status_id' => 7, // Aguardando DIREX
                                'gestor_atual_id' => $secretaria->id,
                            ]);
                            $this->historicoService->registrarAprovacao(
                                $ppp,
                                ($comentario ?? 'PPP aprovado pelo DAF') . ' - Encaminhado para avaliação da DIREX'
                            );
                            return true;
                        } else {
                            throw new \Exception('Secretária não encontrada no sistema.');
                        }
                    } else {
                        $proximoGestor = $this->hierarquiaService->obterProximoGestor(Auth::user());
                    }
                }
                if (!$proximoGestor) {
                    throw new \Exception('Não foi possível identificar o próximo gestor.');
                }
                // Garantir que o próximo gestor tenha o papel de gestor
                $proximoGestor->garantirPapelGestor();
                // Atualizar PPP
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
        * Solicita correção do PPP
        */
        public function solicitarCorrecao(PcaPpp $ppp, string $motivo): bool
        {
            try {
                // Capturar status anterior antes da mudança
                $statusAnterior = $ppp->status_id;
                
                // Identificar o usuário que deve receber o PPP de volta
                $usuarioAnterior = $this->historicoService->identificarUsuarioAnterior($ppp);
                
                // Atualizar PPP: status para aguardando_correção e gestor para usuário anterior
                $ppp->update([
                    'status_id' => 4, // aguardando_correcao
                    'gestor_atual_id' => $usuarioAnterior, // PPP retorna para quem enviou
                ]);
                
                // Registrar no histórico
                $this->historicoService->registrarCorrecaoSolicitada($ppp, $motivo);
                
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
                    'status_id' => 6, // cancelado
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
        
        /**
        * Reenvia PPP após correção
        */
        /**
         * Reenvia PPP após correção
         */
        public function reenviarAposCorrecao(PcaPpp $ppp, ?string $comentario = null): bool
        {
            try {
                // ✅ CORREÇÃO: Usar a mesma lógica robusta do enviarParaAprovacao
                $proximoGestor = null;
                $gestorAtual = User::find($ppp->gestor_atual_id);
                
                if(!$gestorAtual) {
                    $proximoGestor = $this->hierarquiaService->obterProximoGestor(Auth::user());
                } else {
                    $departamento = strtoupper($gestorAtual->department ?? '');
                    $areasEspeciais = ['SUPEX', 'DOE', 'DOM'];
                    
                    if(in_array($departamento, $areasEspeciais)) {
                        $proximoGestor = $this->hierarquiaService->obterGestorComTratamentoEspecial($ppp->user_id);
                        
                    } else if($gestorAtual->hasRole('daf')) {
                        $secretaria = $this->hierarquiaService->obterSecretaria();
                        if ($secretaria) {
                            $ppp->update([
                                'status_id' => 7, // Aguardando DIREX
                                'gestor_atual_id' => $secretaria->id,
                            ]);
                            $this->historicoService->registrarAprovacao(
                                $ppp,
                                ($comentario ?? 'PPP reenviado após correção pelo DAF') . ' - Encaminhado para avaliação da DIREX'
                            );
                            return true;
                        } else {
                            throw new \Exception('Secretária não encontrada no sistema.');
                        }
                    } else {
                        $proximoGestor = $this->hierarquiaService->obterProximoGestor(Auth::user());
                    }
                }
                
                if (!$proximoGestor) {
                    throw new \Exception('Não foi possível identificar o próximo gestor.');
                }
                
                // Garantir que o próximo gestor tenha o papel de gestor
                $proximoGestor->garantirPapelGestor();
                
                // Atualizar PPP: status volta para aguardando_aprovacao
                $ppp->update([
                    'status_id' => 2, // aguardando_aprovacao
                    'gestor_atual_id' => $proximoGestor->id,
                ]);
                
                // Registrar no histórico
                $this->historicoService->registrarCorrecaoEnviada($ppp, $comentario);
                
                return true;
            } catch (\Throwable $ex) {
                Log::error('Erro ao reenviar PPP após correção: ' . $ex->getMessage());
                throw $ex;
            }
        }
        
        public function contarParaAvaliar(int $userId): int
        {
            $usuario = User::find($userId);
            
            // Se o usuário não tem permissão para avaliar, retorna 0
            if (!$usuario || !$usuario->hasAnyRole(['admin', 'daf', 'gestor', 'secretaria'])) {
                return 0;
            }
            
            return PcaPpp::where('status_id', 2)
                ->where('gestor_atual_id', $userId)
                ->count();
        }
        
        public function contarMeus(int $userId): int
        {
            return PcaPpp::where('user_id', $userId)
            ->count();
        }
    }