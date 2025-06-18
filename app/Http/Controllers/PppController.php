<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePppRequest;
use App\Models\PcaPpp;
use Illuminate\Http\Request;

class PppController extends Controller
{
    public function create()
    {
        return view('ppp.create'); // nome do arquivo blade: resources/views/ppp/create.blade.php
    }
    
    public function store(StorePppRequest $request)
    {
        dd('entrou no store');
        
        $dados = $request->all();

        // Tratamento dos valores monetários
        $dados['estimativa_valor'] = intval(str_replace(['R$', '.', ','], ['', '', ''], $dados['estimativa_valor']));
        $dados['valor_contrato_atualizado'] = $request->input('valor_contrato_atualizado')
            ? intval(str_replace(['R$', '.', ','], ['', '', ''], $dados['valor_contrato_atualizado']))
            : null;

        // Adaptação do campo composto
        $dados['ate_partir_dia'] = $request->input('tempo_aquisicao');
        unset($dados['tempo_aquisicao']);

        // Salva no banco
        PcaPpp::create($dados);

        //return redirect()->route('ppp.create')->with('success', 'PPP criado com sucesso!');
        return back()->withInput()->with('success', 'PPP criado com sucesso!');
    }
}
