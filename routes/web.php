<?php

use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PppController;


Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rota de Stop Impersonate - FORA do middleware admin
Route::post('/admin/stop-impersonate', [ImpersonateController::class, 'stopImpersonate'])
    ->middleware(['auth'])
    ->name('admin.stop-impersonate');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users/roles', [UserRoleController::class, 'index'])->name('admin.users.roles');
    Route::put('/admin/users/{user}/roles', [UserRoleController::class, 'update'])->name('admin.users.roles.update');
    Route::post('/admin/users/roles/mass-update', [UserRoleController::class, 'massUpdate'])->name('admin.users.roles.mass-update');
    Route::put('/admin/users/{user}/toggle-active', [UserRoleController::class, 'toggleActive'])->name('admin.users.toggle-active');
    Route::post('/admin/users/sync-ldap', [UserRoleController::class, 'syncLdapUsers'])->name('admin.users.sync-ldap');
    Route::post('/admin/users/store', [UserRoleController::class, 'store'])->name('admin.users.store');
    Route::delete('/admin/users/{user}', [UserRoleController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/api/check-email', [UserController::class, 'checkEmail'])->name('api.check-email');

    // Rota de Impersonate - DENTRO do middleware admin
    Route::post('/admin/impersonate/{user}', [ImpersonateController::class, 'impersonate'])->name('admin.impersonate');
    Route::get('/admin/users/search', [UserRoleController::class, 'searchUsers'])->name('admin.users.search');
});

// Rotas do PPP para usuários autenticados
Route::middleware(['auth'])->group(function () {
    
    // Nova rota para "Meus PPPs" - PPPs criados pelo usuário
    Route::get('ppp/meus', [PppController::class, 'meusPpps'])->name('ppp.meus');
    Route::post('ppp/{ppp}/reenviar-apos-correcao', [PppController::class, 'reenviarAposCorrecao'])->name('ppp.reenviar-apos-correcao');
    
    Route::resource('ppp', PppController::class);
    
    Route::post('ppp/{ppp}/aprovar', [PppController::class, 'aprovar'])->name('ppp.aprovar');
    Route::post('/ppp/{ppp}/reprovar', [PppController::class, 'reprovar'])->name('ppp.reprovar');
    Route::post('ppp/{ppp}/solicitar-correcao', [PppController::class, 'solicitarCorrecao'])->name('ppp.solicitar-correcao');
    Route::post('ppp/{ppp}/enviar-aprovacao', [PppController::class, 'enviarParaAprovacao'])->name('ppp.enviar-aprovacao');
    
    // Nova rota para secretária incluir na PCA
    Route::post('ppp/{ppp}/incluir-pca', [PppController::class, 'incluirNaPca'])->name('ppp.incluir-pca');

    // Rota DIREX (agora usa o mesmo método com contexto)
    Route::post('/ppp/{ppp}/direx/incluir-pca', function($ppp) {
        return app(PppController::class)->incluirNaPca($ppp, 'direx');
    })->name('ppp.direx.incluir-pca');

    Route::get('/ppp/{id}/historico', [PppController::class, 'historico'])->name('ppp.history');
    
    // === NOVAS ROTAS PARA FLUXO DIREX E CONSELHO ===
    
    // Reunião DIREX
    Route::post('/ppp/direx/iniciar-reuniao', [PppController::class, 'iniciarReuniaoDirectx'])->name('ppp.direx.iniciar-reuniao');
    Route::get('/ppp/direx/proximo/{ppp?}', [PppController::class, 'proximoPppDirectx'])->name('ppp.direx.proximo');
    Route::get('/ppp/direx/anterior/{ppp?}', [PppController::class, 'anteriorPppDirectx'])->name('ppp.direx.anterior');
    Route::post('/ppp/direx/encerrar-reuniao', [PppController::class, 'encerrarReuniaoDirectx'])->name('ppp.direx.encerrar-reuniao');
    
    // Navegação durante reunião DIREX
    Route::get('/ppp/{ppp}/direx/navegar-proximo', [PppController::class, 'navegarProximoDirectx'])->name('ppp.direx.navegar-proximo');
    Route::get('/ppp/{ppp}/direx/navegar-anterior', [PppController::class, 'navegarAnteriorDirectx'])->name('ppp.direx.navegar-anterior');
    
    // Ações durante reunião DIREX
    Route::post('/ppp/{ppp}/direx/editar', [PppController::class, 'editarDuranteDirectx'])->name('ppp.direx.editar');
    Route::post('/ppp/{ppp}/direx/incluir-pca', function($id) {
        return app(PppController::class)->incluirNaPca($id, 'direx');
    })->name('ppp.direx.incluir-pca');
    Route::post('/ppp/direx/pausar', [PppController::class, 'pausarReuniaoDirectx'])->name('ppp.direx.pausar');
    Route::post('/ppp/direx/atualizar-status', [PppController::class, 'atualizarStatusDirectx'])->name('ppp.direx.atualizar-status');
    
    // Geração de relatórios
    Route::post('/ppp/relatorios/gerar-excel', [PppController::class, 'gerarExcel'])->name('ppp.relatorios.gerar-excel');
    Route::post('/ppp/relatorios/gerar-pdf', [PppController::class, 'gerarPdf'])->name('ppp.relatorios.gerar-pdf');
    
    // Aprovação do Conselho
    Route::post('/ppp/conselho/processar', [PppController::class, 'processarConselho'])->name('ppp.conselho.processar');
    
    // Histórico da secretária
    Route::get('/ppp/secretaria/historico', [PppController::class, 'historicoSecretaria'])->name('ppp.secretaria.historico');
    
    // Métodos auxiliares para verificação de estado
    Route::get('/ppp/direx/verificar-reuniao-ativa', [PppController::class, 'verificarReuniaoDirectxAtiva'])->name('ppp.direx.verificar-reuniao-ativa');
    Route::get('/ppp/direx/aguardando', [PppController::class, 'obterPppsAguardandoDirectx'])->name('ppp.direx.aguardando');
    Route::get('/ppp/conselho/aguardando', [PppController::class, 'obterPppsAguardandoConselho'])->name('ppp.conselho.aguardando');

    // Dashboard
    Route::get('/dashboard', [PppController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

    // REMOVER A ROTA DASHBOARD DUPLICADA DAQUI
    // Route::get('/dashboard', [PppController::class, 'dashboard'])
    // ->middleware(['auth', 'verified'])
    // ->name('dashboard');
});




// Rota para depuração (ambiente local)
if (app()->environment('local')) {
    Route::get('/debug/auth', function () {
        return response()->json([
            'auth_guards'      => config('auth.guards'),
            'auth_providers'   => config('auth.providers'),
            'ldap_config'      => config('ldap.connections'),
            'ldap_auth_config' => config('ldap_auth'),
        ]);
    });
}

require __DIR__ . '/auth.php';
