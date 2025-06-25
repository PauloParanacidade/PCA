<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePppRequest;
use App\Models\PcaPpp;
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

            return redirect()->route('ppp.meus')->with('success', 'PPP criado com sucesso!');
        } catch (\Illuminate\Database\QueryException $ex) {
            Log::error('Erro de banco de dados ao criar PPP: ' . $ex->getMessage(), [
                'exception' => $ex,
                'dados' => $dados ?? null
            ]);
            Log::debug($ex->getTraceAsString());
            return back()->withInput()->withErrors(['msg' => 'Erro no banco de dados. Contate o administrador.']);
        } catch (\ErrorException $ex) {
            Log::error('Erro PHP (ErrorException): ' . $ex->getMessage(), [
                'exception' => $ex,
                'dados' => $dados ?? null
            ]);
            Log::debug($ex->getTraceAsString());
            return back()->withInput()->withErrors(['msg' => 'Erro interno no sistema. Contate o administrador.']);
        } catch (\Throwable $ex) {
            Log::error('Erro inesperado ao criar PPP: ' . $ex->getMessage(), [
                'exception' => $ex,
                'dados' => $dados ?? null
            ]);
            Log::debug($ex->getTraceAsString());
            return back()->withInput()->withErrors(['msg' => 'Erro inesperado. Contate o administrador.']);
        }
    }

    public function index()
    {
        $ppps = PcaPpp::all();
        return view('ppp.index', compact('ppps'));
    }

    public function show($id)
    {
        $ppp = PcaPpp::findOrFail($id);
        return view('ppp.show', compact('ppp'));
    }

    public function edit($id)
    {
        $ppp = PcaPpp::findOrFail($id);
        return view('ppp.edit', compact('ppp'));
    }

    public function update(Request $request, $id)
{
    try {
        $ppp = PcaPpp::findOrFail($id);

        $dados = $request->validate([
            'categoria' => 'required|string|max:45',
            'nome_item' => 'required|string|max:100',
            'descricao' => 'required|string|max:255',
            'quantidade' => 'required|string|max:45',
            'justificativa_pedido' => 'required|string|max:100',

            'estimativa_valor' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'], // decimal com até 2 casas
            'origem_recurso' => 'required|string|max:20',
            'justificativa_valor' => 'required|string|max:100',
            'grau_prioridade' => 'required|string|max:20',

            'ate_partir_dia' => 'required|string|max:20',
            'data_ideal_aquisicao' => 'required|date',

            'vinculacao_item' => 'required|in:Sim,Não',
            'justificativa_vinculacao' => 'nullable|string|max:100',

            'renov_contrato' => 'required|in:Sim,Não',
            'previsao' => 'nullable|date',
            'num_contrato' => 'nullable|string|max:10',
            'valor_contrato_atualizado' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);
 
        // Converte valores monetários formatados para float
        $dados['estimativa_valor'] = floatval(str_replace([',', 'R$', '.'], ['', '', ''], $request->estimativa_valor));

        if ($request->filled('valor_contrato_atualizado')) {
            $dados['valor_contrato_atualizado'] = floatval(str_replace([',', 'R$', '.'], ['', '', ''], $request->valor_contrato_atualizado));
        } else {
            $dados['valor_contrato_atualizado'] = null;
        }

        $ppp->update($dados);

        return redirect()->route('ppp.index')->with('success', 'PPP atualizado com sucesso.');
    } catch (\Throwable $ex) {
        Log::error('Erro ao atualizar PPP: ' . $ex->getMessage(), [
            'exception' => $ex
        ]);
        return back()->withInput()->withErrors(['msg' => 'Erro ao atualizar.']);
    }
}



    public function destroy($id)
    {
        try {
            $ppp = PcaPpp::findOrFail($id);

            // Opcional: verificar se o usuário tem permissão para deletar este PPP

            $ppp->delete();

            return redirect()->route('ppp.meus')->with('success', 'PPP excluído com sucesso.');
        } catch (\Throwable $ex) {
            Log::error('Erro ao excluir PPP: ' . $ex->getMessage(), [
                'exception' => $ex
            ]);
            return back()->withErrors(['msg' => 'Erro ao excluir.']);
        }
    }
}
