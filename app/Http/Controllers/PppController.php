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

            $dados = $request->all();
            Log::debug('Dados recebidos no request:', $dados);

            // Tratamento dos valores monetários
            if (isset($dados['estimativa_valor'])) {
                $dados['estimativa_valor'] = intval(str_replace(['R$', '.', ','], ['', '', ''], $dados['estimativa_valor']));
            } else {
                Log::warning('Campo estimativa_valor não encontrado no request.');
            }

            if ($request->has('valor_contrato_atualizado') && !empty($dados['valor_contrato_atualizado'])) {
                $dados['valor_contrato_atualizado'] = intval(str_replace(['R$', '.', ','], ['', '', ''], $dados['valor_contrato_atualizado']));
            } else {
                $dados['valor_contrato_atualizado'] = null;
            }

            // Adaptação do campo composto
            if ($request->has('tempo_aquisicao')) {
                $dados['ate_partir_dia'] = $dados['tempo_aquisicao'];
                unset($dados['tempo_aquisicao']);
            } else {
                Log::warning('Campo tempo_aquisicao não encontrado no request.');
            }

            // Criar o registro no banco
            $ppp = PcaPpp::create($dados);
            Log::info('PcaPpp criado com sucesso.', ['id' => $ppp->id]);

            // Retorno para a view com mensagem de sucesso e mantém inputs
            return back()->withInput()->with('success', 'PPP criado com sucesso!');

        } catch (\Illuminate\Database\QueryException $ex) {
            Log::error('Erro de banco de dados ao criar PPP: ' . $ex->getMessage(), [
                'exception' => $ex,
                'dados' => $dados ?? null
            ]);
            return back()->withInput()->withErrors(['msg' => 'Erro no banco de dados. Contate o administrador.']);

        } catch (\ErrorException $ex) {
            Log::error('Erro PHP (ErrorException): ' . $ex->getMessage(), [
                'exception' => $ex,
                'dados' => $dados ?? null
            ]);
            return back()->withInput()->withErrors(['msg' => 'Erro interno no sistema. Contate o administrador.']);

        } catch (\Throwable $ex) {
            Log::error('Erro inesperado ao criar PPP: ' . $ex->getMessage(), [
                'exception' => $ex,
                'dados' => $dados ?? null
            ]);
            return back()->withInput()->withErrors(['msg' => 'Erro inesperado. Contate o administrador.']);
        }
    }
}
