<?php

namespace App\Providers;

use App\Models\Emprestimo;
use App\Models\Usuario;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        // Paginação no padrão visual do protótipo (R.N.F. 13)
        Paginator::defaultView('partials.paginacao');

        // Gate usado nas views e controllers para o perfil Administrador
        Gate::define('admin', fn (Usuario $usuario) => $usuario->isAdmin());

        // Contador de atrasos exibido no menu lateral do Administrador
        View::composer(['layouts.partials.sidebar', 'layouts.partials.topbar'], function ($view) {
            $usuario = auth()->user();
            $view->with('qtdAtrasados', $usuario?->isAdmin() ? Emprestimo::atrasados()->count() : 0);
        });
    }
}
