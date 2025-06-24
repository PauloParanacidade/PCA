<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePppRequest;
use App\Models\PcaPpp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MeusController extends Controller
{
    public function verPps()
    {
        Log::info('ENTROU no método meusPpps em PppController');

        try {
            $usuarioId = auth()->id();
            Log::debug('Buscando PPPs do usuário logado', ['user_id' => $usuarioId]);

            $ppps = PcaPpp::where('user_id', $usuarioId)->get();
            Log::info('Quantidade de PPPs encontrados:', ['total' => $ppps->count()]);

            return view('ppp.meus', compact('ppps'));
        } catch (\Throwable $ex) {
            Log::error('Erro ao carregar os PPPs do usuário:', [
                'exception' => $ex,
                'user_id' => auth()->id(),
            ]);
            Log::debug($ex->getTraceAsString());
            return back()->withErrors(['msg' => 'Erro ao carregar seus PPPs. Tente novamente ou contate o administrador.']);
        }
    }
}
