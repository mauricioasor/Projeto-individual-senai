@extends('layouts.app')
@section('titulo', 'Empréstimos')

@section('conteudo')
@php
    $abas = ['todos' => ['Todos', 'bi-collection'], 'ativos' => ['Ativos', 'bi-check-circle'], 'atrasados' => ['Atrasados', 'bi-exclamation-triangle'],
             'devolvidos' => ['Devolvidos', 'bi-arrow-counterclockwise'], 'pendentes' => ['Contribuição pendente', 'bi-heart']];
@endphp
<div class="page-header">
  <div>
    <h1 class="page-title">Empréstimos</h1>
    <p class="page-subtitle">Devoluções, prazos, renovações e contribuições em um só lugar.</p>
  </div>
  <div class="page-actions">
    @if ($contagens['atrasados'] > 0)
      <form method="POST" action="{{ route('admin.emprestimos.notificar-todos') }}" data-confirmar="Enviar e-mail de cobrança para todos os empréstimos em atraso?">
        @csrf
        <button type="submit" class="btn-mr outline"><i class="bi bi-bell"></i> Notificar atrasados ({{ $contagens['atrasados'] }})</button>
      </form>
    @endif
    <a href="{{ route('emprestimos.create') }}" class="btn-mr primary"><i class="bi bi-plus-lg"></i> Novo empréstimo</a>
  </div>
</div>

@if ($contagens['atrasados'] > 0)
  <div class="alert-bar warn mb-4"><i class="bi bi-exclamation-triangle-fill"></i>
    <div>Existem <b>{{ $contagens['atrasados'] }} empréstimo(s) atrasado(s)</b> que requerem ação.</div>
  </div>
@endif

<div class="tabs mb-3">
  @foreach ($abas as $chave => [$rotulo, $icone])
    <a href="{{ route('admin.emprestimos.index', array_filter(['aba' => $chave, 'q' => $busca])) }}" class="tab {{ $aba === $chave ? 'active' : '' }}">
      <i class="bi {{ $icone }}"></i> {{ $rotulo }} <span class="count">{{ $contagens[$chave] }}</span>
    </a>
  @endforeach
</div>

<form method="GET" action="{{ route('admin.emprestimos.index') }}" class="filter-row" role="search">
  <input type="hidden" name="aba" value="{{ $aba }}">
  <div class="search">
    <i class="bi bi-search"></i>
    <input type="search" name="q" value="{{ $busca }}" placeholder="Buscar por colaborador, livro ou código…" maxlength="100" aria-label="Buscar">
  </div>
  <button type="submit" class="filter-btn active"><i class="bi bi-funnel"></i> Filtrar</button>
</form>

<div class="card-mr">
  <div class="table-wrap">
    <table class="table-mr">
      <thead><tr><th>Código</th><th>Colaborador</th><th>Livros</th><th>Empréstimo</th><th>Devolução prevista</th><th>Prazo</th><th>Situação</th><th class="text-end">Ações</th></tr></thead>
      <tbody>
      @forelse ($emprestimos as $e)
        @php [$rotulo, $cor] = $e->situacao(); @endphp
        <tr>
          <td><code>{{ $e->codigo() }}</code></td>
          <td><div class="cell-user"><div class="user-avatar">{{ $e->usuario->iniciais() }}</div><div class="cell-stack"><b>{{ $e->usuario->nome }}</b><small>{{ $e->usuario->email }}</small></div></div></td>
          <td>{{ $e->itens->pluck('livro.titulo')->join(', ') }}</td>
          <td>{{ $e->data_emprestimo->format('d/m/Y') }}</td>
          <td>{{ $e->data_prevista_dev->format('d/m/Y') }}</td>
          <td>
            @if ($e->estaAtrasado())
              <span class="badge-mr danger">{{ $e->diasAtraso() }} dia(s) de atraso</span>
            @elseif ($e->status === 'ATIVO')
              <span class="badge-mr success">{{ $e->diasRestantes() }} dia(s) restante(s)</span>
            @else
              <span class="badge-mr neutral">Devolvido em {{ $e->data_devolucao?->format('d/m') }}</span>
            @endif
          </td>
          <td><span class="badge-mr {{ $cor }}">{{ $rotulo }}</span></td>
          <td class="text-end">
            <div class="acoes">
              @if ($e->estaAtrasado())
                <form method="POST" action="{{ route('admin.emprestimos.notificar', $e) }}" class="form-inline">
                  @csrf
                  <button type="submit" class="btn-sm-action primary" title="Notificar por e-mail"><i class="bi bi-bell"></i></button>
                </form>
              @endif
              <a href="{{ route('admin.emprestimos.show', $e) }}" class="btn-sm-action outline" title="Ver e registrar devolução"><i class="bi bi-eye"></i></a>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="8"><div class="vazio"><i class="bi bi-inbox"></i>Nenhum empréstimo encontrado.</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $emprestimos->links() }}
</div>
@endsection
