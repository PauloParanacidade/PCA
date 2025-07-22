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
            
            // ✅ EXCEÇÃO: Verificar se o gestor atual é SUPEX, DOE ou DOM
            $isSupexDoeOuDom = $this->verificarSeEhSupexDoeOuDom($gestorAtual);
            
            if ($isSupexDoeOuDom) {
                // Encaminhar diretamente para DAF
                $dafUser = $this->obterUsuarioDAF();
                
                if ($dafUser) {
                    $dafUser->garantirPapelGestor();
                    
                    $ppp->update([
                        'status_id' => 2, // aguardando_aprovacao
                        'gestor_atual_id' => $dafUser->id,
                    ]);
                    
                    $this->historicoService->registrarAprovacao(
                        $ppp, 
                        ($comentario ?? 'PPP aprovado') . ' - Encaminhado diretamente para DAF (exceção SUPEX/DOE/DOM)'
                    );
                    
                    return true;
                } else {
                    throw new \Exception('Usuário DAF não encontrado no sistema.');
                }
            }
            
            // Lógica normal para outros gestores
            $proximoGestor = $this->hierarquiaService->obterProximoGestor($gestorAtual);
        
            if($proximoGestor) {
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
        
            $this->historicoService->registrarAprovacao($ppp, $comentario ?? 'PPP aprovado');
        
            return true;
        
        } catch (\Throwable $ex) {
            Log::error('Erro ao aprovar PPP: ' . $ex->getMessage());
            throw $ex;
        }
    }

    /**
     * Verifica se o usuário pertence às áreas SUPEX, DOE ou DOM
     */
    private function verificarSeEhSupexDoeOuDom(User $usuario): bool
    {
        if (!$usuario) {
            return false;
        }
        
        // Verificar APENAS pelo campo department (sigla da área do próprio usuário)
        $department = strtoupper($usuario->department ?? '');
        
        // Verificar se é SUPEX, DOE ou DOM
        $areasEspeciais = ['SUPEX', 'DOE', 'DOM'];
        
        if (in_array($department, $areasEspeciais)) {
            Log::info('✅ Usuário identificado como SUPEX/DOE/DOM', [
                'user_id' => $usuario->id,
                'user_name' => $usuario->name,
                'department' => $department
            ]);
            return true;
        }
        return false;
    }

    /**
     * Obtém o primeiro usuário com role DAF ativo
     */
    private function obterUsuarioDAF()
    {
        Log::info('Buscando usuário DAF no sistema');
        
        $usuarioDAF = User::whereHas('roles', function ($query) {
            $query->where('name', 'daf');
        })->where('active', true)->first();
        
        if (!$usuarioDAF) {
            Log::error('Nenhum usuário DAF ativo encontrado no sistema');
            
            // Debug: listar todos os usuários com role daf
            $usuariosDaf = User::whereHas('roles', function ($query) {
                $query->where('name', 'daf');
            })->get();
            
            Log::info('Usuários com role DAF encontrados: ' . $usuariosDaf->count());
            
            throw new \Exception('Usuário DAF não encontrado no sistema.');
        }
        
        Log::info("Usuário DAF encontrado: {$usuarioDAF->name}");
        return $usuarioDAF;
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
