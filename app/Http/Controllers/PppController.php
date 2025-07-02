<?php


namespace App\Http\Controllers;

use App\Http\Requests\StorePppRequest;
use App\Models\PcaPpp;
use App\Models\PppHistorico;
use App\Models\PppStatusDinamico;
use App\Models\User;
use App\Services\PppStatusService;
use App\Services\PppHistoricoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PppController extends Controller
{
    protected $statusService;
    protected $historicoService;
    
    public function __construct(PppStatusService $statusService, PppHistoricoService $historicoService)
    {
        $this->statusService = $statusService;
        $this->historicoService = $historicoService;
    }
    
    public function create()
    {
        return view('ppp.create');
    }

    public function store(StorePppRequest $request)
    {
        //dd($request);
        try {
            $partes = $this->separarPorTraco($request->input('area_responsavel'));
            $areaResponsavel = $partes[0];
            $gestorResponsavel = $partes[1];
            $manager = User::where('name', $gestorResponsavel)
            ->where('department', $areaResponsavel)
            ->first();
            //dd($manager);
    
            // ✅ NOVA REGRA: Verificar e atribuir papel de gestor automaticamente
            if ($manager) {
                $manager->garantirPapelGestor();
            }
    
            $ppp = PcaPpp::create([
                'user_id' => Auth::id(),
                'status_fluxo' => 'rascunho',
                'gestor_atual_id' => $manager->id,
                'previsao' => $request->filled('previsao') ? $request->previsao : null,
                'area_solicitante'=>$request->area_solicitante,
                'area_responsavel'=>$request->area_responsavel,
                'categoria'=>$request->categoria,
                'nome_item'=>$request->nome_item,
                'descricao'=>$request->descricao,
                'quantidade'=>$request->quantidade,
                'justificativa_pedido'=>$request->justificativa_pedido,
                'estimativa_valor'=>$request->estimativa_valor,
                'justificativa_valor'=>$request->justificativa_valor,
                'origem_recurso'=>$request->origem_recurso,
                'grau_prioridade'=>$request->grau_prioridade,
                'ate_partir_dia'=>$request->ate_partir_dia,
                'data_ideal_aquisicao'=>$request->data_ideal_aquisicao,
                'vinculacao_item'=>$request->vinculacao_item,
                'justificativa_vinculacao'=>$request->justificativa_vinculacao,
                'renov_contrato'=>$request->renov_contrato,
                'previsao'=>$request->previsao,
                'valor_contrato_atualizado'=>$request->valor_contrato_atualizado,
            ]);
            
            
            // Criar status dinâmico inicial
            $statusDinamico = $this->statusService->criarStatusDinamico(
                $ppp, 
                'enviou_para_avaliacao', 
                auth()->user()->id, 
                $manager->id, 
                'Rascunho'
            );

            // Registrar no histórico
            $this->historicoService->registrarCriacao($ppp, $statusDinamico);
            //dd($statusDinamico);
            return redirect()->route('ppp.index')->with('success', 'PPP criado com sucesso!');
            
        } catch (\Throwable $ex) {
            Log::error('Erro ao criar PPP: ' . $ex->getMessage());
            return back()->withInput()->withErrors(['msg' => 'Erro ao criar PPP.']);
        }
    }

        private function separarPorTraco($texto)
        {
            if (empty($texto)) {
                return ['', ''];
            }
            
            // Separa pelo traço e remove espaços em branco das extremidades
            $partes = array_map('trim', explode('-', $texto, 2));
            
            // Garante que sempre retorne pelo menos 2 elementos
            if (count($partes) === 1) {
                return [$partes[0], ''];
            }
            
            return $partes;
        }
        
    public function index(Request $request)
    {
        //dd($request);  
        try {
            // Modificar para incluir PPPs próprios E PPPs para aprovação
            $query = PcaPpp::where(function($q) {
                    $q->where('user_id', Auth::id()) // PPPs criados pelo usuário
                      ->orWhere('gestor_atual_id', Auth::id()); // PPPs enviados para aprovação
                })
                ->with([
                    'user', 
                    'statusDinamicos',
                    'gestorAtual',
                    'historicos.usuario'
                ])
                ->orderBy('created_at', 'desc');
        
            // Aplicar filtros
            if ($request->filled('status_fluxo')) {
                $query->where('status_fluxo', $request->status_fluxo);
            }
            
            if ($request->filled('setor')) {
                $query->where('area_solicitante', $request->setor);
            }
            
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
            dd($e);
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
            return view('ppp.edit', compact('ppp'));
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
        //dd($request);
        try {
            $ppp = PcaPpp::findOrFail($id);
            $dados = $request->validated();
            
            // ✅ CORREÇÃO: Processar valores monetários dos dados validados
            if (isset($dados['estimativa_valor'])) {
                $dados['estimativa_valor'] = floatval(
                    str_replace(['R$', '.', ','], ['', '', '.'], $dados['estimativa_valor'])
                );
            }
            
            if (isset($dados['valor_contrato_atualizado'])) {
                $dados['valor_contrato_atualizado'] = floatval(
                    str_replace(['R$', '.', ','], ['', '', '.'], $dados['valor_contrato_atualizado'])
                );
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

            return redirect()->route('ppp.index')->with('success', 'PPP atualizado com sucesso.');
        } catch (\Throwable $ex) {
            Log::error('Erro ao atualizar PPP: ' . $ex->getMessage(), [
                'exception' => $ex,
                'ppp_id' => $id,
            ]);
            Log::debug($ex->getTraceAsString());
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
        dd($statusFormatado);
        // Criar novo status dinâmico
        return \App\Models\PppStatusDinamico::create([
            'ppp_id' => $ppp->id,
            'status_tipo_id' => $tipoStatus === 'rascunho' ? null : \App\Models\PppStatus::where('tipo', $tipoStatus)->first()->id,
            'remetente_nome' => $remetente->name ?? null,
            'remetente_sigla' => $remetenteSigla,
            'destinatario_nome' => $destinatario->name ?? null,
            'destinatario_sigla' => $destinatarioSigla,
            'status_formatado' => $statusFormatado,
            'ativo' => true,
        ]);
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
                return back()->withErrors(['msg' => 'Você não tem permissão para esta ação.']);
            }
            
            $proximoGestor = $this->obterProximoGestor(Auth::user());
            
            if (!$proximoGestor) {
                return back()->withErrors(['msg' => 'Não foi possível identificar o próximo gestor.']);
            }
            
            $ppp->update([
                'status_fluxo' => 'aguardando_aprovacao',
                'gestor_atual_id' => $proximoGestor->id,
            ]);
            
            // Criar status dinâmico
            $statusDinamico = $this->statusService->criarStatusDinamico(
                $ppp, 
                'enviou_para_avaliacao', 
                Auth::id(), 
                $proximoGestor->id
            );

            // Registrar no histórico
            $this->historicoService->registrarEnvioAprovacao(
                $ppp, 
                $statusDinamico, 
                $request->input('justificativa', 'PPP enviado para aprovação')
            );
            
            return redirect()->route('ppp.index')->with('success', 'PPP enviado para aprovação com sucesso!');
            
        } catch (\Throwable $ex) {
            Log::error('Erro ao enviar PPP para aprovação: ' . $ex->getMessage());
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
        if ($ppp->status_fluxo !== 'aguardando_aprovacao') {
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
}