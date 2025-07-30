<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePppRequest;
use App\Models\PcaPpp;
use App\Models\PppHistorico;
use App\Models\User;
use App\Services\PppHistoricoService;
use App\Services\PppService;
use App\Services\HierarquiaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PcaExport;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;


class PppController extends Controller
{
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
            
            // 🔍 LOG: Valores financeiros recebidos do frontend
            Log::info('💰 VALORES FINANCEIROS - Processamento completo', [
                'estimativa_valor_original' => $request->estimativa_valor,
                'valor_contrato_atualizado_original' => $request->valor_contrato_atualizado,
                'user_id' => Auth::id(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            // ✅ Processar valores monetários
            $estimativaFloat = floatval(str_replace(',', '.', str_replace(['R$', '.', ' '], '', $request->estimativa_valor)));
            
            $valorFloat = null;
            if ($request->filled('valor_contrato_atualizado')) {
                $valorFloat = floatval(str_replace(',', '.', str_replace(['R$', '.', ' '], '', $request->valor_contrato_atualizado)));
            }
            
            // 🔍 LOG: Valores após conversão para float
            Log::info('💰 VALORES FINANCEIROS - Após conversão para float', [
                'estimativa_valor_convertido' => $estimativaFloat,
                'valor_contrato_atualizado_convertido' => $valorFloat,
                'estimativa_valor_que_sera_salvo' => $estimativaFloat ?: 0.01,
                'valor_contrato_atualizado_que_sera_salvo' => $valorFloat ?: 0.01
            ]);
            
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
            
            // 🔍 LOG: Confirmação dos valores salvos no banco
            Log::info('✅ PPP criado - Valores financeiros confirmados no banco', [
                'ppp_id' => $ppp->id,
                'estimativa_valor_salvo_no_banco' => $ppp->estimativa_valor,
                'valor_contrato_atualizado_salvo_no_banco' => $ppp->valor_contrato_atualizado,
                'tipo_estimativa_valor' => gettype($ppp->estimativa_valor),
                'tipo_valor_contrato_atualizado' => gettype($ppp->valor_contrato_atualizado)
            ]);

            $this->historicoService->registrarCriacao($ppp);
        
        Log::info('✅ PPP criado com sucesso', [
            'ppp_id' => $ppp->id,
            'status_atual' => $ppp->status_id,
            'gestor_atual_id' => $ppp->gestor_atual_id,
        ]);
        
        // ✅ NOVO: Verificar se deve enviar para aprovação
        if ($request->input('acao') === 'enviar_aprovacao') {
            try {
                Log::info('🚀 Enviando PPP recém-criado para aprovação', [
                    'ppp_id' => $ppp->id
                ]);
                
                $this->pppService->enviarParaAprovacao(
                    $ppp,
                    $request->input('justificativa')
                );
                
                Log::info('✅ PPP enviado para aprovação com sucesso');
                
                return redirect()
                    ->route('ppp.meus')
                    ->with('success', 'PPP criado e enviado para aprovação com sucesso.');
                    
            } catch (\Throwable $e) {
                Log::error('❌ Erro ao enviar PPP para aprovação: '.$e->getMessage());
                return redirect()
                    ->route('ppp.edit', $ppp->id)
                    ->with('error', 'PPP criado, mas houve erro ao enviar para aprovação: ' . $e->getMessage());
            }
        }
        
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
        // DEBUG: Verificar dados recebidos
        Log::info('📝 Dados recebidos no update:', [
            'acao' => $request->input('acao'),
            'modo' => $request->input('modo'),
            'ppp_id' => $id,
            'all_data' => $request->all()
        ]);
        
        $usuario = auth()->user();
        $acao    = $request->input('acao'); // 'salvar' ou 'enviar_aprovacao'
        $modo    = $request->input('modo'); // 'edicao' ou 'criacao'

        Log::info('🛠️ Ação detectada no update()', [
            'ppp_id' => $id,
            'acao'   => $acao,
            'modo'   => $modo,
            'data'   => $request->all()
        ]);
        //dd($request->all());

        if ($modo === 'edicao' && $acao === 'salvar') {
            $ppp = PcaPpp::findOrFail($id);

            $statusAnterior = $ppp->status_id;
            $ppp->fill($request->validated());

            $ppp = $this->processMonetaryFields($request, $ppp);

            $ppp->save();

            if ($statusAnterior != $ppp->status_id) {
                PppHistorico::create([
                    'ppp_id'          => $ppp->id,
                    'status_anterior' => $statusAnterior,
                    'status_atual'    => $ppp->status_id,
                    'justificativa'   => $request->input('justificativa'),
                    'user_id'         => $usuario->id,
                ]);
            }

            return redirect()
                ->route('ppp.show', $ppp->id)
                ->with('success', 'PPP atualizada com sucesso.');
        }

        if ($acao === 'enviar_aprovacao') {
            try {
                Log::info('🚀 Iniciando envio para aprovação', [
                    'ppp_id' => $id,
                    'user_id' => auth()->id(),
                    'dados' => $request->validated()
                ]);
                
                $ppp = PcaPpp::findOrFail($id);
                
                // ✅ Salvar os dados do formulário ANTES de enviar
                $ppp->fill($request->validated());
                $ppp = $this->processMonetaryFields($request, $ppp);
                $ppp->save();
                
                Log::info('✅ PPP salvo com sucesso, enviando para aprovação');
                
                // Delegamos ao service todo o fluxo de aprovação
                $this->pppService->enviarParaAprovacao(
                    $ppp,
                    $request->input('justificativa')
                );
                
                Log::info('✅ PPP enviado para aprovação com sucesso, redirecionando');
                
                return redirect()
                    ->route('ppp.meus')
                    ->with('success', 'PPP enviada para aprovação.');
                    
            } catch (\Throwable $e) {
                Log::error('❌ Erro ao enviar PPP para aprovação no update: '.$e->getMessage(), [
                    'ppp_id' => $id,
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()->withErrors(['erro' => $e->getMessage()]);
            }
        }

        // Ação padrão: apenas salvar quaisquer outras alterações
        $ppp = PcaPpp::findOrFail($id);
        $statusAnterior = $ppp->status_id;

        $ppp->fill($request->validated());
        $ppp->save();

        if ($statusAnterior != $ppp->status_id) {
            PppHistorico::create([
                'ppp_id'          => $ppp->id,
                'status_anterior' => $statusAnterior,
                'status_atual'    => $ppp->status_id,
                'justificativa'   => $request->input('justificativa'),
                'user_id'         => $usuario->id,
            ]);
        }

        return redirect()
            ->route('ppp.meus')
            ->with('success', 'PPP atualizada com sucesso.');
}

    public function processMonetaryFields($request, $ppp) : PcaPpp
    {
        Log::info('💰 PROCESSAMENTO VALORES - Entrada', [
            'estimativa_valor_original' => $request->estimativa_valor,
            'valor_contrato_original' => $request->valor_contrato_atualizado,
            'tipo_estimativa' => gettype($request->estimativa_valor),
            'tipo_valor_contrato' => gettype($request->valor_contrato_atualizado)
        ]);
        
        // ✅ CORREÇÃO: Processar apenas se o valor estiver formatado em padrão brasileiro
        $estimativaValor = $request->estimativa_valor;
        
        if (is_string($estimativaValor) && strpos($estimativaValor, 'R$') !== false) {
            // Valor formatado brasileiro: "R$ 1.234,56"
            $estimativaFloat = floatval(str_replace(',', '.', str_replace(['R$', '.', ' '], '', $estimativaValor)));
        } else {
            // Valor já numérico: 1234.56
            $estimativaFloat = floatval($estimativaValor);
        }

        $valorFloat = null;
        if ($request->filled('valor_contrato_atualizado')) {
            $valorContratoAtualizado = $request->valor_contrato_atualizado;
            
            if (is_string($valorContratoAtualizado) && strpos($valorContratoAtualizado, 'R$') !== false) {
                // Valor formatado brasileiro: "R$ 4.567,89"
                $valorFloat = floatval(str_replace(',', '.', str_replace(['R$', '.', ' '], '', $valorContratoAtualizado)));
            } else {
                // Valor já numérico: 4567.89
                $valorFloat = floatval($valorContratoAtualizado);
            }
        }
        
        $ppp->estimativa_valor = $estimativaFloat;
        $ppp->valor_contrato_atualizado = $valorFloat;

        Log::info('💰 PROCESSAMENTO VALORES - Saída', [
            'estimativa_valor_processado' => $estimativaFloat,
            'valor_contrato_processado' => $valorFloat,
            'ppp_id' => $ppp->id ?? 'novo'
        ]);
        
        return $ppp;
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
            // CORRIGIDO: Para "PPPs para Avaliar" - apenas PPPs onde o usuário é gestor, excluindo os que ele criou
            $query->where('gestor_atual_id', Auth::id())
                  ->where('user_id', '!=', Auth::id()); // Excluir PPPs criados pelo próprio usuário
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

            // -- INÍCIO DEBUG --
// foreach ($ppps as $ppp) {
//     $gestor = $ppp->gestorAtual; // relacionamento carregado em with()
//     dd([
//         'ppp_id'           => $ppp->id,
//         'gestor_atual_id'  => $gestor?->id,
//         'gestor_nome'      => $gestor?->name,
//         'gestor_department'=> $gestor?->department,
//         'gestor_managerDN' => $gestor?->manager,
//     ]);
// }
// -- FIM DEBUG --
            
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
            $ppp = PcaPpp::with(['user', 'status', 'gestorAtual'])->findOrFail($id);
            $usuarioLogado = Auth::user();
            
            // CORRIGIDO: Para determinar próximo gestor, considerar o usuário logado se ele for gestor
            $usuarioParaAnalise = $usuarioLogado->hasRole(['dom', 'supex', 'doe']) ? $usuarioLogado : $ppp->user;
            $proximoGestor = $this->hierarquiaService->obterGestorComTratamentoEspecial($usuarioParaAnalise);
            $ehProximoGestor = $proximoGestor && $proximoGestor->id === $usuarioLogado->id;
            
            // CORRIGIDO: Definir se o usuário pode gerenciar este PPP
            $ehGestor = $usuarioLogado->hasRole(['admin', 'daf', 'secretaria']) || 
                       ($usuarioLogado->hasRole('gestor') && $this->hierarquiaService->ehGestorDe($usuarioLogado, $ppp->user));
            
            // Buscar histórico
            $historicos = PppHistorico::where('ppp_id', $ppp->id)
            ->with(['statusAnterior', 'statusAtual', 'usuario'])
            ->orderBy('created_at', 'desc')
            ->get();
    
            // Lógica de navegação para secretária
            $navegacao = null;
            if ($usuarioLogado->hasRole('secretaria')) {
                $navegacao = $this->obterNavegacaoSecretaria($ppp->id);
            }
    
            // Registrar visualização se for gestor
            if ($ppp->gestor_atual_id === $usuarioLogado->id && $ppp->status_id === 2) {
                $ppp->update(['status_id' => 3]); // em_avaliacao
                $this->historicoService->registrarEmAvaliacao($ppp);
            }
            
            // NOVO: Registrar quando usuário abre PPP para correção
            if ($ppp->gestor_atual_id === $usuarioLogado->id && $ppp->status_id === 4) {
                $ppp->update(['status_id' => 5]); // em_correcao
                $this->historicoService->registrarCorrecaoIniciada($ppp);
            }
            
            // dd([
            //     'ppp_id' => $ppp->id,
            //     'ppp_status' => $ppp->status_id,
            //     'ppp_criador' => $ppp->user->name ?? 'N/A',
            //     'ppp_criador_department' => $ppp->user->department ?? 'N/A',
            //     'usuario_logado' => $usuarioLogado->name,
            //     'usuario_department' => $usuarioLogado->department ?? 'N/A',
            //     'usuario_roles' => $usuarioLogado->roles->pluck('name')->toArray(), // CORRIGIDO: usar 'name' em vez de 'slug'
            //     'proximoGestor' => $proximoGestor ? $proximoGestor->name : 'null',
            //     'proximoGestor_roles' => $proximoGestor ? $proximoGestor->roles->pluck('name')->toArray() : [],
            //     'ehProximoGestor' => $ehProximoGestor,
            //     'ehGestor' => $ehGestor,
            //     'navegacao' => $navegacao
            // ]);
            
            return view('ppp.show', compact('ppp', 'historicos', 'navegacao', 'ehProximoGestor', 'ehGestor'));
        } catch (\Exception $e) {
            Log::error('Erro ao visualizar PPP: ' . $e->getMessage());
            return redirect()->route('ppp.index')->with('error', 'Erro ao carregar PPP.');
        }
    }
    
    /**
    * Obtém informações de navegação para a secretária
    */
    private function obterNavegacaoSecretaria($pppAtualId)
    {
        // Buscar todos os PPPs que a secretária pode visualizar (aprovados pelo DAF)
        $pppsSecretaria = PcaPpp::where('status_id', 6) // aprovado_final
            ->orderBy('id')
            ->pluck('id')
            ->toArray();
        
        $posicaoAtual = array_search($pppAtualId, $pppsSecretaria);
        
        if ($posicaoAtual === false) {
            return null;
        }
        
        return [
            'anterior' => $posicaoAtual > 0 ? $pppsSecretaria[$posicaoAtual - 1] : null,
            'proximo' => $posicaoAtual < count($pppsSecretaria) - 1 ? $pppsSecretaria[$posicaoAtual + 1] : null,
            'atual' => $posicaoAtual + 1,
            'total' => count($pppsSecretaria)
        ];
    }
    
    /**
    * Método específico para secretária incluir PPP na tabela PCA
    */
    public function incluirNaPca($id)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);
            $usuarioLogado = Auth::user();
            
            // Verificar se é secretária
            if (!$usuarioLogado->hasRole('secretaria')) {
                return redirect()->back()->with('error', 'Acesso negado. Apenas a secretária pode incluir PPPs na tabela PCA.');
            }
            
            // Verificar se PPP está no status correto
            if ($ppp->status_id !== 6) { // aprovado_final
                return redirect()->back()->with('error', 'PPP deve estar com status "Aprovado Final" para ser incluído na tabela PCA.');
            }
            
            $comentario = request('comentario');
            
            // Atualizar status para aprovado_direx
            $ppp->update([
                'status_id' => 8, // aprovado_direx
                'gestor_atual_id' => $usuarioLogado->id
            ]);
            
            // Registrar no histórico
            $this->historicoService->registrarAcao(
                $ppp,
                'incluido_pca',
                $comentario ?? 'PPP incluído na tabela PCA pela secretária',
                6, // Status anterior: aprovado_final
                8  // Status atual: aprovado_direx
            );
            
            return redirect()->route('ppp.index')
                ->with('success', 'PPP incluído na tabela PCA com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao incluir PPP na PCA: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao incluir PPP na tabela PCA.');
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
        // Se o PPP ainda está em rascunho (status 1), manter comportamento de criação
        if ($ppp->status_id == 1) {
            $edicao = false;
            $isCreating = true;
        } else {
            $edicao = true;
            $isCreating = false;
        }

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
    $ppp = PcaPpp::findOrFail($id);

    if ($ppp->user_id !== Auth::id()) {
        abort(403, 'Você não tem permissão.');
    }

    try {
        // 🔥 Aqui só delegamos ao service:
        $this->pppService->enviarParaAprovacao(
            $ppp,
            $request->input('justificativa')
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'PPP enviado para aprovação com sucesso!'
            ]);
        }

        return redirect()
            ->route('ppp.index')
            ->with('success', 'PPP enviado para aprovação com sucesso!');
    } catch (\Throwable $e) {
        Log::error('Erro ao enviar PPP: '.$e->getMessage(), ['ppp_id' => $id]);
        return back()->withErrors(['msg' => 'Erro: ' . $e->getMessage()]);
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
        
        if (!in_array($ppp->status_id, [3])) { // 3 = em_avaliacao
            return redirect()->back()->with('error', 'Este PPP não está disponível para aprovação.');
        }
        
        if ($ppp->gestor_atual_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Você não é o gestor responsável por este PPP.');
        }
        
        try {
            $resultado = $pppService->enviarParaAprovacao($ppp, $request->input('comentario'));
            
            if ($resultado) return redirect()->route('ppp.index')->with('success', 'PPP aprovado com sucesso!');
            
            return redirect()->back()->with('error', 'Erro ao aprovar o PPP.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao aprovar PPP: ' . $e->getMessage());
        }
    }
    
    // /**
    // * Processa o envio para aprovação internamente
    // */
    // private function processarEnvioAprovacao(PcaPpp $ppp, Request $request): array
    // {
    //     try {
    //         Log::info('🔄 processarEnvioAprovacao() - Iniciando processamento interno', [
    //             'ppp_id' => $ppp->id,
    //             'status_atual' => $ppp->status_id,
    //             'gestor_atual' => $ppp->gestor_atual_id,
    //             'user_solicitante' => Auth::id()
    //         ]);
            
    //         // ✅ ALTERAÇÃO: Usar HierarquiaService em vez da lógica duplicada
    //         $proximoGestor = $this->hierarquiaService->obterProximoGestor(Auth::user());
            
    //         Log::info('🔍 Resultado da busca por próximo gestor', [
    //             'proximo_gestor_encontrado' => $proximoGestor ? true : false,
    //             'proximo_gestor_id' => $proximoGestor ? $proximoGestor->id : null,
    //             'proximo_gestor_nome' => $proximoGestor ? $proximoGestor->name : null
    //         ]);
            
    //         if (!$proximoGestor) {
    //             Log::error('❌ Próximo gestor não encontrado', [
    //                 'ppp_id' => $ppp->id,
    //                 'user_id' => Auth::id()
    //             ]);
    //             return [
    //                 'success' => false,
    //                 'message' => 'Não foi possível identificar o próximo gestor.'
    //             ];
    //         }
            
    //         Log::info('📝 Atualizando status do PPP', [
    //             'ppp_id' => $ppp->id,
    //             'status_de' => $ppp->status_id,
    //             'status_para' => 2,
    //             'gestor_de' => $ppp->gestor_atual_id,
    //             'gestor_para' => $proximoGestor->id
    //         ]);
            
    //         //dd(HierarquiaService->extrairSiglaAreaGestor($proximoGestor));
            
    //         $ppp->update([
    //             'status_id' => 2, // aguardando_aprovacao
    //             'gestor_atual_id' => $proximoGestor->id,            
    //         ]);
            
    //         Log::info('✅ Status do PPP atualizado', [
    //             'ppp_id' => $ppp->id,
    //             'novo_status' => $ppp->fresh()->status_id,
    //             'novo_gestor' => $ppp->fresh()->gestor_atual_id
    //         ]);
            
    //         // Registrar no histórico
    //         $this->historicoService->registrarEnvioAprovacao(
    //             $ppp,
    //             'PPP enviado para aprovação automaticamente após criação'
    //         );
            
    //         Log::info('📋 Histórico registrado com sucesso', [
    //             'ppp_id' => $ppp->id
    //         ]);
            
    //         Log::info('✅ processarEnvioAprovacao() - Concluído com sucesso', [
    //             'ppp_id' => $ppp->id,
    //             'status_final' => $ppp->fresh()->status_id,
    //             'gestor_final' => $ppp->fresh()->gestor_atual_id
    //         ]);
            
    //         return [
    //             'success' => true,
    //             'message' => 'PPP enviado para aprovação com sucesso!'
    //         ];
            
    //     } catch (\Throwable $ex) {
    //         Log::error('💥 ERRO CRÍTICO em processarEnvioAprovacao()', [
    //             'ppp_id' => $ppp->id,
    //             'exception_message' => $ex->getMessage(),
    //             'exception_file' => $ex->getFile(),
    //             'exception_line' => $ex->getLine(),
    //             'stack_trace' => $ex->getTraceAsString()
    //         ]);
            
    //         return [
    //             'success' => false,
    //             'message' => $ex->getMessage()
    //         ];
    //     }
    // }
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

    /**
     * NOVOS MÉTODOS PARA FLUXO DIREX E CONSELHO
     */

    /**
     * Inicia reunião da DIREX (Secretária)
     */
    public function iniciarReuniaoDirectx(Request $request)
    {
        try {
            $usuarioLogado = Auth::user();
            
            // Verificar se é secretária
            if (!$usuarioLogado->hasRole('secretaria')) {
                return redirect()->back()->with('error', 'Acesso negado. Apenas a secretária pode iniciar reunião da DIREX.');
            }
            
            // Verificar se há PPPs aguardando DIREX
            $pppsAguardandoDirectx = PcaPpp::where('status_id', 8) // aguardando_direx
                ->orderBy('id')
                ->get();
            
            if ($pppsAguardandoDirectx->isEmpty()) {
                return redirect()->back()->with('error', 'Não há PPPs aguardando avaliação da DIREX.');
            }
            
            // Registrar início da reunião no histórico da secretária
            $this->historicoService->registrarReuniaoDirectxIniciada(
                $pppsAguardandoDirectx->first(),
                'Reunião da DIREX iniciada pela secretária'
            );
            
            // Redirecionar para o primeiro PPP da lista
            $primeiroPpp = $pppsAguardandoDirectx->first();
            
            return redirect()->route('ppp.show', $primeiroPpp->id)
                ->with('success', 'Reunião da DIREX iniciada! Avaliando PPP: ' . $primeiroPpp->nome_item)
                ->with('reuniao_direx_ativa', true);
                
        } catch (\Exception $e) {
            Log::error('Erro ao iniciar reunião DIREX: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao iniciar reunião da DIREX.');
        }
    }
    
    /**
     * Navega para próximo PPP durante reunião DIREX
     */
    public function proximoPppDirectx($id)
    {
        try {
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return redirect()->back()->with('error', 'Acesso negado.');
            }
            
            $pppsDirectx = PcaPpp::whereIn('status_id', [8, 9, 10]) // aguardando_direx, direx_avaliando, direx_editado
                ->orderBy('id')
                ->pluck('id')
                ->toArray();
            
            $posicaoAtual = array_search($id, $pppsDirectx);
            
            if ($posicaoAtual === false || $posicaoAtual >= count($pppsDirectx) - 1) {
                return redirect()->back()->with('info', 'Este é o último PPP da reunião.');
            }
            
            $proximoId = $pppsDirectx[$posicaoAtual + 1];
            
            return redirect()->route('ppp.show', $proximoId)
                ->with('reuniao_direx_ativa', true);
                
        } catch (\Exception $e) {
            Log::error('Erro ao navegar para próximo PPP: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao navegar.');
        }
    }
    
    /**
     * Navega para PPP anterior durante reunião DIREX
     */
    public function anteriorPppDirectx($id)
    {
        try {
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return redirect()->back()->with('error', 'Acesso negado.');
            }
            
            $pppsDirectx = PcaPpp::whereIn('status_id', [8, 9, 10]) // aguardando_direx, direx_avaliando, direx_editado
                ->orderBy('id')
                ->pluck('id')
                ->toArray();
            
            $posicaoAtual = array_search($id, $pppsDirectx);
            
            if ($posicaoAtual === false || $posicaoAtual <= 0) {
                return redirect()->back()->with('info', 'Este é o primeiro PPP da reunião.');
            }
            
            $anteriorId = $pppsDirectx[$posicaoAtual - 1];
            
            return redirect()->route('ppp.show', $anteriorId)
                ->with('reuniao_direx_ativa', true);
                
        } catch (\Exception $e) {
            Log::error('Erro ao navegar para PPP anterior: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao navegar.');
        }
    }
    
    /**
     * Edita PPP durante reunião DIREX
     */
    public function editarDuranteDirectx($id)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return redirect()->back()->with('error', 'Acesso negado.');
            }
            
            // Alterar status para direx_editado
            $statusAnterior = $ppp->status_id;
            $ppp->update(['status_id' => 10]); // direx_editado
            
            // Registrar no histórico
            $this->historicoService->registrarDirectxEditado(
                $ppp,
                'PPP editado durante reunião da DIREX',
                $statusAnterior,
                10
            );
            
            return redirect()->route('ppp.edit', $id)
                ->with('success', 'PPP marcado como editado pela DIREX.')
                ->with('reuniao_direx_ativa', true);
                
        } catch (\Exception $e) {
            Log::error('Erro ao editar PPP durante DIREX: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao editar PPP.');
        }
    }
    
    /**
     * Inclui PPP na tabela PCA durante reunião DIREX
     */
    public function incluirNaPcaDirectx($id)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return redirect()->back()->with('error', 'Acesso negado.');
            }
            
            // Verificar se PPP está no status correto
            if (!in_array($ppp->status_id, [8, 9, 10])) { // aguardando_direx, direx_avaliando, direx_editado
                return redirect()->back()->with('error', 'PPP não está disponível para inclusão na PCA.');
            }
            
            $statusAnterior = $ppp->status_id;
            
            // Atualizar status para aguardando_conselho
            $ppp->update([
                'status_id' => 11, // aguardando_conselho
                'gestor_atual_id' => $usuarioLogado->id
            ]);
            
            // Registrar no histórico
            $this->historicoService->registrarInclusaoPca(
                $ppp,
                'PPP incluído na tabela PCA durante reunião da DIREX',
                $statusAnterior,
                11
            );
            
            return redirect()->back()
                ->with('success', 'PPP incluído na tabela PCA com sucesso!')
                ->with('reuniao_direx_ativa', true);
                
        } catch (\Exception $e) {
            Log::error('Erro ao incluir PPP na PCA: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao incluir PPP na tabela PCA.');
        }
    }
    
    /**
     * Encerra reunião da DIREX
     */
    public function encerrarReuniaoDirectx()
    {
        try {
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return redirect()->back()->with('error', 'Acesso negado.');
            }
            
            // Verificar se ainda há PPPs pendentes
            $pppsAguardandoDirectx = PcaPpp::where('status_id', 8)->count(); // aguardando_direx
            
            if ($pppsAguardandoDirectx > 0) {
                return redirect()->back()->with('warning', 'Ainda há PPPs aguardando avaliação da DIREX.');
            }
            
            // Registrar encerramento no histórico
            $ultimoPpp = PcaPpp::whereIn('status_id', [9, 10, 11])
                ->orderBy('updated_at', 'desc')
                ->first();
            
            if ($ultimoPpp) {
                $this->historicoService->registrarReuniaoDirectxEncerrada(
                    $ultimoPpp,
                    'Reunião da DIREX encerrada pela secretária'
                );
            }
            
            return redirect()->route('ppp.index')
                ->with('success', 'Reunião da DIREX encerrada com sucesso!')
                ->with('reuniao_direx_encerrada', true);
                
        } catch (\Exception $e) {
            Log::error('Erro ao encerrar reunião DIREX: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao encerrar reunião.');
        }
    }
    
    /**
     * Gera relatório Excel dos PPPs aprovados
     */
    public function gerarExcel()
    {
        try {
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return redirect()->back()->with('error', 'Acesso negado.');
            }
            
            // Buscar PPPs aguardando conselho
            $ppps = PcaPpp::where('status_id', 11) // aguardando_conselho
                ->with(['user', 'status'])
                ->orderBy('id')
                ->get();
            
            if ($ppps->isEmpty()) {
                return redirect()->back()->with('error', 'Não há PPPs para gerar relatório Excel.');
            }
            
            // Registrar geração no histórico
            $this->historicoService->registrarExcelGerado(
                $usuarioLogado->id,
                'Relatório Excel gerado pela secretária'
            );
            
            // Gerar Excel usando Maatwebsite\Excel
            $fileName = 'PCA_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            //return Excel::download(new PcaExport($ppps), $fileName);
                
        } catch (\Exception $e) {
            Log::error('Erro ao gerar Excel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao gerar relatório Excel.');
        }
    }
    
    /**
     * Gera relatório PDF dos PPPs aprovados
     */
    public function gerarPdf()
    {
        try {
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return redirect()->back()->with('error', 'Acesso negado.');
            }
            
            // Buscar PPPs aguardando conselho
            $ppps = PcaPpp::where('status_id', 11) // aguardando_conselho
                ->with(['user', 'status'])
                ->orderBy('id')
                ->get();
            
            if ($ppps->isEmpty()) {
                return redirect()->back()->with('error', 'Não há PPPs para gerar relatório PDF.');
            }
            
            // Registrar geração no histórico
            $this->historicoService->registrarPdfGerado(
                $usuarioLogado->id,
                'Relatório PDF gerado pela secretária'
            );
            
            // Gerar PDF usando DomPDF
            $pdf = PDF::loadView('ppp.relatorios.pca-pdf', compact('ppps'));
            $fileName = 'PCA_' . date('Y-m-d_H-i-s') . '.pdf';
            
            return $pdf->download($fileName);
                
        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao gerar relatório PDF.');
        }
    }
    
    /**
     * Processa aprovação ou reprovação do Conselho
     */
    public function processarConselho(Request $request)
    {
        try {
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return redirect()->back()->with('error', 'Acesso negado.');
            }
            
            $request->validate([
                'decisao' => 'required|in:aprovar,reprovar',
                'comentario' => 'nullable|string|max:1000'
            ]);
            
            $decisao = $request->input('decisao');
            $comentario = $request->input('comentario', 'Decisão do Conselho registrada pela secretária');
            
            // Buscar todos os PPPs aguardando conselho
            $ppps = PcaPpp::where('status_id', 11)->get(); // aguardando_conselho
            
            if ($ppps->isEmpty()) {
                return redirect()->back()->with('error', 'Não há PPPs aguardando decisão do Conselho.');
            }
            
            $novoStatus = ($decisao === 'aprovar') ? 12 : 13; // conselho_aprovado : conselho_reprovado
            $acao = ($decisao === 'aprovar') ? 'conselho_aprovado' : 'conselho_reprovado';
            
            // Atualizar todos os PPPs
            foreach ($ppps as $ppp) {
                $ppp->update([
                    'status_id' => $novoStatus,
                    'gestor_atual_id' => $usuarioLogado->id
                ]);
                
                // ✅ CORREÇÃO: Registrar no histórico individualmente
                if ($decisao === 'aprovar') {
                    $this->historicoService->registrarAcao(
                        $ppp,
                        'conselho_aprovado',
                        $comentario,
                        11, // status anterior
                        $novoStatus, // status atual
                        $usuarioLogado->id
                    );
                } else {
                    $this->historicoService->registrarAcao(
                        $ppp,
                        'conselho_reprovado',
                        $comentario,
                        11, // status anterior
                        $novoStatus, // status atual
                        $usuarioLogado->id
                    );
                }
            }
            
            $mensagem = ($decisao === 'aprovar') 
                ? 'Conselho aprovou todos os PPPs com sucesso!' 
                : 'Conselho reprovou todos os PPPs.';
            
            return redirect()->route('ppp.index')
                ->with('success', $mensagem)
                ->with('conselho_processado', true);
                
        } catch (\Exception $e) {
            Log::error('Erro ao processar decisão do Conselho: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao processar decisão do Conselho.');
        }
    }
    
    /**
     * Obtém histórico específico da secretária
     */
    public function historicoSecretaria()
    {
        try {
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return redirect()->back()->with('error', 'Acesso negado.');
            }
            
            // Buscar histórico de ações da secretária
            $historicos = PppHistorico::whereIn('acao', [
                'reuniao_direx_iniciada',
                'reuniao_direx_encerrada',
                'excel_gerado',
                'pdf_gerado',
                'conselho_aprovado',
                'conselho_reprovado'
            ])
            ->with(['ppp', 'usuario'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
            return view('ppp.partials.historico-secretaria-modal', compact('historicos'));
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar histórico da secretária: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao carregar histórico'], 500);
        }
    }
    
    /**
     * Verifica se há reunião DIREX ativa
     */
    public function verificarReuniaoDirectxAtiva()
    {
        try {
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return response()->json(['ativa' => false]);
            }
            
            // Verificar se há PPPs em avaliação pela DIREX
            $temReuniaoAtiva = $this->historicoService->temReuniaoDirectxAtiva();
            
            return response()->json(['ativa' => $temReuniaoAtiva]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao verificar reunião DIREX: ' . $e->getMessage());
            return response()->json(['ativa' => false]);
        }
    }
    
    /**
     * Obtém PPPs aguardando DIREX para a secretária
     */
    public function obterPppsAguardandoDirectx()
    {
        try {
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }
            
            $ppps = $this->historicoService->obterPppsAguardandoDirectx();
            
            return response()->json([
                'success' => true,
                'ppps' => $ppps,
                'total' => $ppps->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao obter PPPs aguardando DIREX: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }
    
    /**
     * Obtém PPPs aguardando Conselho para a secretária
     */
    public function obterPppsAguardandoConselho()
    {
        try {
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }
            
            $ppps = $this->historicoService->obterPppsAguardandoConselho();
            
            return response()->json([
                'success' => true,
                'ppps' => $ppps,
                'total' => $ppps->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao obter PPPs aguardando Conselho: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }
    
    /**
     * Pausar reunião DIREX
     */
    public function pausarReuniaoDirectx(Request $request)
    {
        try {
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
            }
            
            // Salvar estado da reunião na sessão
            session([
                'reuniao_direx_pausada' => true,
                'ppp_atual_id' => $request->ppp_atual_id,
                'reuniao_direx_ativa' => false
            ]);
            
            // Registrar no histórico
            $this->historicoService->registrarReuniaoDirectxPausada($usuarioLogado->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Reunião pausada com sucesso.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao pausar reunião DIREX: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno.'], 500);
        }
    }
    
    /**
     * Atualizar status do PPP durante DIREX
     */
    public function atualizarStatusDirectx(Request $request)
    {
        try {
            $usuarioLogado = Auth::user();
            
            if (!$usuarioLogado->hasRole('secretaria')) {
                return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
            }
            
            $request->validate([
                'ppp_id' => 'required|exists:pca_ppps,id',
                'status' => 'required|integer|min:1|max:13'
            ]);
            
            $ppp = PcaPpp::findOrFail($request->ppp_id);
            $statusAnterior = $ppp->status_id;
            
            $ppp->update(['status_id' => $request->status]);
            
            // Registrar no histórico
            $this->historicoService->registrarMudancaStatus(
                $ppp,
                'Status atualizado durante reunião DIREX',
                $statusAnterior,
                $request->status,
                $usuarioLogado->id
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso.',
                'novo_status' => $request->status
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status DIREX: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno.'], 500);
        }
    }

    /**
     * Lista apenas os PPPs criados pelo usuário logado
     */
    public function meusPpps(Request $request)
    {
        try {
            Log::info('DEBUG Meus PPPs - Usuário atual', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'N/A'
            ]);
            
            $query = PcaPpp::query();
            
            // Filtrar apenas PPPs criados pelo usuário logado
            $query->where('user_id', Auth::id());

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
            
            return view('ppp.meus', compact('ppps'));
            
        } catch (\Exception $e) {
            Log::error('Erro ao listar Meus PPPs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar a lista de Meus PPPs.');
        }
    }

    /**
     * Reenvia PPP após correção
     */
    public function reenviarAposCorrecao(Request $request, PcaPpp $ppp)
    {
        // Verificar se o usuário é o responsável pela correção
        if ($ppp->gestor_atual_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Você não tem permissão para reenviar este PPP.');
        }
        
        // Verificar se o PPP está no status correto
        if ($ppp->status_id !== 5) { // em_correcao
            return redirect()->back()->with('error', 'PPP não está no status adequado para reenvio.');
        }
        
        try {
            $this->pppService->reenviarAposCorrecao(
                $ppp,
                $request->input('comentario')
            );
            
            return redirect()->route('ppp.index')
                ->with('success', 'PPP corrigido e reenviado para aprovação com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao reenviar PPP: ' . $e->getMessage());
        }
    }

    public function dashboard()
    {
        $userId = Auth::id();

    $pppsParaAvaliar = $this->pppService->contarParaAvaliar($userId);
    $pppsMeus = $this->pppService->contarMeus($userId);

    $usuario = Auth::user();

    // Recuperar data da última atualização via GitHub com cache de 1 hora
    $ultimaAtualizacao = Cache::remember('ultima_atualizacao_github', 3600, function () {
        $response = Http::withToken(env('GITHUB_TOKEN'))
            ->get('https://api.github.com/repos/PauloParanacidade/PCA/commits');

        return $response->json()[0]['commit']['committer']['date'] ?? null;
    });

    return view('dashboard', compact('pppsParaAvaliar', 'pppsMeus', 'usuario', 'ultimaAtualizacao'));

    }

}



            
            