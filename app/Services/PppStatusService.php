<?php

namespace App\Services;

use App\Models\PcaPpp;
use App\Models\PppStatus;
use App\Models\PppStatusDinamico;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PppStatusService
{
    /**
     * Cria um novo status dinâmico para o PPP
     */
    public function criarStatusDinamico(PcaPpp $ppp, string $tipoStatus, ?int $remetenteId = null, ?int $destinatarioId = null, ?string $statusCustom = null): PppStatusDinamico
    {
        //dd($ppp);
        Log::info('Criando status dinâmico para PPP: ' . $ppp->id);
        // Desativar status dinâmico anterior
        $ppp->statusDinamicos()->update(['ativo' => false]);
        
        // if ($statusCustom) {
        //     return $this->criarStatusCustomizado($ppp, $statusCustom);
        // }
        
        return $this->criarStatusComTemplate($ppp, $tipoStatus, $remetenteId, $destinatarioId);
    }
    
    /**
     * Cria status customizado (ex: rascunho)
     */
    private function criarStatusCustomizado(PcaPpp $ppp, string $statusCustom): PppStatusDinamico
    {
        return PppStatusDinamico::create([
            'ppp_id' => $ppp->id,
            'status_tipo_id' => 1,
            'remetente_nome' => null,
            'remetente_sigla' => null,
            'destinatario_nome' => null,
            'destinatario_sigla' => null,
            'status_formatado' => $statusCustom,
            'ativo' => true,
        ]);
    }
    
    /**
     * Cria status usando template
     */
    private function criarStatusComTemplate(PcaPpp $ppp, string $tipoStatus, ?int $remetenteId, ?int $destinatarioId): PppStatusDinamico
    {
        $statusTemplate = PppStatus::where('tipo', $tipoStatus)->first();
        
        if (!$statusTemplate) {
            throw new \Exception("Template de status não encontrado: {$tipoStatus}");
        }
        
        $remetente = $remetenteId ? User::find($remetenteId) : null;
        $destinatario = $destinatarioId ? User::find($destinatarioId) : null;
        
        $statusFormatado = $this->formatarStatusComUsuarios(
            $statusTemplate->template,
            $remetente,
            $destinatario
        );
        //dd($destinatario);
        return PppStatusDinamico::create([
            'ppp_id' => $ppp->id,
            'status_tipo_id' => $statusTemplate->id,
            'remetente_nome' => $remetente->name ?? null,
            'remetente_sigla' => $remetente ? $this->extrairSiglaArea($remetente) : null,
            'destinatario_nome' => $destinatario->name ?? null,
            'destinatario_sigla' => $destinatario->department,
            'status_formatado' => $statusFormatado,
            'ativo' => true,
        ]);
    }
    
    /**
     * Formata o status substituindo placeholders
     */
    private function formatarStatusComUsuarios(string $template, ?User $remetente, ?User $destinatario): string
    {
        $statusFormatado = $template;
        
        if ($remetente) {
            $remetenteSigla = $this->extrairSiglaArea($remetente);
            $remetenteTexto = $remetente->name . ' [' . ($remetenteSigla ?? 'N/A') . ']';
            $statusFormatado = str_replace('[remetente]', $remetenteTexto, $statusFormatado);
        }
        
        if ($destinatario) {
            $destinatarioSigla = $this->extrairSiglaAreaGestor($destinatario);
            $destinatarioTexto = $destinatario->name . ' [' . $destinatario->department . ']';
            $statusFormatado = str_replace('[destinatario]', $destinatarioTexto, $statusFormatado);
        }
        
        return $statusFormatado;
    }
    
    /**
     * Extrai a sigla da área do próprio usuário
     */
    private function extrairSiglaArea(User $usuario): string
    {
        return $usuario->department ?? 'N/A';
    }
    
    /**
     * Extrai a sigla da área do gestor
     */
    private function extrairSiglaAreaGestor(User $usuario): string
    {
        $department = $usuario->department;
        
        if (!$department) {
            return 'N/A';
        }
        
        if (preg_match('/OU=([^,]+)/', $department, $matches)) {
            return trim($matches[1]);
        }
        
        return 'N/A';
    }
}