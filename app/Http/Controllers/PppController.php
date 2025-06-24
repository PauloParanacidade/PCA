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

            // Usar somente dados validados
            $dados = $request->validated();

            // Conversão de valores monetários para float
            $dados['estimativa_valor'] = floatval(
                str_replace(['R$', '.', ','], ['', '', '.'], $dados['estimativa_valor'])
            );

            $dados['valor_contrato_atualizado'] = $request->filled('valor_contrato_atualizado')
                ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $dados['valor_contrato_atualizado']))
                : null;

            // Atribuição do usuário autenticado
            $dados['user_id'] = auth()->id();
            Log::debug('user_id atribuído:', ['user_id' => $dados['user_id']]);

            //Isso permite que previsao aceite null quando não for renovação de contrato (ou o campo não for preenchido).
            $dados['previsao'] = $request->filled('previsao') ? $dados['previsao'] : null;

            // Criar o registro no banco
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
}
