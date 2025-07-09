<?php


namespace App\Http\Controllers;

use App\Http\Requests\StorePppRequest;
use App\Models\PcaPpp;
use App\Models\PppHistorico;
use App\Models\PppStatusDinamico;
use App\Models\User;
// ❌ REMOVER: use App\Services\PppStatusService;
use App\Services\PppHistoricoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PppController extends Controller
{
    // ❌ REMOVER: protected $statusService;
    protected $historicoService;
    
    public function __construct(PppHistoricoService $historicoService)
    {
        $this->historicoService = $historicoService;
    }
    
    public function create()
    {
        return view('ppp.form');
    }

    public function store(StorePppRequest $request)
    {
        try {
            //dd($request);
            $manager = Auth::user();
            
            // ✅ NOVA REGRA: Verificar e atribuir papel de gestor automaticamente
            if ($manager) {
                try {
                    $manager->garantirPapelGestor();
                } catch (\Exception $e) {
                    Log::error('Erro ao garantir papel de gestor: ' . $e->getMessage(), [
                        'user_id' => $manager->id,
                        'user_name' => $manager->name
                    ]);
                }
            }
            
            // ✅ CORREÇÃO: Processar valores monetários corretamente
            // Remove R$, espaços e converte formato brasileiro para decimal
            $estimativaLimpa = str_replace(['R$', ' '], '', $request->estimativa_valor);
            // Remove pontos (separadores de milhares) e converte vírgula para ponto decimal
            $estimativaLimpa = str_replace(['.'], '', $estimativaLimpa); // Remove pontos
            $estimativaFloat = floatval(str_replace(',', '.', $estimativaLimpa)); // Converte vírgula para ponto
            
            $valorLimpo = null;
            $valorFloat = null;
            if ($request->filled('valor_contrato_atualizado')) {
                $valorLimpo = str_replace(['R$', ' '], '', $request->valor_contrato_atualizado);
                $valorLimpo = str_replace(['.'], '', $valorLimpo); // Remove pontos
                $valorFloat = floatval(str_replace(',', '.', $valorLimpo)); // Converte vírgula para ponto
            }
            
            // ✅ NOVO: Verificar se é um rascunho (apenas card azul preenchido)
            $isRascunho = $this->isRascunho($request);
            
            $ppp = PcaPpp::create([
                'user_id' => Auth::id(),
                'status_id' => 1,
                'gestor_atual_id' => $manager->id,
                'categoria' => $request->categoria,
                'nome_item' => $request->nome_item,
                'descricao' => $request->descricao,
                'quantidade' => $request->quantidade,
                'justificativa_pedido' => $request->justificativa_pedido,
                'estimativa_valor' => $estimativaFloat ?: 0.01,
                'justificativa_valor' => $request->justificativa_valor,
                'grau_prioridade' => $request->grau_prioridade,
                // Aplicar valores padrão diretamente
                'origem_recurso' => $request->origem_recurso ?: 'PRC',
                'vinculacao_item' => $request->vinculacao_item ?: 'Não',
                'justificativa_vinculacao' => $request->justificativa_vinculacao ?: '.',
                'renov_contrato' => $request->renov_contrato ?: 'Não',
                'valor_contrato_atualizado' => $valorFloat ?: 0.01,
                'num_contrato' => $request->num_contrato ?: '.',
                'mes_vigencia_final' => $request->mes_vigencia_final ?: '.',
                'contrato_prorrogavel' => $request->contrato_prorrogavel ?: 'Não',
                'tem_contrato_vigente' => $request->tem_contrato_vigente ?: 'Não',
                'natureza_objeto' => $request->natureza_objeto ?: '.',
                // Adicionar campos que podem estar faltando
                'dependencia_item' => $request->dependencia_item ?: 'Não',
                'justificativa_dependencia' => $request->justificativa_dependencia ?: '.',
                'cronograma_jan' => $request->cronograma_jan ?: 'Não',
                'cronograma_fev' => $request->cronograma_fev ?: 'Não',
                'cronograma_mar' => $request->cronograma_mar ?: 'Não',
                'cronograma_abr' => $request->cronograma_abr ?: 'Não',
                'cronograma_mai' => $request->cronograma_mai ?: 'Não',
                'cronograma_jun' => $request->cronograma_jun ?: 'Não',
                'cronograma_jul' => $request->cronograma_jul ?: 'Não',
                'cronograma_ago' => $request->cronograma_ago ?: 'Não',
                'cronograma_set' => $request->cronograma_set ?: 'Não',
                'cronograma_out' => $request->cronograma_out ?: 'Não',
                'cronograma_nov' => $request->cronograma_nov ?: 'Não',
                'cronograma_dez' => $request->cronograma_dez ?: 'Não',
            ]);
            
            // Registrar no histórico
            $this->historicoService->registrarCriacao($ppp);
            
            Log::info('PPP criado com sucesso.', ['ppp_id' => $ppp->id]);
            
            // Verificar se é uma requisição AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'PPP criado com sucesso.',
                    'ppp_id' => $ppp->id
                ]);
            }
            
            return redirect()->route('ppp.index')->with('success', 'PPP criado com sucesso.');
            
        } catch (\Throwable $ex) {
            Log::error('Erro ao criar PPP: ' . $ex->getMessage());
            Log::error('Stack trace: ' . $ex->getTraceAsString()); // Para debug
            return back()->withInput()->withErrors(['msg' => 'Erro ao criar PPP: ' . $ex->getMessage()]);
        }
    }

        
        
    public function index(Request $request)
    {
        //dd($request);  
        try {
            // LOG 1: Verificar usuário atual
            Log::info('DEBUG PPP Index - Usuário atual', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? 'N/A'
            ]);
            
            // LOG 2: Total de PPPs no banco (incluindo soft deleted)
            $totalPppsComDeleted = PcaPpp::withTrashed()->count();
            $totalPppsAtivos = PcaPpp::count();
            Log::info('DEBUG PPP Index - Total PPPs no banco', [
                'total_com_deleted' => $totalPppsComDeleted,
                'total_ativos' => $totalPppsAtivos
            ]);
            
            $query = PcaPpp::where(function($q) {
                    $q->where('user_id', Auth::id())
                      ->orWhere('gestor_atual_id', Auth::id());
                })
                ->with([
                    'user', 
                    'status', // ✅ ADICIONAR: Carregar o relacionamento status
                    'gestorAtual',
                    'historicos.usuario'
                ])
                ->orderBy('created_at', 'desc');
        
            // LOG 3: Quantos PPPs passam pelo filtro inicial (user_id ou gestor_atual_id)
            $totalFiltroInicial = clone $query;
            $countFiltroInicial = $totalFiltroInicial->count();
            Log::info('DEBUG PPP Index - PPPs após filtro inicial', [
                'count_filtro_inicial' => $countFiltroInicial,
                'filtros_aplicados' => [
                    'user_id' => Auth::id(),
                    'gestor_atual_id' => Auth::id()
                ]
            ]);
            
            // ✅ CORRIGIR: Filtro deve usar status_id ao invés de status_fluxo
            if ($request->filled('status_id')) {
                $query->where('status_id', $request->status_id);
                Log::info('DEBUG PPP Index - Filtro status_id aplicado', [
                    'status_id' => $request->status_id
                ]);
            }
            
            // Campo area_solicitante foi removido
            
            if ($request->filled('busca')) {
                $busca = $request->busca;
                $query->where(function($q) use ($busca) {
                    $q->where('nome_item', 'like', "%{$busca}%")
                      ->orWhere('descricao', 'like', "%{$busca}%");
                });
                Log::info('DEBUG PPP Index - Filtro busca aplicado', [
                    'busca' => $busca
                ]);
            }
            
            // LOG 4: Quantos PPPs após todos os filtros (antes da paginação)
            $totalAposFiltros = clone $query;
            $countAposFiltros = $totalAposFiltros->count();
            Log::info('DEBUG PPP Index - PPPs após todos os filtros', [
                'count_apos_filtros' => $countAposFiltros
            ]);
            
            // LOG 5: SQL da query para debug
            $sqlQuery = $query->toSql();
            $bindings = $query->getBindings();
            Log::info('DEBUG PPP Index - SQL Query', [
                'sql' => $sqlQuery,
                'bindings' => $bindings
            ]);
            
            $ppps = $query->paginate(10)->withQueryString();
            
            // LOG 6: Resultado final da paginação
            Log::info('DEBUG PPP Index - Resultado final', [
                'total_paginated' => $ppps->total(),
                'current_page' => $ppps->currentPage(),
                'per_page' => $ppps->perPage(),
                'items_na_pagina_atual' => $ppps->count()
            ]);
        
            return view('ppp.index', compact('ppps'));
        } catch (\Exception $e) {
            // dd($e); // ❌ COMENTAR ESTA LINHA
            Log::error('Erro ao listar PPPs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar a lista de PPPs.');
        }
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

            return view('ppp.show', compact('ppp', 'historicos'));
        } catch (\Throwable $ex) {
            Log::error('Erro ao exibir PPP:', [
                'exception' => $ex,
                'ppp_id' => $id,
            ]);
            Log::debug($ex->getTraceAsString());
            return back()->withErrors(['msg' => 'Erro ao exibir o PPP.']);
        }
    }

    public function edit($id)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);
            return view('ppp.form', compact('ppp'));
        } catch (\Throwable $ex) {
            Log::error('Erro ao carregar PPP para edição:', [
                'exception' => $ex,
                'ppp_id' => $id,
            ]);
            Log::debug($ex->getTraceAsString());
            return back()->withErrors(['msg' => 'Erro ao carregar PPP para edição.']);
        }
    }

    public function update(StorePppRequest $request, $id)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);
            $dados = $request->validated();
            
            // ✅ CORREÇÃO: Processar valores monetários dos dados validados
            if (isset($dados['estimativa_valor'])) {
                $estimativaLimpa = str_replace(['R$', ' '], '', $dados['estimativa_valor']);
                $estimativaLimpa = str_replace(['.'], '', $estimativaLimpa);
                $dados['estimativa_valor'] = floatval(str_replace(',', '.', $estimativaLimpa));
            }
            
            if (isset($dados['valor_contrato_atualizado'])) {
                $valorLimpo = str_replace(['R$', ' '], '', $dados['valor_contrato_atualizado']);
                $valorLimpo = str_replace(['.'], '', $valorLimpo);
                $dados['valor_contrato_atualizado'] = floatval(str_replace(',', '.', $valorLimpo));
            }
            
            $statusAnterior = $ppp->status_id;
            $statusNovo = $dados['status_id'] ?? $statusAnterior;
            
            $ppp->update($dados);

            // Registrar histórico se status mudou
            if ($statusAnterior != $statusNovo) {
                PppHistorico::create([
                    'ppp_id'         => $ppp->id,
                    'status_anterior'=> $statusAnterior,
                    'status_atual'   => $statusNovo,
                    'justificativa'  => $request->input('justificativa'),
                    'user_id'        => Auth::id(),
                ]);
                Log::info('Histórico registrado após alteração de status.', [
                    'ppp_id' => $ppp->id,
                    'status_anterior' => $statusAnterior,
                    'status_novo' => $statusNovo,
                ]);
            }

            // Verificar se é uma requisição AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'PPP atualizado com sucesso.',
                    'ppp_id' => $ppp->id
                ]);
            }

            return redirect()->route('ppp.index')->with('success', 'PPP atualizado com sucesso.');
        } catch (\Throwable $ex) {
            Log::error('Erro ao atualizar PPP: ' . $ex->getMessage(), [
                'exception' => $ex,
                'ppp_id' => $id,
            ]);
            
            // Verificar se é uma requisição AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar PPP: ' . $ex->getMessage()
                ], 500);
            }
            
            return back()->withInput()->withErrors(['msg' => 'Erro ao atualizar.']);
        }
    }

    public function destroy($id)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);
    
            // Opcional: verificar se o usuário tem permissão para deletar este PPP
    
            $ppp->delete();
    
            Log::info('PPP excluído com sucesso.', ['ppp_id' => $id]);
    
            return redirect()->route('ppp.index')->with('success', 'PPP excluído com sucesso.');
        } catch (\Throwable $ex) {
            Log::error('Erro ao excluir PPP: ' . $ex->getMessage(), [
                'exception' => $ex,
                'ppp_id' => $id,
            ]);
            Log::debug($ex->getTraceAsString());
            return back()->withErrors(['msg' => 'Erro ao excluir.']);
        }
    }

    private function criarStatusDinamico($ppp, $tipoStatus, $remetenteId = null, $destinatarioId = null, $statusCustom = null)
    {
        // Desativar status dinâmico anterior
        $ppp->statusDinamicos()->update(['ativo' => false]);
        
        if ($statusCustom) {
            // Status customizado (ex: rascunho)
            $statusFormatado = $statusCustom;
            $remetenteSigla = null;
            $destinatarioSigla = null;
        } else {
            // Buscar template do status
            $statusTemplate = \App\Models\PppStatus::where('tipo', $tipoStatus)->first();
            
            if (!$statusTemplate) {
                throw new \Exception("Template de status não encontrado: {$tipoStatus}");
            }
            
            // Obter dados dos usuários
            $remetente = $remetenteId ? User::find($remetenteId) : null;
            $destinatario = $destinatarioId ? User::find($destinatarioId) : null;
            
            // Extrair siglas das áreas
            $remetenteSigla = $remetente ? $this->extrairSiglaArea($remetente) : null;
            $destinatarioSigla = $destinatario ? $this->extrairSiglaAreaGestor($destinatario) : null;
            
            // Substituir placeholders
            $statusFormatado = $statusTemplate->template;
            
            if ($remetente) {
                $remetenteTexto = $remetente->name . ' [' . ($remetenteSigla ?? 'N/A') . ']';
                $statusFormatado = str_replace('[remetente]', $remetenteTexto, $statusFormatado);
            }
            
            if ($destinatario) {
                $destinatarioTexto = $destinatario->name . ' [' . ($destinatarioSigla ?? 'N/A') . ']';
                $statusFormatado = str_replace('[destinatario]', $destinatarioTexto, $statusFormatado);
            }
        }
        // dd($statusFormatado);
        // // Criar novo status dinâmico
        // return \App\Models\PppStatusDinamico::create([
        //     'ppp_id' => $ppp->id,
        //     'status_tipo_id' => $tipoStatus === 'rascunho' ? null : \App\Models\PppStatus::where('tipo', $tipoStatus)->first()->id,
        //     'remetente_nome' => $remetente->name ?? null,
        //     'remetente_sigla' => $remetenteSigla,
        //     'destinatario_nome' => $destinatario->name ?? null,
        //     'destinatario_sigla' => $destinatarioSigla,
        //     'status_formatado' => $statusFormatado,
        //     'ativo' => true,
        // ]);
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
        try {
            $ppp = PcaPpp::findOrFail($id);
            
            if ($ppp->user_id !== Auth::id()) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Você não tem permissão para esta ação.'], 403);
                }
                return back()->withErrors(['msg' => 'Você não tem permissão para esta ação.']);
            }
            
            $proximoGestor = $this->obterProximoGestor(Auth::user());
            
            if (!$proximoGestor) {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Não foi possível identificar o próximo gestor.'], 400);
                }
                return back()->withErrors(['msg' => 'Não foi possível identificar o próximo gestor.']);
            }
            
            $ppp->update([
                'status_id' => 2, // aguardando_aprovacao
                'gestor_atual_id' => $proximoGestor->id,
            ]);
            
            // Registrar no histórico
            $this->historicoService->registrarEnvioAprovacao(
                $ppp, 
                $request->input('justificativa', 'PPP enviado para aprovação')
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'PPP enviado para aprovação com sucesso!',
                    'ppp_id' => $ppp->id
                ]);
            }
            
            return redirect()->route('ppp.index')->with('success', 'PPP enviado para aprovação com sucesso!');
            
        } catch (\Throwable $ex) {
            Log::error('Erro ao enviar PPP para aprovação: ' . $ex->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Erro ao enviar PPP para aprovação.'], 500);
            }
            
            return back()->withErrors(['msg' => 'Erro ao enviar PPP para aprovação.']);
        }
    }

    private function obterProximoGestor($usuario)
    {
        // Extrair o gestor do campo manager (formato LDAP)
        $managerDN = $usuario->manager;
        
        if (!$managerDN) {
            Log::warning('Usuário não possui gestor definido', ['user_id' => $usuario->id]);
            return null;
        }
        
        // Extrair o nome do gestor do Distinguished Name (DN)
        // Formato: CN=Nome do Gestor,OU=Sigla da Área,DC=domain,DC=com
        if (preg_match('/CN=([^,]+),OU=([^,]+)/', $managerDN, $matches)) {
            $nomeGestor = trim($matches[1]);
            $siglaAreaGestor = trim($matches[2]);
            
            // Buscar o gestor pelo nome
            $gestor = User::where('name', 'like', "%{$nomeGestor}%")
                         ->where('active', true)
                         ->first();
            
            if ($gestor) {
                Log::info('Gestor encontrado na hierarquia', [
                    'usuario_id' => $usuario->id,
                    'gestor_id' => $gestor->id,
                    'gestor_nome' => $gestor->name,
                    'area_gestor' => $siglaAreaGestor
                ]);
                return $gestor;
            }
            
            Log::warning('Gestor não encontrado na base de dados', [
                'user_id' => $usuario->id,
                'nome_gestor_extraido' => $nomeGestor,
                'area_gestor_extraida' => $siglaAreaGestor
            ]);
        } else {
            Log::warning('Formato do manager DN não reconhecido', [
                'user_id' => $usuario->id,
                'manager_dn' => $managerDN
            ]);
        }
        
        return null;
    }

    public function aprovar(Request $request, PcaPpp $ppp, \App\Services\PppService $pppService)
    {
        // Verificar se o usuário tem permissão
        if (!auth()->user()->hasAnyRole(['admin', 'daf', 'gestor'])) {
            return redirect()->back()->with('error', 'Você não tem permissão para aprovar PPPs.');
        }
    
        // Verificar se o PPP está aguardando aprovação
        if ($ppp->status_id !== 2) { // 2 = aguardando_aprovacao
            return redirect()->back()->with('error', 'Este PPP não está aguardando aprovação.');
        }
    
        // Verificar se o usuário é o gestor responsável
        if ($ppp->gestor_atual_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Você não é o gestor responsável por este PPP.');
        }
    
        // Validar comentário se fornecido
        $request->validate([
            'comentario' => 'nullable|string|max:1000'
        ]);
    
        try {
            // Usar o PppService para aprovar
            $resultado = $pppService->aprovarPpp($ppp, $request->input('comentario'));
            
            if ($resultado) {
                return redirect()->route('ppp.index')->with('success', 'PPP aprovado com sucesso!');
            } else {
                return redirect()->back()->with('error', 'Erro ao aprovar o PPP.');
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao aprovar PPP: ' . $e->getMessage());
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


    
    
