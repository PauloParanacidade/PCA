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
        $manager = Auth::user();

        // ✅ Verificar e atribuir papel de gestor automaticamente
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

        // ✅ Obter o próximo gestor
        $proximoGestor = $this->obterProximoGestor($manager);

        Log::info('🔍 Gestor identificado na criação', [
            'user_id' => Auth::id(),
            'proximo_gestor_id' => $proximoGestor?->id,
            'proximo_gestor_nome' => $proximoGestor?->name,
        ]);

        // ✅ Processar valores monetários
        $estimativaFloat = floatval(str_replace(',', '.', str_replace(['R$', '.', ' '], '', $request->estimativa_valor)));

        $valorFloat = null;
        if ($request->filled('valor_contrato_atualizado')) {
            $valorFloat = floatval(str_replace(',', '.', str_replace(['R$', '.', ' '], '', $request->valor_contrato_atualizado)));
        }

        // ✅ Criar PPP
        $ppp = PcaPpp::create([
            'user_id' => Auth::id(),
            'gestor_atual_id' => $proximoGestor?->id,
            'status_id' => 1, // rascunho
            'nome_item' => $request->nome_item,
            'descricao_item' => $request->descricao_item,
            'categoria' => $request->categoria,
            'quantidade' => $request->quantidade,
            'unidade_medida' => $request->unidade_medida,
            'valor_total_estimado' => $estimativaFloat,
            'grau_prioridade' => $request->grau_prioridade,
            'area_solicitante' => $request->area_solicitante,
            'justificativa_contratacao' => $request->justificativa_contratacao,
            'origem_recurso' => $request->origem_recurso ?: 'PRC',
            'valor_contrato_atualizado' => $valorFloat ?: 0.01,
            'tem_contrato_vigente' => $request->tem_contrato_vigente ?: 'Não',
            'contrato_prorrogavel' => $request->contrato_prorrogavel ?: 'Não',
            'renov_contrato' => $request->renov_contrato ?: 'Não',
            'num_contrato' => $request->num_contrato ?: '.',
            'mes_vigencia_final' => $request->mes_vigencia_final ?: '.',
            'natureza_objeto' => $request->natureza_objeto ?: '.',
            'vinculacao_item' => $request->vinculacao_item ?: 'Não',
            'justificativa_vinculacao' => $request->justificativa_vinculacao ?: '.',
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

        // ✅ Registrar histórico
        $this->historicoService->registrarCriacao($ppp);

        Log::info('✅ PPP criado com sucesso', [
            'ppp_id' => $ppp->id,
            'status_atual' => $ppp->status_id,
            'gestor_atual_id' => $ppp->gestor_atual_id,
        ]);

        // ✅ Resposta
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'PPP criado com sucesso.',
                'ppp_id' => $ppp->id,
                'actionValue' => 'aguardando_aprovacao'
            ]);
        }

        return redirect()->route('ppp.index')->with('success', 'PPP criado com sucesso.');
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
        //dd($request);
        try {
            // 🔎 Novo log detalhado sobre a ação
            Log::info('🛠️ Ação detectada no update()', [
                'request_input_acao' => $request->input('acao'),
                'request_get_acao' => request('acao'),
                'request_method' => $request->method(),
                'request_full_data' => $request->all()
            ]);

            $ppp = PcaPpp::findOrFail($id);
            $dados = $request->validated();
            //dd($dados);
            Log::info('🔍 Verificando se ação é "enviar"', [
                'acao_recebida' => $request->input('acao'),
                'condicao_resultado' => $request->input('acao') === 'enviar'
            ]);

//dd($request->input('acao'));
                
            
            // Verificar se a ação é para enviar para aprovação
if ($request->input('acao') === 'enviar_aprovacao') {
    Log::info('Enviando PPP para aprovação AÇÃO = ENVIAR_APROVAÇÃO - ESTÁ OK ATÉ AQUI', ['ppp_id' => $ppp->id, 'user_id' => auth()->id()]);
    
    try {
        $resultado = $this->processarEnvioAprovacao($ppp, $request);
        
        if (!$resultado['success']) {
            Log::error('Erro ao processar envio para aprovação', [
                'ppp_id' => $ppp->id,
                'erro' => $resultado['message']
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message']
                ]);
            }
            
            return redirect()->back()->withErrors(['erro' => $resultado['message']]);
        }
        
        Log::info('PPP enviado para aprovação com sucesso', ['ppp_id' => $ppp->id]);
    } catch (\Exception $e) {
        Log::error('Exceção ao enviar PPP para aprovação', [
            'ppp_id' => $ppp->id,
            'erro' => $e->getMessage()
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao enviar para aprovação'
            ]);
        }
        
        return redirect()->back()->withErrors(['erro' => 'Erro interno ao enviar para aprovação']);
    }
}
            
            
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
            
            Log::info('Conteúdo do update PPP', [
                'dados' => $dados,
                'request_acao' => $request->input('acao'),
                'esperado_status' => $dados['status_id'] ?? 'N/A',
            ]);
            
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
        
    public function index(Request $request)
{
    try {
        Log::info('DEBUG PPP Index - Usuário atual', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'N/A'
        ]);
        
        $query = PcaPpp::query();
        
        // Aplicar filtro baseado no tipo de visualização
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
                    $query->where(function($q) {
                        $q->where('user_id', Auth::id())
                          ->orWhere('gestor_atual_id', Auth::id());
                    });
            }
        } else {
            // Comportamento padrão: mostrar PPPs criados pelo usuário OU onde ele é gestor
            $query->where(function($q) {
                $q->where('user_id', Auth::id())
                  ->orWhere('gestor_atual_id', Auth::id());
            });
        }
        
        $query->with([
            'user', 
            'status',
            'gestorAtual',
            'historicos.usuario'
        ])->orderBy('created_at', 'desc');
        
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
        
        return view('ppp.index', compact('ppps'));
        
    } catch (\Exception $e) {
        Log::error('Erro ao listar PPPs: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Erro ao carregar a lista de PPPs.');
    }
}

    public function show($id)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);
            //dd($ppp);
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
            $edicao = true;
            return view('ppp.form', compact('ppp','edicao'));
        } catch (\Throwable $ex) {
            Log::error('Erro ao carregar PPP para edição:', [
                'exception' => $ex,
                'ppp_id' => $id,
            ]);
            Log::debug($ex->getTraceAsString());
            return back()->withErrors(['msg' => 'Erro ao carregar PPP para edição.']);
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
            
            $proximoGestor = $this->obterProximoGestor(Auth::user());
            
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


            //trecho comentado em função da resposta dada pela IA.
            // $this->historicoService->registrarEnvioAprovacao(
            //     $ppp, 
            //     $request->input('justificativa', 'PPP enviado para aprovação')
            // );

            //trecho proposto pela IA. Se não funcionar apropriadamente deverá ser excluído e descomentar o trecho acima
            $this->historicoService->registrarEnvioAprovacao($ppp, $justificativa);

            Log::info('✅ Histórico registrado com sucesso');
            
            //trecho comentado em função da resposta dada pela IA.
            // if ($request->ajax()) {
            //     return response()->json([
            //         'success' => true, 
            //         'message' => 'PPP enviado para aprovação com sucesso!',
            //         'ppp_id' => $ppp->id
            //     ]);
            // }

            //trecho proposto pela IA. Se não funcionar apropriadamente deverá ser excluído e descomentar o trecho acima
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

            //trecho comentado em função da resposta dada pela IA.
            // Log::error('Erro ao enviar PPP para aprovação: ' . $ex->getMessage());
            
            // if ($request->ajax()) {
            //     return response()->json(['success' => false, 'message' => 'Erro ao enviar PPP para aprovação.'], 500);
            // }
            
            // return back()->withErrors(['msg' => 'Erro ao enviar PPP para aprovação.']);

            //trecho proposto pela IA. Se não funcionar apropriadamente deverá ser excluído e descomentar o trecho acima
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

    private function obterProximoGestor($usuario)
    {
        Log::info('🔍 obterProximoGestor() - INICIANDO', [
            'user_id' => $usuario->id,
            'user_name' => $usuario->name,
            'user_manager' => $usuario->manager ?? 'N/A'
        ]);
        
        // Extrair o gestor do campo manager (formato LDAP)
        $managerDN = $usuario->manager;
        
        if (!$managerDN) {
            Log::warning('❌ Usuário não possui gestor definido', ['user_id' => $usuario->id]);
            return null;
        }
        
        Log::info('🔍 Manager DN encontrado', ['manager_dn' => $managerDN]);
        
        // Extrair o nome do gestor do Distinguished Name (DN)
        // Formato: CN=Nome do Gestor,OU=Sigla da Área,DC=domain,DC=com
        if (preg_match('/CN=([^,]+),OU=([^,]+)/', $managerDN, $matches)) {
            $nomeGestor = trim($matches[1]);
            $siglaAreaGestor = trim($matches[2]);
            
            Log::info('✅ Dados extraídos do DN', [
                'nome_gestor' => $nomeGestor,
                'sigla_area' => $siglaAreaGestor
            ]);
            
            // Buscar o gestor pelo nome
            $gestor = User::where('name', 'like', "%{$nomeGestor}%")
                         ->where('active', true)
                         ->first();
            
            if ($gestor) {
                Log::info('✅ Gestor encontrado na hierarquia', [
                    'usuario_id' => $usuario->id,
                    'gestor_id' => $gestor->id,
                    'gestor_nome' => $gestor->name,
                    'area_gestor' => $siglaAreaGestor
                ]);
                return $gestor;
            }
            
            Log::warning('❌ Gestor não encontrado na base de dados', [
                'user_id' => $usuario->id,
                'nome_gestor_extraido' => $nomeGestor,
                'area_gestor_extraida' => $siglaAreaGestor
            ]);
        } else {
            Log::warning('❌ Formato do manager DN não reconhecido', [
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
            $resultado = $pppService->aprovarPpp($ppp, $request->input('comentario')); // envio para o service, onde irá executar a transferência de responsabilidade
            
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
            
            $proximoGestor = $this->obterProximoGestor(Auth::user());
            
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


    
    
