@extends('layouts.app')
@section('titulo', 'Usuários')

@section('conteudo')
<div class="page-header">
  <div>
    <h1 class="page-title">Usuários</h1>
    <p class="page-subtitle">{{ $total }} cadastrados · {{ $ativos }} ativos · {{ $admins }} administrador(es)</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.usuarios.create') }}" class="btn-mr primary"><i class="bi bi-person-plus"></i> Novo usuário</a>
  </div>
</div>

<form method="GET" action="{{ route('admin.usuarios.index') }}" class="filter-row" role="search">
  <div class="search">
    <i class="bi bi-search"></i>
    <input type="search" name="q" value="{{ $filtros['q'] ?? '' }}" placeholder="Buscar por nome ou e-mail…" maxlength="100" aria-label="Buscar">
  </div>
  <select name="perfil" class="form-select filtro-select" aria-label="Perfil">
    <option value="">Todos os perfis</option>
    <option value="ADMIN" @selected(($filtros['perfil'] ?? null) === 'ADMIN')>Administradores</option>
    <option value="USUARIO" @selected(($filtros['perfil'] ?? null) === 'USUARIO')>Colaboradores</option>
  </select>
  <select name="situacao" class="form-select filtro-select" aria-label="Situação">
    <option value="">Qualquer situação</option>
    <option value="ATIVO" @selected(($filtros['situacao'] ?? null) === 'ATIVO')>Ativos</option>
    <option value="INATIVO" @selected(($filtros['situacao'] ?? null) === 'INATIVO')>Inativos</option>
  </select>
  <button type="submit" class="filter-btn active"><i class="bi bi-funnel"></i> Filtrar</button>
</form>

<div class="users-grid">
  @forelse ($usuarios as $u)
    <div class="user-card-grid {{ $u->atrasados_count || $u->pendentes_count ? 'border-danger' : '' }}">
      <div class="user-card-avatar">{{ $u->iniciais() }}</div>
      <p class="user-card-name">{{ $u->nome }}</p>
      <p class="user-card-email">{{ $u->email }}</p>
      <div class="d-flex justify-content-center gap-1 mb-2 flex-wrap">
        <span class="badge-mr {{ $u->isAdmin() ? 'primary' : 'neutral' }}">{{ $u->isAdmin() ? 'Administrador' : 'Colaborador' }}</span>
        <span class="badge-mr {{ $u->isAtivo() ? 'success' : 'danger' }}">{{ $u->isAtivo() ? 'Ativo' : 'Inativo' }}</span>
        @if ($u->atrasados_count || $u->pendentes_count)<span class="badge-mr danger"><i class="bi bi-exclamation-triangle-fill"></i>Pendência</span>@endif
      </div>
      <div class="user-card-stats">
        <div><div class="user-stat-num">{{ $u->emprestimos_count }}</div><div class="user-stat-label">Empréstimos</div></div>
        <div><div class="user-stat-num">{{ $u->ativos_count }}</div><div class="user-stat-label">Ativos</div></div>
        <div><div class="user-stat-num {{ $u->atrasados_count ? 'text-danger' : '' }}">{{ $u->atrasados_count }}</div><div class="user-stat-label">Atrasos</div></div>
      </div>
      <div class="user-card-acoes">
        <a href="{{ route('admin.usuarios.historico', $u) }}" class="btn-sm-action outline" title="Histórico"><i class="bi bi-clock-history"></i></a>
        <a href="{{ route('admin.usuarios.edit', $u) }}" class="btn-sm-action outline" title="Editar"><i class="bi bi-pencil"></i></a>
        @unless ($u->is(auth()->user()))
          <form method="POST" action="{{ route('admin.usuarios.situacao', $u) }}" class="form-inline">
            @csrf @method('PATCH')
            <button type="submit" class="btn-sm-action outline" title="{{ $u->isAtivo() ? 'Inativar' : 'Reativar' }}"><i class="bi {{ $u->isAtivo() ? 'bi-person-dash' : 'bi-person-check' }}"></i></button>
          </form>
          @if ($u->emprestimos_count === 0)
            <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}" class="form-inline" data-confirmar="Excluir o usuário {{ $u->nome }}?">
              @csrf @method('DELETE')
              <button type="submit" class="btn-sm-action outline" title="Excluir"><i class="bi bi-trash"></i></button>
            </form>
          @endif
        @endunless
      </div>
    </div>
  @empty
    <div class="vazio"><i class="bi bi-people"></i>Nenhum usuário encontrado.</div>
  @endforelse
</div>
<div class="card-mr mt-3">{{ $usuarios->links() }}</div>
@endsection
