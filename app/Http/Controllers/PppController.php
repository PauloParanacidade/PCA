<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePppRequest;
use App\Models\PcaPPP as PPP;

class PppController extends Controller
{
    public function store(StorePppRequest $request)
    {
        // Aqui o request já está validado automaticamente

        $data = $request->validated(); // pega só os dados validados

        // Crie o PPP usando a model, por exemplo:
        PPP::create($data);

        return redirect()->back()->with('success', 'PPP criado com sucesso');
    }
}
