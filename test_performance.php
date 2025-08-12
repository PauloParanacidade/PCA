<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\HierarquiaService;

$user = User::first();

echo "=== TESTE DE PERFORMANCE - HIERARQUIA SERVICE OTIMIZADO ===\n";
echo "Usuário de teste: {$user->name} (ID: {$user->id})\n\n";

echo "1. Testando versão atual - Sem cache...\n";
$start = microtime(true);
$service = new HierarquiaService();
$result = $service->obterArvoreHierarquica($user);
$end = microtime(true);
$tempoAtual = round(($end - $start) * 1000, 2);

echo "   Tempo: {$tempoAtual} ms\n";
echo "   Usuários: " . count($result) . "\n\n";

echo "2. Testando versão OTIMIZADA ...\n";
$start = microtime(true);
$resultCache = $service->obterArvoreHierarquica2($user);
$end = microtime(true);
$tempoOti = round(($end - $start) * 1000, 2);

echo " obterArvoreHierarquica: \n";
foreach ($result as $res) {
    echo " {$res} ";
}
echo "\n";
echo " obterArvoreHierarquica2: \n";
foreach ($resultCache as $res) {
    echo " {$res} ";
}
echo "\n";

echo "   Tempo: {$tempoOti} ms\n";
echo "   Usuários: " . count($resultCache) . "\n\n";

echo "=== COMPARAÇÃO ===\n";
echo "Sem cache: {$tempoAtual} ms\n";
echo "Com cache: {$tempoOti} ms\n\n";

$melhoria = round((($tempoAtual - $tempoOti) / $tempoAtual) * 100, 1);
echo "Melhoria com remoção de query dentro de loop: {$melhoria}%\n\n";

sort($result);
sort($resultCache);
$resultadosIguais = ($result === $resultCache);
echo "Resultados idênticos: " . ($resultadosIguais ? 'SIM' : 'NÃO') . "\n";

echo "\nIDs dos usuários na árvore: " . implode(', ', $result) . "\n";

echo "\nCuidado com queries dentro de loops!\n\n";
echo ">    foreach(foo as bar) {\n";
echo ">        usuarios = User::where('active', true)->get();\n";
echo ">    }\n";
echo "\nA cada iteração no loop, é realizado um query no BD\n";
echo "Se possível extrair a query e utilizar a lista resultante dela no loop.\n";
