@php
    $usuarioLogado = auth()->user();
    $ativo = fn (string ...$rotas) => request()->routeIs(...$rotas) ? 'active' : '';
@endphp
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">MR</div>
    <div class="sidebar-brand">
      <b>MR Biblioteca</b>
      <small>Sistema de Gestão</small>
    </div>
    <button type="button" class="toggle-btn" data-alternar-menu title="Recolher menu" aria-label="Recolher menu">
      <i class="bi bi-chevron-double-left"></i>
    </button>
  </div>

  @if ($usuarioLogado->isAdmin())
    <div class="sidebar-section">Menu principal</div>
    <nav class="sidebar-nav">
      <a href="{{ route('admin.painel') }}" class="menu-item {{ $ativo('admin.painel') }}" title="Painel">
        <i class="bi bi-speedometer2"></i><span>Painel</span>
      </a>
      <a href="{{ route('catalogo') }}" class="menu-item {{ $ativo('catalogo', 'admin.livros.*') }}" title="Catálogo de livros">
        <i class="bi bi-book"></i><span>Catálogo de livros</span>
      </a>
      <a href="{{ route('admin.emprestimos.index') }}" class="menu-item {{ $ativo('admin.emprestimos.*', 'emprestimos.*') }}" title="Empréstimos">
        <i class="bi bi-arrow-left-right"></i><span>Empréstimos</span>
        @if ($qtdAtrasados > 0)<span class="menu-badge" title="Empréstimos em atraso">{{ $qtdAtrasados }}</span>@endif
      </a>
      <a href="{{ route('admin.usuarios.index') }}" class="menu-item {{ $ativo('admin.usuarios.*') }}" title="Usuários">
        <i class="bi bi-people"></i><span>Usuários</span>
      </a>
    </nav>

    <div class="sidebar-section">Cadastros</div>
    <nav class="sidebar-nav">
      <a href="{{ route('admin.autores.index') }}" class="menu-item {{ $ativo('admin.autores.*') }}" title="Autores">
        <i class="bi bi-person-lines-fill"></i><span>Autores</span>
      </a>
      <a href="{{ route('admin.categorias.index') }}" class="menu-item {{ $ativo('admin.categorias.*') }}" title="Categorias">
        <i class="bi bi-tags"></i><span>Categorias</span>
      </a>
    </nav>

    <div class="sidebar-section">Relatórios</div>
    <nav class="sidebar-nav">
      <a href="{{ route('admin.relatorios.emprestimos') }}" class="menu-item {{ $ativo('admin.relatorios.*') }}" title="Relatório de empréstimos">
        <i class="bi bi-file-earmark-bar-graph"></i><span>Relatório de empréstimos</span>
      </a>
    </nav>
  @else
    <div class="sidebar-section">Menu</div>
    <nav class="sidebar-nav">
      <a href="{{ route('painel.colaborador') }}" class="menu-item {{ $ativo('painel.colaborador') }}" title="Meu painel">
        <i class="bi bi-house"></i><span>Meu painel</span>
      </a>
      <a href="{{ route('catalogo') }}" class="menu-item {{ $ativo('catalogo') }}" title="Catálogo de livros">
        <i class="bi bi-book"></i><span>Catálogo de livros</span>
      </a>
      <a href="{{ route('emprestimos.create') }}" class="menu-item {{ $ativo('emprestimos.create') }}" title="Novo empréstimo">
        <i class="bi bi-plus-circle"></i><span>Novo empréstimo</span>
      </a>
      <a href="{{ route('historico.meu') }}" class="menu-item {{ $ativo('historico.meu') }}" title="Meu histórico">
        <i class="bi bi-clock-history"></i><span>Meu histórico</span>
      </a>
    </nav>
  @endif

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">{{ $usuarioLogado->iniciais() }}</div>
      <div class="user-info">
        <b>{{ $usuarioLogado->primeiroNome() }}</b>
        <small>{{ $usuarioLogado->isAdmin() ? 'Administrador' : 'Colaborador' }}</small>
      </div>
      <form method="POST" action="{{ route('logout') }}" class="ms-auto">
        @csrf
        <button type="submit" class="logout-btn" title="Sair" aria-label="Sair"><i class="bi bi-box-arrow-right"></i></button>
      </form>
    </div>
  </div>
</aside>
