<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin', function ($user) {
            return $user->hasRole('admin');
        });

        Gate::define('manager', function ($user) {
            return $user->hasRole('manager');
        });

        Gate::define('user', function ($user) {
            return $user->hasRole('user');
        });

        // Gates para usuários especiais - secretária tem os mesmos privilégios
        Gate::define('daf', function ($user) {
            return $user->hasRole(['daf', 'secretaria']);
        });

        Gate::define('dom', function ($user) {
            return $user->hasRole(['dom', 'secretaria']);
        });

        Gate::define('doe', function ($user) {
            return $user->hasRole(['doe', 'secretaria']);
        });

        Gate::define('supex', function ($user) {
            return $user->hasRole(['supex', 'secretaria']);
        });

        Gate::define('secretaria', function ($user) {
            return $user->hasRole('secretaria');
        });

        // Gate para usuários especiais (todos juntos)
        Gate::define('usuario_especial', function ($user) {
            return $user->hasRole(['admin', 'daf', 'dom', 'doe', 'supex', 'secretaria', 'gestor']);
        });
    }
}
