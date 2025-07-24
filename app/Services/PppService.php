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
            $criadorPpp = User::find($ppp->user_id);
            
            // ✅ NOVA LÓGICA: Verificar se o criador do PPP é DAF, DOM, DOE, SUPEX ou Secretária
            if ($criadorPpp && $this->verificarSeEhPerfilEspecialParaDirex($criadorPpp)) {
                // Buscar secretária diretamente
                $secretaria = User::whereHas('roles', function ($query) {
                    $query->where('name', 'secretaria');
                })->where('active', true)->first();
                
                if ($secretaria) {
                    $ppp->update([
                        'status_id' => 7, // aguardando_direx
                        'gestor_atual_id' => $secretaria->id,
                    ]);
                    
                    $this->historicoService->registrarEnvioAprovacao(
                        $ppp,
                        ($justificativa ?? 'PPP enviado para aprovação') . ' - Encaminhado diretamente para DIREX (perfil especial)'
                    );
                    
                    return true;
                } else {
                    throw new \Exception('Secretária não encontrada no sistema.');
                }
            }
            
            // Lógica normal para outros usuários
            $proximoGestor = $this->hierarquiaService->obterProximoGestor($ppp->user_id);
    
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
     * Aprova um PPP
     */
    public function aprovarPpp(PcaPpp $ppp, ?string $comentario = null): bool
    {
        try {
            $gestorAtual = User::find($ppp->gestor_atual_id);
            
            // ✅ VERIFICAÇÃO DAF: Se gestor atual é DAF, encaminhar para Secretária
            if ($gestorAtual && $gestorAtual->hasRole('daf')) {
                $secretaria = User::whereHas('roles', function ($query) {
                    $query->where('name', 'secretaria');
                })->where('active', true)->first();
                
                if ($secretaria) {
                    $ppp->update([
                        'status_id' => 7, // aguardando_direx
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
            }
            
            // ✅ VERIFICAÇÃO SUPEX/DOE/DOM: Encaminhar para DAF
            if ($gestorAtual && $this->verificarSeEhSupexDoeOuDom($gestorAtual)) {
                $dafUser = $this->obterUsuarioDAF();
                
                if ($dafUser) {
                    $dafUser->garantirPapelGestor();
                    
                    $ppp->update([
                        'status_id' => 2, // aguardando_aprovacao
                        'gestor_atual_id' => $dafUser->id,
                    ]);
                    
                    $this->historicoService->registrarAprovacao(
                        $ppp, 
                        ($comentario ?? 'PPP aprovado') . ' - Encaminhado para DAF (SUPEX/DOE/DOM)'
                    );
                    
                    return true;
                } else {
                    throw new \Exception('Usuário DAF não encontrado no sistema.');
                }
            }
            
            // ✅ LÓGICA NORMAL: Para todos os outros gestores (incluindo Aníbal → Camila)
            $proximoGestor = $this->hierarquiaService->obterProximoGestor($gestorAtual);
        
            if($proximoGestor) {
                $proximoGestor->garantirPapelGestor();
        
                $ppp->update([
                    'status_id' => 2, // aguardando_aprovacao
                    'gestor_atual_id' => $proximoGestor->id,
                ]);
            } else {
                throw new \Exception('Fim da hierarquia atingido sem encontrar próximo gestor.');
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


/**
 * Verifica se o usuário é DAF, DOM, DOE, SUPEX ou Secretária
 */
private function verificarSeEhPerfilEspecialParaDirex(User $usuario): bool
{
    // Verificar por role secretaria
    if ($usuario->hasRole('secretaria')) {
        return true;
    }
    
    // Verificar por role DAF
    if ($usuario->hasRole('daf')) {
        return true;
    }
    
    // Verificar por department (DOM, DOE, SUPEX)
    $department = strtoupper($usuario->department ?? '');
    $departamentosEspeciais = ['DOM', 'DOE', 'SUPEX'];
    
    if (in_array($department, $departamentosEspeciais)) {
        return true;
    }
    
    return false;
}
}
