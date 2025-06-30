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
        Log::info('ENTROU no método store');

        try {
            Log::info('Início do método store em PppController');

            $dados = $request->validated();

            $dados['estimativa_valor'] = floatval(
                str_replace(['R$', '.', ','], ['', '', '.'], $dados['estimativa_valor'])
            );

            $dados['valor_contrato_atualizado'] = $request->filled('valor_contrato_atualizado')
                ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $dados['valor_contrato_atualizado']))
                : null;

            $dados['user_id'] = Auth::id();
            $dados['status_id'] = 1; // Status inicial padrão
            Log::debug('user_id atribuído:', ['user_id' => $dados['user_id']]);

            $dados['previsao'] = $request->filled('previsao') ? $dados['previsao'] : null;

            $ppp = PcaPpp::create($dados);
            Log::info('PcaPpp criado com sucesso.', ['id' => $ppp->id]);

            // Histórico inicial
            PppHistorico::create([
                'ppp_id'         => $ppp->id,
                'status_anterior' => null,
                'status_atual'   => $ppp->status_id,
                'justificativa'  => null,
                'user_id'        => Auth::id(),
            ]);
            Log::info('Histórico inicial registrado.', ['ppp_id' => $ppp->id]);

            return redirect()->route('ppp.index')->with('success', 'PPP criado com sucesso!');
            
        } catch (\Illuminate\Database\QueryException $ex) {
            dd([
                'tipo' => 'Erro de banco de dados',
                'mensagem' => $ex->getMessage(),
                'exception' => $ex,
                'dados' => $dados ?? null,
                'trace' => $ex->getTraceAsString()
            ]);
        } catch (\ErrorException $ex) {
            dd([
                'tipo' => 'Erro PHP (ErrorException)',
                'mensagem' => $ex->getMessage(),
                'exception' => $ex,
                'dados' => $dados ?? null,
                'trace' => $ex->getTraceAsString()
            ]);
        } catch (\Throwable $ex) {
            dd([
                'tipo' => 'Erro inesperado',
                'mensagem' => $ex->getMessage(),
                'exception' => $ex,
                'dados' => $dados ?? null,
                'trace' => $ex->getTraceAsString()
            ]);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = PcaPpp::where('user_id', Auth::id())
                           ->with(['user', 'historicos.usuario', 'historicos.statusAnterior', 'historicos.statusAtual'])
                           ->orderBy('created_at', 'desc');

            // Aplicar filtros se fornecidos
            if ($request->filled('status')) {
                $query->where('status', $request->status);
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


}