<?php

use App\Http\Controllers\Admin\AutorController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\EmprestimoAdminController;
use App\Http\Controllers\Admin\LivroController;
use App\Http\Controllers\Admin\NotificacaoController;
use App\Http\Controllers\Admin\RelatorioController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\CadastroController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RecuperacaoSenhaController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\EmprestimoController;
use App\Http\Controllers\HistoricoController;
use App\Http\Controllers\PainelController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'painel' : 'login'));

/*
| Visitante (sem sessão ativa) – R.F. 02, 06 e 07
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/cadastro', [CadastroController::class, 'create'])->name('cadastro');
    Route::post('/cadastro', [CadastroController::class, 'store'])->middleware('throttle:10,1')->name('cadastro.store');

    Route::get('/esqueci-senha', [RecuperacaoSenhaController::class, 'create'])->name('senha.solicitar');
    Route::post('/esqueci-senha', [RecuperacaoSenhaController::class, 'store'])->middleware('throttle:5,1')->name('senha.enviar');
    Route::get('/redefinir-senha/{token}', [RecuperacaoSenhaController::class, 'edit'])->name('senha.redefinir');
    Route::post('/redefinir-senha', [RecuperacaoSenhaController::class, 'update'])->name('senha.atualizar');
});

/*
| Usuário autenticado (Administrador e Usuário)
*/
Route::middleware(['auth', 'ativo'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/painel', [PainelController::class, 'index'])->name('painel');
    Route::get('/meu-painel', [PainelController::class, 'colaborador'])->name('painel.colaborador');      // R.F. 14

    Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');                       // R.F. 15

    Route::get('/emprestimos/novo', [EmprestimoController::class, 'create'])->name('emprestimos.create');  // R.F. 08
    Route::post('/emprestimos', [EmprestimoController::class, 'store'])->name('emprestimos.store');
    Route::post('/emprestimos/{emprestimo}/renovar', [EmprestimoController::class, 'renovar'])->name('emprestimos.renovar'); // R.F. 09

    Route::get('/meus-emprestimos', [HistoricoController::class, 'meu'])->name('historico.meu');          // R.F. 16

    /*
    | Exclusivo do Administrador (R.N.F. 06)
    */
    Route::prefix('admin')->name('admin.')->middleware('perfil:ADMIN')->group(function () {
        Route::get('/painel', [PainelController::class, 'admin'])->name('painel');                        // R.F. 13

        Route::resource('usuarios', UsuarioController::class)->except('show')
            ->parameters(['usuarios' => 'usuario']);                                                     // R.F. 01
        Route::patch('/usuarios/{usuario}/situacao', [UsuarioController::class, 'alternarSituacao'])->name('usuarios.situacao');
        Route::get('/usuarios/{usuario}/historico', [HistoricoController::class, 'usuario'])->name('usuarios.historico'); // R.F. 16

        Route::resource('autores', AutorController::class)->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['autores' => 'autor']);                                                        // R.F. 03
        Route::resource('categorias', CategoriaController::class)->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['categorias' => 'categoria']);                                                 // R.F. 04
        Route::resource('livros', LivroController::class)->except(['index', 'show'])
            ->parameters(['livros' => 'livro']);                                                         // R.F. 05

        Route::get('/emprestimos', [EmprestimoAdminController::class, 'index'])->name('emprestimos.index');
        Route::post('/emprestimos/notificar-atrasados', [NotificacaoController::class, 'todos'])->name('emprestimos.notificar-todos'); // R.F. 12
        Route::get('/emprestimos/{emprestimo}', [EmprestimoAdminController::class, 'show'])->name('emprestimos.show');
        Route::post('/emprestimos/{emprestimo}/devolucao', [EmprestimoAdminController::class, 'devolver'])->name('emprestimos.devolver'); // R.F. 10
        Route::post('/emprestimos/{emprestimo}/pagamento', [EmprestimoAdminController::class, 'pagamento'])->name('emprestimos.pagamento'); // R.F. 11
        Route::post('/emprestimos/{emprestimo}/notificar', [NotificacaoController::class, 'um'])->name('emprestimos.notificar');

        Route::get('/relatorios/emprestimos', [RelatorioController::class, 'index'])->name('relatorios.emprestimos'); // R.F. 17
        Route::get('/relatorios/emprestimos/exportar/{formato}', [RelatorioController::class, 'exportar'])
            ->whereIn('formato', ['csv', 'pdf'])->name('relatorios.exportar');
    });
});
