<?php


namespace App\Http\Controllers;

use App\Http\Requests\StorePppRequest;
use App\Models\PcaPpp;
use App\Models\PppHistorico;
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

            $dados['user_id'] = auth()->id();
            Log::debug('user_id atribuído:', ['user_id' => $dados['user_id']]);

            $dados['previsao'] = $request->filled('previsao') ? $dados['previsao'] : null;

            $ppp = PcaPpp::create($dados);
            Log::info('PcaPpp criado com sucesso.', ['id' => $ppp->id]);

            // Histórico inicial
            // PppHistorico::create([
            //     'ppp_id'         => $ppp->id,
            //     'status_anterior' => null,
            //     'status_atual'   => $ppp->status_id,
            //     'justificativa'  => null,
            //     'user_id'        => auth()->id(),
            // ]);
            Log::info('Histórico inicial registrado.', ['ppp_id' => $ppp->id]);

            //redireciona para ppp
            return redirect()->route('ppp.index')->with('success', 'PPP criado com sucesso.');
        } catch (\Illuminate\Database\QueryException $ex) {
            Log::error('Erro de banco de dados ao criar PPP: ' . $ex->getMessage(), [
                'exception' => $ex,
                'dados' => $dados ?? null
            ]);
            Log::debug($ex->getTraceAsString());
            dd($ex);
            return back()->withInput()->withErrors(['msg' => 'Erro no banco de dados. Contate o administrador.']);
        } catch (\ErrorException $ex) {
            Log::error('Erro PHP (ErrorException): ' . $ex->getMessage(), [
                'exception' => $ex,
                'dados' => $dados ?? null
            ]);
            Log::debug($ex->getTraceAsString());
            dd($ex);
            return back()->withInput()->withErrors(['msg' => 'Erro interno no sistema. Contate o administrador.']);
        } catch (\Throwable $ex) {
            Log::error('Erro inesperado ao criar PPP: ' . $ex->getMessage(), [
                'exception' => $ex,
                'dados' => $dados ?? null
            ]);
            Log::debug($ex->getTraceAsString());
            dd($ex);
            return back()->withInput()->withErrors(['msg' => 'Erro inesperado. Contate o administrador.']);
        }
    }

    public function index()
    {
        Log::info('ENTROU no método index em PppController');

        try {
            $usuarioId = auth()->id();
            Log::debug('Buscando PPPs do usuário logado', ['user_id' => $usuarioId]);

            $ppps = PcaPpp::where('user_id', $usuarioId)->get();
            Log::info('Quantidade de PPPs encontrados:', ['total' => $ppps->count()]);

            return view('ppp.index', compact('ppps'));
        } catch (\Throwable $ex) {
            Log::error('Erro ao carregar os PPPs do usuário:', [
                'exception' => $ex,
                'user_id' => auth()->id(),
            ]);
            Log::debug($ex->getTraceAsString());
            return back()->withErrors(['msg' => 'Erro ao carregar seus PPPs.']);
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
                    'user_id'        => auth()->id(),
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

            return redirect()->route('ppp.meus')->with('success', 'PPP excluído com sucesso.');
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