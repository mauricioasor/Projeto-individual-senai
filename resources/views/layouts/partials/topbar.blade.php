@php $usuarioLogado = auth()->user(); @endphp
<div class="topbar">
  <button type="button" class="btn-icon btn-menu-mobile" data-abrir-menu aria-label="Abrir menu">
    <i class="bi bi-list"></i>
  </button>
  <div class="topbar-actions">
    @can('admin')
      <a href="{{ route('admin.emprestimos.index', ['aba' => 'atrasados']) }}" class="btn-icon" title="Empréstimos em atraso">
        <i class="bi bi-bell"></i>
        @if (($qtdAtrasados ?? 0) > 0)<span class="dot"></span>@endif
      </a>
    @endcan
    <div class="topbar-user-wrap">
      <div class="user-avatar">{{ $usuarioLogado->iniciais() }}</div>
      <div class="topbar-user-info">
        <b>{{ $usuarioLogado->nome }}</b>
        <small>{{ $usuarioLogado->isAdmin() ? 'Administrador' : 'Colaborador' }}</small>
      </div>
    </div>
  </div>
</div>
