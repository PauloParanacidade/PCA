<?php


namespace App\Http\Controllers;

use App\Http\Requests\StorePppRequest;
use App\Models\PcaPpp;
use App\Models\PppHistorico;
use App\Models\PppStatusDinamico;
use App\Models\User;
// ❌ REMOVER: use App\Services\PppStatusService;
use App\Services\PppHistoricoService;
use App\Services\PppService;
use App\Services\HierarquiaService; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PppController extends Controller
{
    // ❌ REMOVER: protected $statusService;
    protected $historicoService;
    protected $hierarquiaService;
    protected $pppService;
    
    public function __construct(PppHistoricoService $historicoService, \App\Services\HierarquiaService $hierarquiaService, PppService $pppService)
    {
        $this->historicoService = $historicoService;
        $this->hierarquiaService = $hierarquiaService;
        $this->pppService = $pppService;
    }
    
    public function create()
    {
        return view('ppp.form', [
            'isCreating' => true,
            'showAllCards' => true // Nova flag para mostrar todos os cards
        ]);
    }
    
    public function store(StorePppRequest $request)
    {
        try {
            Log::info('🛠️ Ação detectada no store()', [
                'request_input_acao' => $request->input('acao'),
                'request_get_acao' => request('acao'),
                'request_method' => $request->method(),
                'request_full_data' => $request->all()
            ]);
            
            // 👉 AQUI: Logar tipo de ação
            Log::info('🎯 Tipo de ação', [
                'acao' => $request->input('acao')
            ]);
            
            if ($request->input('acao') === 'salvar_rascunho') {
                Log::info('💾 Ação detectada: salvar_rascunho');
            }
            
            // ✅ Processar valores monetários
            $estimativaFloat = floatval(str_replace(',', '.', str_replace(['R$', '.', ' '], '', $request->estimativa_valor)));
            
            $valorFloat = null;
            if ($request->filled('valor_contrato_atualizado')) {
                $valorFloat = floatval(str_replace(',', '.', str_replace(['R$', '.', ' '], '', $request->valor_contrato_atualizado)));
            }
            
            $ppp = PcaPpp::create([
                
                //CARD AZUL
                'user_id' => Auth::id(),
                'status_id' => 1,
                'nome_item' => $request->nome_item,
                'quantidade' => $request->quantidade,
                'grau_prioridade' => $request->grau_prioridade,
                'descricao' => $request->descricao,
                'natureza_objeto' => $request->natureza_objeto ?: '.',
                'categoria' => $request->categoria,
                'justificativa_pedido' => $request->justificativa_pedido,
                
                //CARD AMARELO
                'tem_contrato_vigente' => $request->tem_contrato_vigente ?: 'Não',
                
                //CARD VERDE
                'estimativa_valor' => $estimativaFloat ?: 0.01,
                'origem_recurso' => $request->origem_recurso ?: 'PRC',
                'valor_contrato_atualizado' => $valorFloat ?: 0.01,
                'justificativa_valor' => $request->justificativa_valor ?: '.',
                
                //CARD CIANO
                'vinculacao_item' => $request->vinculacao_item ?: 'Não',
                'justificativa_vinculacao' => $request->justificativa_vinculacao ?: '.',
                
                //A SER IMPLEMENTADO NO UPDATE, NO CAMPO VALOR SE +1 EXERCÍCIO
                // 'cronograma_jan' => $request->cronograma_jan ?: 'Não',
                // 'cronograma_fev' => $request->cronograma_fev ?: 'Não',
                // 'cronograma_mar' => $request->cronograma_mar ?: 'Não',
                // 'cronograma_abr' => $request->cronograma_abr ?: 'Não',
                // 'cronograma_mai' => $request->cronograma_mai ?: 'Não',
                // 'cronograma_jun' => $request->cronograma_jun ?: 'Não',
                // 'cronograma_jul' => $request->cronograma_jul ?: 'Não',
                // 'cronograma_ago' => $request->cronograma_ago ?: 'Não',
                // 'cronograma_set' => $request->cronograma_set ?: 'Não',
                // 'cronograma_out' => $request->cronograma_out ?: 'Não',
                // 'cronograma_nov' => $request->cronograma_nov ?: 'Não',
                // 'cronograma_dez' => $request->cronograma_dez ?: 'Não',
            ]);
            
            $this->historicoService->registrarCriacao($ppp);
            
            Log::info('✅ PPP criado com sucesso', [
                'ppp_id' => $ppp->id,
                'status_atual' => $ppp->status_id,
                'gestor_atual_id' => $ppp->gestor_atual_id,
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'PPP criado com sucesso.',
                    'ppp_id' => $ppp->id,
                    'actionValue' => 'aguardando_aprovacao'
                ]);
            }
            
            return redirect()->route('ppp.edit', $ppp->id)
            ->with('success', 'Rascunho salvo com sucesso! Agora você pode preencher os demais campos.');
            
            
        } catch (\Throwable $ex) {
            Log::error('💥 ERRO CRÍTICO ao criar PPP', [
                'exception_message' => $ex->getMessage(),
                'exception_file' => $ex->getFile(),
                'exception_line' => $ex->getLine(),
                'stack_trace' => $ex->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return back()->withInput()->withErrors(['msg' => 'Erro ao criar PPP: ' . $ex->getMessage()]);
        }
    }
    
    public function update(StorePppRequest $request, $id)
{
    try {
        $usuario = auth()->user();
        $acao = $request->input('acao'); // 'salvar' ou 'enviar_aprovacao'
        $modo = $request->input('modo'); // 'edicao' ou 'criacao'

        Log::info('🛠️ Ação detectada no update()', [
            'acao' => $acao,
            'modo' => $modo,
            'full_data' => $request->all()
        ]);

        $ppp = PcaPpp::findOrFail($id);
        $dados = $request->validated();

        // ✅ Tratamento de valores monetários
        if (isset($dados['estimativa_valor'])) {
            $dados['estimativa_valor'] = floatval(str_replace(',', '.', str_replace(['R$', '.', ' '], '', $dados['estimativa_valor'])));
        }

        if (isset($dados['valor_contrato_atualizado'])) {
            $dados['valor_contrato_atualizado'] = floatval(str_replace(',', '.', str_replace(['R$', '.', ' '], '', $dados['valor_contrato_atualizado'])));
        }

        // 🔄 Atualiza PPP (sem alterar status ainda)
        $statusAnterior = $ppp->status_id;
        $statusNovo = $dados['status_id'] ?? $statusAnterior;

        $ppp->fill($dados);
        $ppp->updated_at = $usuario->id;

        // 🎯 Se for apenas salvar (não enviar)
        if ($modo === 'edicao' && $acao === 'salvar') {
            $ppp->save();

            if ($statusAnterior != $statusNovo) {
                PppHistorico::create([
                    'ppp_id' => $ppp->id,
                    'status_anterior' => $statusAnterior,
                    'status_atual' => $statusNovo,
                    'justificativa' => $request->input('justificativa'),
                    'user_id' => $usuario->id,
                ]);
            }

            Log::info('PPP atualizada sem envio para aprovação', ['ppp_id' => $ppp->id]);
            return redirect()->route('ppp.show', $ppp->id)->with('success', 'PPP atualizada com sucesso.');
        }

        // ✅ Enviar para aprovação
        if ($acao === 'enviar_aprovacao') {
            Log::info('Enviando PPP para aprovação', ['ppp_id' => $ppp->id]);

            $resultado = $this->processarEnvioAprovacao($ppp, $request);

            if (!$resultado['success']) {
                Log::error('Erro ao enviar para aprovação', ['ppp_id' => $ppp->id, 'erro' => $resultado['message']]);
                return redirect()->back()->withErrors(['erro' => $resultado['message']]);
            }

            Log::info('PPP enviada com sucesso', ['ppp_id' => $ppp->id]);
            return redirect()->route('ppp.index')->with('success', 'PPP enviada para aprovação.');
        }

        // 🔃 Ação padrão: apenas salvar
        $ppp->save();

        if ($statusAnterior != $statusNovo) {
            PppHistorico::create([
                'ppp_id' => $ppp->id,
                'status_anterior' => $statusAnterior,
                'status_atual' => $statusNovo,
                'justificativa' => $request->input('justificativa'),
                'user_id' => $usuario->id,
            ]);
        }

        return redirect()->route('ppp.index')->with('success', 'PPP atualizada com sucesso.');

    } catch (\Throwable $ex) {
        Log::error('Erro ao atualizar PPP', [
            'erro' => $ex->getMessage(),
            'trace' => $ex->getTraceAsString(),
            'ppp_id' => $id,
        ]);

        return back()->withInput()->withErrors(['msg' => 'Erro ao atualizar.']);
    }
}


    public function index(Request $request)
    {
        try {
            Log::info('DEBUG PPP Index - Usuário atual', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'N/A'
            ]);
            
            $query = PcaPpp::query();
            
            if ($request->filled('tipo_visualizacao')) {
                switch ($request->tipo_visualizacao) {
                    case 'meus_ppps':
                        $query->where('user_id', Auth::id());
                        break;
                        
                    case 'pendentes_aprovacao':
                        $query->where('gestor_atual_id', Auth::id())
                        ->where('status_id', 2); // aguardando_aprovacao
                        break;
                        
                    default:
                        $query->where(function ($q) {
                            $q->where('user_id', Auth::id())
                            ->orWhere('gestor_atual_id', Auth::id())
                            ->orWhereExists(function ($subQuery) {
                                $subQuery->select(DB::raw(1))
                                ->from('ppp_gestores_historico')
                                ->whereColumn('ppp_gestores_historico.ppp_id', 'pca_ppps.id')
                                ->where('ppp_gestores_historico.gestor_id', Auth::id());
                            });
                        });
                    break;
                }
            } else {
                // Comportamento padrão: mostrar PPPs criados pelo usuário OU onde ele é gestor atual
                $query->where(function ($q) {
                    $q->where('user_id', Auth::id())
                    ->orWhere('gestor_atual_id', Auth::id());
                });
            }

        $query->with([
            'user',
            'status',
            'gestorAtual',
            'historicos.usuario'
            ])->orderBy('id', 'desc');
            
            // Filtro por status
            if ($request->filled('status_id')) {
                $query->where('status_id', $request->status_id);
            }
            
            // Filtro por busca
            if ($request->filled('busca')) {
                $busca = $request->busca;
                $query->where(function($q) use ($busca) {
                    $q->where('nome_item', 'like', "%{$busca}%")
                    ->orWhere('descricao', 'like', "%{$busca}%");
                });
            }
            
            $ppps = $query->paginate(10)->withQueryString();
            
            $ppps = $this->getNextApprover($ppps);
            
            return view('ppp.index', compact('ppps'));
            
        } catch (\Exception $e) {
            Log::error('Erro ao listar PPPs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar a lista de PPPs.');
        }
    }
    
    public function getNextApprover($ppps)
    {
        // Filtrar apenas IDs válidos (não nulos)
        $currentManagersIds = $ppps->map(function ($ppp) {
            return $ppp->gestor_atual_id;
        })->filter()->unique();
        
        $userManagerByIds = User::whereIn('id', $currentManagersIds)
        ->get()
        ->keyBy('id');
        
        foreach($ppps as $ppp) {
            // Verificar se gestor_atual_id existe e não é null
            if ($ppp->gestor_atual_id && isset($userManagerByIds[$ppp->gestor_atual_id])) {
                $currentManager = $userManagerByIds[$ppp->gestor_atual_id];
                
                $nomeGestor = 'N/A';
                $siglaAreaGestor = 'N/A';
                
                // Tentar extrair informações do gestor
                if (preg_match('/CN=([^,]+),OU=([^,]+)/', $currentManager->distinguishedname ?? '', $matches)) {
                    $nomeGestor = trim($matches[1]);
                    $siglaAreaGestor = trim($matches[2]);
                }
                
                $ppp->next_approver = $nomeGestor . ' - ' . $siglaAreaGestor;
                $ppp->current_approver = $currentManager->name . ' - ' . ($currentManager->department ?? 'N/A');
            } else {
                // Definir valores padrão quando não há gestor atual
                $ppp->next_approver = 'Aguardando definição';
                $ppp->current_approver = 'Nenhum gestor atribuído';
            }
        }
        
        return $ppps;
    }
    
    public function show($id)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);
            $historicos = PppHistorico::where('ppp_id', $ppp->id)
            ->with(['statusAnterior', 'statusAtual', 'usuario'])
            ->orderBy('created_at')
            ->get();
            
            Log::info('Exibindo PPP e histórico.', ['ppp_id' => $ppp->id, 'historico_count' => $historicos->count()]);
            $isCreating = false;
            
            return view('ppp.show', compact('ppp', 'historicos', 'isCreating'));
        } catch (\Throwable $ex) {
            Log::error('Erro ao exibir PPP:', [
                'exception' => $ex,
                'ppp_id' => $id,
            ]);
            Log::debug($ex->getTraceAsString());
            return back()->withErrors(['msg' => 'Erro ao exibir o PPP.']);
        }
    }
    
    /**
    * Retorna o histórico do PPP via AJAX
    */
    public function historico($id)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);
            $historicos = PppHistorico::where('ppp_id', $ppp->id)
            ->with(['statusAnterior', 'statusAtual', 'usuario'])
            ->orderBy('created_at')
            ->get();
            
            return view('ppp.partials.historico-modal', compact('ppp', 'historicos'));
        } catch (\Throwable $ex) {
            return response()->json(['error' => 'Erro ao carregar histórico'], 500);
        }
    }
    
    public function edit($id)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);
            $edicao = true;
            $isCreating = false;

            return view('ppp.form', compact('ppp','edicao', 'isCreating'));
        } catch (\Throwable $ex) {
            Log::error('Erro ao carregar PPP para edição:', [
                'exception' => $ex,
                'ppp_id' => $id,
            ]);
            Log::debug($ex->getTraceAsString());
            return back()->withErrors(['msg' => 'Erro ao carregar PPP para edição.']);
        }
    }
    
    
    
    public function destroy(Request $request, $id)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);
            
            // Validar comentário obrigatório
            $request->validate([
                'comentario' => 'required|string|min:10|max:1000'
            ], [
                'comentario.required' => 'O comentário é obrigatório para exclusão.',
                'comentario.min' => 'O comentário deve ter pelo menos 10 caracteres.',
                'comentario.max' => 'O comentário não pode exceder 1000 caracteres.'
            ]);
            
            // Registrar no histórico antes da exclusão
            \App\Models\PppHistorico::create([
                'ppp_id' => $ppp->id,
                'user_id' => auth()->id(),
                'acao' => 'exclusao',
                'justificativa' => $request->comentario,  // CORRIGIDO: comentario → justificativa
                'status_anterior' => $ppp->status_id,     // CORRIGIDO: status_anterior_id → status_anterior
                'status_atual' => $ppp->status_id,                   // CORRIGIDO: status_novo_id → status_atual
            ]);
            
            // Executar soft delete
            $ppp->delete();
            
            Log::info('PPP excluído com sucesso.', [
                'ppp_id' => $id,
                'user_id' => auth()->id(),
                'comentario' => $request->comentario
            ]);
            
            return redirect()->route('ppp.index')
            ->with('success', 'PPP excluído com sucesso. O comentário foi registrado no histórico.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $ex) {
            Log::error('Erro ao excluir PPP: ' . $ex->getMessage(), [
                'exception' => $ex,
                'ppp_id' => $id,
            ]);
            
            return back()->withErrors(['msg' => 'Erro ao excluir PPP: ' . $ex->getMessage()]);
        }
    }
    
    public function solicitarCorrecao(Request $request, PcaPpp $ppp)
    {
        $request->validate([
            'motivo' => 'required|string|max:1000'
        ]);
        
        try {
            $this->pppService->solicitarCorrecao($ppp, $request->motivo);
            
            return redirect()->route('ppp.index')
            ->with('success', 'Correção solicitada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
            ->with('error', 'Erro ao solicitar correção: ' . $e->getMessage());
        }
    }
    
    // private function criarStatusDinamico($ppp, $tipoStatus, $remetenteId = null, $destinatarioId = null, $statusCustom = null)
    // {
    //     // Desativar status dinâmico anterior
    //     $ppp->statusDinamicos()->update(['ativo' => false]);
        
    //     if ($statusCustom) {
    //         // Status customizado (ex: rascunho)
    //         $statusFormatado = $statusCustom;
    //         $remetenteSigla = null;
    //         $destinatarioSigla = null;
    //     } else {
    //         // Buscar template do status
    //         $statusTemplate = \App\Models\PppStatus::where('tipo', $tipoStatus)->first();
            
    //         if (!$statusTemplate) {
    //             throw new \Exception("Template de status não encontrado: {$tipoStatus}");
    //         }
            
    //         // Obter dados dos usuários
    //         $remetente = $remetenteId ? User::find($remetenteId) : null;
    //         $destinatario = $destinatarioId ? User::find($destinatarioId) : null;
            
    //         // Extrair siglas das áreas
    //         $remetenteSigla = $remetente ? $this->extrairSiglaArea($remetente) : null;
    //         $destinatarioSigla = $destinatario ? $this->extrairSiglaAreaGestor($destinatario) : null;
            
    //         // Substituir placeholders
    //         $statusFormatado = $statusTemplate->template;
            
    //         if ($remetente) {
    //             $remetenteTexto = $remetente->name . ' [' . ($remetenteSigla ?? 'N/A') . ']';
    //             $statusFormatado = str_replace('[remetente]', $remetenteTexto, $statusFormatado);
    //         }
            
    //         if ($destinatario) {
    //             $destinatarioTexto = $destinatario->name . ' [' . ($destinatarioSigla ?? 'N/A') . ']';
    //             $statusFormatado = str_replace('[destinatario]', $destinatarioTexto, $statusFormatado);
    //         }
    //     }
    // }
    
    /**
    * Extrai a sigla da área do próprio usuário (campo department)
    */
    private function extrairSiglaArea($usuario)
    {
        return $usuario->department ?? 'N/A';
    }
    
    /**
    * Extrai a sigla da área do gestor a partir do campo manager
    */
    private function extrairSiglaAreaGestor($usuario)
    {
        $managerDN = $usuario->manager;
        
        if (!$managerDN) {
            return 'N/A';
        }
        
        // Extrair OU (Organizational Unit) do DN
        // Formato: CN=Nome do Gestor,OU=Sigla da Área,DC=domain,DC=com
        if (preg_match('/OU=([^,]+)/', $managerDN, $matches)) {
            return trim($matches[1]);
        }
        
        return 'N/A';
    }
    
    public function enviarParaAprovacao($id, Request $request)
    {
        Log::info('🚀 PppController.enviarParaAprovacao() - INICIANDO', [
            'ppp_id' => $id,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'N/A',
            'request_method' => $request->method(),
            'is_ajax' => $request->ajax(),
            'request_data' => $request->all()
        ]);
        
        try {
            $ppp = PcaPpp::findOrFail($id);
            
            Log::info('✅ PPP encontrado', [
                'ppp_id' => $ppp->id,
                'ppp_nome' => $ppp->nome_item,
                'status_atual' => $ppp->status_id,
                'user_criador' => $ppp->user_id,
                'gestor_atual' => $ppp->gestor_atual_id
            ]);
            
            if ($ppp->user_id !== Auth::id()) {
                Log::warning('❌ Usuário não tem permissão para enviar este PPP', [
                    'ppp_user_id' => $ppp->user_id,
                    'current_user_id' => Auth::id()
                ]);
                
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Você não tem permissão para esta ação.'], 403);
                }
                return back()->withErrors(['msg' => 'Você não tem permissão para esta ação.']);
            }
            
            Log::info('✅ Permissão validada - Buscando próximo gestor');
            
            // ✅ ALTERAÇÃO: Usar HierarquiaService
            $proximoGestor = $this->hierarquiaService->obterProximoGestor(Auth::user());
            
            if (!$proximoGestor) {
                Log::error('❌ Próximo gestor não encontrado', [
                    'user_manager' => Auth::user()->manager ?? 'N/A'
                ]);
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Não foi possível identificar o próximo gestor.'], 400);
                }
                return back()->withErrors(['msg' => 'Não foi possível identificar o próximo gestor.']);
            }
            
            Log::info('✅ Próximo gestor encontrado', [
                'gestor_id' => $proximoGestor->id,
                'gestor_nome' => $proximoGestor->name
            ]);
            
            Log::info('🔄 Atualizando status do PPP', [
                'status_anterior' => $ppp->status_id,
                'status_novo' => 2, // aguardando_aprovacao
                'gestor_anterior' => $ppp->gestor_atual_id,
                'gestor_novo' => $proximoGestor->id
            ]);
            
            $ppp->update([
                'status_id' => 2, // aguardando_aprovacao
                'gestor_atual_id' => $proximoGestor->id,
            ]);
            
            Log::info('✅ PPP atualizado com sucesso', [
                'ppp_id' => $ppp->id,
                'novo_status' => $ppp->fresh()->status_id,
                'novo_gestor' => $ppp->fresh()->gestor_atual_id
            ]);
            
            // Registrar no histórico
            $justificativa = $request->input('justificativa', 'PPP enviado para aprovação');
            Log::info('📝 Registrando no histórico', ['justificativa' => $justificativa]);
            
            $this->historicoService->registrarEnvioAprovacao($ppp, $justificativa);
            
            Log::info('✅ Histórico registrado com sucesso');
            
            if ($request->ajax()) {
                $response = [
                    'success' => true,
                    'message' => 'PPP enviado para aprovação com sucesso!',
                    'ppp_id' => $ppp->id,
                    'novo_status' => $ppp->fresh()->status_id
                ];
                
                Log::info('📤 Retornando resposta AJAX', $response);
                return response()->json($response);
            }
            
            Log::info('🔄 Redirecionando para index');
            
            return redirect()->route('ppp.index')->with('success', 'PPP enviado para aprovação com sucesso!');
            
        } catch (\Throwable $ex) {
            Log::error('💥 ERRO em enviarParaAprovacao', [
                'exception_message' => $ex->getMessage(),
                'exception_file' => $ex->getFile(),
                'exception_line' => $ex->getLine(),
                'stack_trace' => $ex->getTraceAsString(),
                'ppp_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Erro ao enviar PPP para aprovação: ' . $ex->getMessage()], 500);
            }
            
            return back()->withErrors(['msg' => 'Erro ao enviar PPP para aprovação: ' . $ex->getMessage()]);
        }
    }
    
    public function aprovar(Request $request, PcaPpp $ppp, \App\Services\PppService $pppService)
    {
        $request->validate([
            'comentario' => 'nullable|string|max:1000'
        ]);
        if(!auth()->user()->hasAnyRole(['admin', 'daf', 'gestor'])) {
            return redirect()->back()->with('error', 'Você não tem permissão para aprovar PPPs.');
        }
        
        if ($ppp->status_id !== 2) { // 2 = aguardando_aprovacao
            return redirect()->back()->with('error', 'Este PPP não está aguardando aprovação.');
        }
        
        if ($ppp->gestor_atual_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Você não é o gestor responsável por este PPP.');
        }
        
        try {
            $resultado = $pppService->aprovarPpp($ppp, $request->input('comentario'));
            
            if ($resultado) return redirect()->route('ppp.index')->with('success', 'PPP aprovado com sucesso!');
            
            return redirect()->back()->with('error', 'Erro ao aprovar o PPP.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao aprovar PPP: ' . $e->getMessage());
        }
    }
    
    /**
    * Processa o envio para aprovação internamente
    */
    private function processarEnvioAprovacao(PcaPpp $ppp, Request $request): array
    {
        try {
            Log::info('🔄 processarEnvioAprovacao() - Iniciando processamento interno', [
                'ppp_id' => $ppp->id,
                'status_atual' => $ppp->status_id,
                'gestor_atual' => $ppp->gestor_atual_id,
                'user_solicitante' => Auth::id()
            ]);
            
            // ✅ ALTERAÇÃO: Usar HierarquiaService em vez da lógica duplicada
            $proximoGestor = $this->hierarquiaService->obterProximoGestor(Auth::user());
            
            Log::info('🔍 Resultado da busca por próximo gestor', [
                'proximo_gestor_encontrado' => $proximoGestor ? true : false,
                'proximo_gestor_id' => $proximoGestor ? $proximoGestor->id : null,
                'proximo_gestor_nome' => $proximoGestor ? $proximoGestor->name : null
            ]);
            
            if (!$proximoGestor) {
                Log::error('❌ Próximo gestor não encontrado', [
                    'ppp_id' => $ppp->id,
                    'user_id' => Auth::id()
                ]);
                return [
                    'success' => false,
                    'message' => 'Não foi possível identificar o próximo gestor.'
                ];
            }
            
            Log::info('📝 Atualizando status do PPP', [
                'ppp_id' => $ppp->id,
                'status_de' => $ppp->status_id,
                'status_para' => 2,
                'gestor_de' => $ppp->gestor_atual_id,
                'gestor_para' => $proximoGestor->id
            ]);
            
            //dd(HierarquiaService->extrairSiglaAreaGestor($proximoGestor));
            
            $ppp->update([
                'status_id' => 2, // aguardando_aprovacao
                'gestor_atual_id' => $proximoGestor->id,            
            ]);
            
            Log::info('✅ Status do PPP atualizado', [
                'ppp_id' => $ppp->id,
                'novo_status' => $ppp->fresh()->status_id,
                'novo_gestor' => $ppp->fresh()->gestor_atual_id
            ]);
            
            // Registrar no histórico
            $this->historicoService->registrarEnvioAprovacao(
                $ppp,
                'PPP enviado para aprovação automaticamente após criação'
            );
            
            Log::info('📋 Histórico registrado com sucesso', [
                'ppp_id' => $ppp->id
            ]);
            
            Log::info('✅ processarEnvioAprovacao() - Concluído com sucesso', [
                'ppp_id' => $ppp->id,
                'status_final' => $ppp->fresh()->status_id,
                'gestor_final' => $ppp->fresh()->gestor_atual_id
            ]);
            
            return [
                'success' => true,
                'message' => 'PPP enviado para aprovação com sucesso!'
            ];
            
        } catch (\Throwable $ex) {
            Log::error('💥 ERRO CRÍTICO em processarEnvioAprovacao()', [
                'ppp_id' => $ppp->id,
                'exception_message' => $ex->getMessage(),
                'exception_file' => $ex->getFile(),
                'exception_line' => $ex->getLine(),
                'stack_trace' => $ex->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => $ex->getMessage()
            ];
        }
    }
    /**
    * Reprova um PPP
    */
    public function reprovar(Request $request, PcaPpp $ppp, \App\Services\PppService $pppService)
    {
        // Verificar se o usuário tem permissão
        if (!auth()->user()->hasAnyRole(['admin', 'daf', 'gestor'])) {
            return redirect()->back()->with('error', 'Você não tem permissão para reprovar PPPs.');
        }
        
        // Verificar se o PPP está aguardando aprovação
        if ($ppp->status_id !== 2) { // 2 = aguardando_aprovacao
            return redirect()->back()->with('error', 'Este PPP não está aguardando aprovação.');
        }
        
        // Verificar se o usuário é o gestor responsável
        if ($ppp->gestor_atual_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Você não é o gestor responsável por este PPP.');
        }
        
        // Validar motivo obrigatório
        $request->validate([
            'motivo' => 'required|string|max:1000'
        ], [
            'motivo.required' => 'O motivo da reprovação é obrigatório.',
            'motivo.max' => 'O motivo não pode exceder 1000 caracteres.'
        ]);
        
        try {
            // Usar o PppService para reprovar
            $resultado = $pppService->reprovarPpp($ppp, $request->input('motivo'));
            
            if ($resultado) {
                return redirect()->route('ppp.index')->with('success', 'PPP reprovado com sucesso! O PPP permanece disponível para consultas e edições futuras.');
            } else {
                return redirect()->back()->with('error', 'Erro ao reprovar o PPP.');
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao reprovar PPP: ' . $e->getMessage());
        }
    }
    
    /**
    * Verifica se o PPP deve ser salvo como rascunho
    * baseado nos campos preenchidos (apenas card azul)
    */
    public function isRascunho($request)
    {
        // Campos obrigatórios do card azul (primeira etapa)
        $camposCardAzul = [
            'categoria',
            'nome_item',
            'descricao',
            'quantidade',
            'justificativa_pedido'
        ];
        
        // Campos das etapas seguintes
        $camposEtapasSeguintes = [
            'natureza_objeto',
            'grau_prioridade',
            'estimativa_valor',
            'justificativa_valor',
            'origem_recurso',
            'vinculacao_item',
            'tem_contrato_vigente'
        ];
        
        // Verifica se todos os campos do card azul estão preenchidos
        foreach ($camposCardAzul as $campo) {
            if (empty($request->input($campo))) {
                return false; // Se algum campo obrigatório não estiver preenchido, não é rascunho válido
            }
        }
        
        // Verifica se pelo menos um campo das etapas seguintes está vazio ou com valor padrão
        foreach ($camposEtapasSeguintes as $campo) {
            $valor = $request->input($campo);
            if (empty($valor) || in_array($valor, ['A definir', 'Valor a ser definido nas próximas etapas', '.'])) {
                return true; // É um rascunho se algum campo das próximas etapas não foi preenchido
            }
        }
        
        return false; // Todos os campos estão preenchidos, não é rascunho
    }
}



            
            