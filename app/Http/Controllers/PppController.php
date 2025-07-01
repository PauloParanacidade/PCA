<?php


namespace App\Http\Controllers;

use App\Http\Requests\StorePppRequest;
use App\Models\PcaPpp;
use App\Models\PppHistorico;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PppController extends Controller
{
    public function create()
    {
        return view('ppp.create');
    }

    public function store(StorePppRequest $request)
    {
        try {
            $dados = $request->validated();
            
            // Processar valores monetários
            $dados['estimativa_valor'] = floatval(
                str_replace(['R$', '.', ','], ['', '', '.'], $dados['estimativa_valor'])
            );
            
            $dados['valor_contrato_atualizado'] = $request->filled('valor_contrato_atualizado')
                ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $request->valor_contrato_atualizado))
                : null;
                
            // Configurar dados iniciais
            $dados['user_id'] = Auth::id();
            $dados['status_fluxo'] = 'rascunho';
            $dados['gestor_atual_id'] = null; // Será definido quando enviar para aprovação
            $dados['previsao'] = $request->filled('previsao') ? $dados['previsao'] : null;
            
            $ppp = PcaPpp::create($dados);
            
            // Criar status dinâmico inicial (rascunho)
            $this->criarStatusDinamico($ppp, 'rascunho', null, null, 'PPP criado como rascunho');
            
            // Registrar no histórico
            PppHistorico::create([
                'ppp_id' => $ppp->id,
                'status_dinamico_id' => $ppp->statusDinamico->id,
                'acao' => 'criacao',
                'justificativa' => 'PPP criado pelo usuário',
                'user_id' => Auth::id(),
            ]);
            
            Log::info('PPP criado com sucesso.', ['id' => $ppp->id]);
            
            return redirect()->route('ppp.index')->with('success', 'PPP criado com sucesso!');
            
        } catch (\Throwable $ex) {
            Log::error('Erro ao criar PPP: ' . $ex->getMessage());
            return back()->withInput()->withErrors(['msg' => 'Erro ao criar PPP.']);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = PcaPpp::where('user_id', Auth::id())
                       ->with([
                           'user', 
                           'statusDinamico',
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
        try {
            $ppp = PcaPpp::findOrFail($id);

            $dados = $request->validated();

            $dados['estimativa_valor'] = floatval(
                str_replace(['R$', '.', ','], ['', '', '.'], $request->estimativa_valor)
            );

            $dados['valor_contrato_atualizado'] = $request->filled('valor_contrato_atualizado')
                ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $request->valor_contrato_atualizado))
                : null;

            $dados['previsao'] = $request->filled('previsao') ? $request->previsao : null;

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
        } else {
            // Buscar template do status
            $statusTemplate = \App\Models\PppStatus::where('tipo', $tipoStatus)->first();
            
            if (!$statusTemplate) {
                throw new \Exception("Template de status não encontrado: {$tipoStatus}");
            }
            
            // Obter dados dos usuários
            $remetente = $remetenteId ? User::find($remetenteId) : null;
            $destinatario = $destinatarioId ? User::find($destinatarioId) : null;
            
            // Substituir placeholders
            $statusFormatado = $statusTemplate->template;
            
            if ($remetente) {
                $remetenteTexto = $remetente->name . ' [' . ($remetente->setor ?? 'N/A') . ']';
                $statusFormatado = str_replace('[remetente]', $remetenteTexto, $statusFormatado);
            }
            
            if ($destinatario) {
                $destinatarioTexto = $destinatario->name . ' [' . ($destinatario->setor ?? 'N/A') . ']';
                $statusFormatado = str_replace('[destinatario]', $destinatarioTexto, $statusFormatado);
            }
        }
        
        // Criar novo status dinâmico
        return \App\Models\PppStatusDinamico::create([
            'ppp_id' => $ppp->id,
            'status_tipo_id' => $tipoStatus === 'rascunho' ? null : \App\Models\PppStatus::where('tipo', $tipoStatus)->first()->id,
            'remetente_nome' => $remetente->name ?? null,
            'remetente_sigla' => $remetente->setor ?? null,
            'destinatario_nome' => $destinatario->name ?? null,
            'destinatario_sigla' => $destinatario->setor ?? null,
            'status_formatado' => $statusFormatado,
            'ativo' => true,
        ]);
    }

    public function enviarParaAprovacao($id, Request $request)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);
            
            // Verificar se é o dono do PPP
            if ($ppp->user_id !== Auth::id()) {
                return back()->withErrors(['msg' => 'Você não tem permissão para esta ação.']);
            }
            
            // Obter próximo gestor na hierarquia
            $proximoGestor = $this->obterProximoGestor(Auth::user());
            
            if (!$proximoGestor) {
                return back()->withErrors(['msg' => 'Não foi possível identificar o próximo gestor.']);
            }
            
            // Atualizar PPP
            $ppp->update([
                'status_fluxo' => 'aguardando_aprovacao',
                'gestor_atual_id' => $proximoGestor->id,
            ]);
            
            // Criar status dinâmico
            $statusDinamico = $this->criarStatusDinamico(
                $ppp, 
                'enviou_para_avaliacao', 
                Auth::id(), 
                $proximoGestor->id
            );
            
            // Registrar no histórico
            PppHistorico::create([
                'ppp_id' => $ppp->id,
                'status_dinamico_id' => $statusDinamico->id,
                'acao' => 'envio_aprovacao',
                'justificativa' => $request->input('justificativa', 'PPP enviado para aprovação'),
                'user_id' => Auth::id(),
            ]);
            
            // TODO: Enviar notificação por email
            
            return redirect()->route('ppp.index')->with('success', 'PPP enviado para aprovação com sucesso!');
            
        } catch (\Throwable $ex) {
            Log::error('Erro ao enviar PPP para aprovação: ' . $ex->getMessage());
            return back()->withErrors(['msg' => 'Erro ao enviar PPP para aprovação.']);
        }
    }

    private function obterProximoGestor($usuario)
    {
        // TODO: Implementar lógica de hierarquia baseada no setor/cargo
        // Por enquanto, retorna um gestor fixo para teste
        return \App\Models\User::where('role', 'gestor')->first();
    }

} // Fechar a classe aqui