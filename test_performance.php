<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\HierarquiaService;

$user = User::first();

echo "=== TESTE DE PERFORMANCE - HIERARQUIA SERVICE OTIMIZADO ===\n";
echo "Usuário de teste: {$user->name} (ID: {$user->id})\n\n";

// Limpar cache primeiro
\Illuminate\Support\Facades\Cache::forget("arvore_hierarquica_user_{$user->id}");

// Teste da versão otimizada (primeira execução - sem cache)
echo "1. Testando versão OTIMIZADA (sem cache)...\n";
$start = microtime(true);
$service = new HierarquiaService();
$result = $service->obterArvoreHierarquica($user);
$end = microtime(true);
$tempoSemCache = round(($end - $start) * 1000, 2);

echo "   Tempo: {$tempoSemCache} ms\n";
echo "   Usuários: " . count($result) . "\n\n";

// Teste da versão otimizada (segunda execução - com cache)
echo "2. Testando versão OTIMIZADA (com cache)...\n";
$start = microtime(true);
$resultCache = $service->obterArvoreHierarquica($user);
$end = microtime(true);
$tempoComCache = round(($end - $start) * 1000, 2);

echo "   Tempo: {$tempoComCache} ms\n";
echo "   Usuários: " . count($resultCache) . "\n\n";

// Comparação
echo "=== COMPARAÇÃO ===\n";
echo "Sem cache: {$tempoSemCache} ms\n";
echo "Com cache: {$tempoComCache} ms\n\n";

$melhoria = round((($tempoSemCache - $tempoComCache) / $tempoSemCache) * 100, 1);
echo "Melhoria com cache: {$melhoria}%\n\n";

// Verificar se os resultados são iguais
sort($result);
sort($resultCache);

$resultadosIguais = ($result === $resultCache);
echo "Resultados idênticos: " . ($resultadosIguais ? 'SIM' : 'NÃO') . "\n";

echo "\nIDs dos usuários na árvore: " . implode(', ', $result) . "\n";