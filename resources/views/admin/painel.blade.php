@extends('layouts.app')
@section('titulo', 'Painel')

@section('conteudo')
<div class="page-header">
  <div>
    <h1 class="page-title">Olá, {{ auth()->user()->primeiroNome() }}</h1>
    <p class="page-subtitle">Resumo da biblioteca em {{ now()->translatedFormat('l, j \d\e F \d\e Y') }}.</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('emprestimos.create') }}" class="btn-mr primary"><i class="bi bi-plus-lg"></i> Novo empréstimo</a>
  </div>
</div>

{{-- R.F. 13 – indicadores --}}
<div class="kpi-grid">
  <div class="kpi">
    <div class="kpi-icon"><i class="bi bi-book"></i></div>
    <div class="kpi-label">Títulos no acervo</div>
    <div class="kpi-value">{{ $totalTitulos }}</div>
    <span class="kpi-delta"><i class="bi bi-collection"></i>{{ $totalExemplares }} exemplares</span>
  </div>
  <div class="kpi">
    <div class="kpi-icon info"><i class="bi bi-people"></i></div>
    <div class="kpi-label">Usuários ativos</div>
    <div class="kpi-value">{{ $usuariosAtivos }}</div>
    <span class="kpi-delta {{ $usuariosComPendencia ? 'neg' : '' }}"><i class="bi bi-exclamation-triangle"></i>{{ $usuariosComPendencia }} com pendência</span>
  </div>
  <div class="kpi">
    <div class="kpi-icon warning"><i class="bi bi-arrow-left-right"></i></div>
    <div class="kpi-label">Empréstimos ativos</div>
    <div class="kpi-value">{{ $emprestimosAtivos }}</div>
    <span class="kpi-delta {{ $emprestimosAtrasados ? 'neg' : '' }}"><i class="bi bi-clock-history"></i>{{ $emprestimosAtrasados }} atrasados</span>
  </div>
  <div class="kpi">
    <div class="kpi-icon success"><i class="bi bi-graph-up-arrow"></i></div>
    <div class="kpi-label">Disponibilidade</div>
    <div class="kpi-value">{{ $disponibilidade }}%</div>
    <span class="kpi-delta"><i class="bi bi-check2-circle"></i>exemplares na estante</span>
  </div>
</div>

<div class="two-col">
  <div class="card-mr">
    <div class="card-mr-header">
      <div class="card-mr-title-wrap">
        <h3 class="card-mr-title">Empréstimos recentes</h3>
        <p class="card-mr-subtitle">Últimos registros</p>
      </div>
      <a href="{{ route('admin.emprestimos.index') }}" class="card-mr-link">Ver todos <i class="bi bi-arrow-right"></i></a>
    </div>
    @forelse ($recentes as $e)
      @php [$rotulo, $cor] = $e->situacao(); @endphp
      <a href="{{ route('admin.emprestimos.show', $e) }}" class="list-item text-decoration-none text-reset">
        <div class="list-avatar">{{ $e->usuario->iniciais() }}</div>
        <div class="list-content">
          <div class="list-title">{{ $e->usuario->nome }}</div>
          <div class="list-subtitle">{{ $e->itens->pluck('livro.titulo')->join(', ') }}</div>
        </div>
        <div class="list-meta">
          <span class="badge-mr {{ $cor }}">{{ $rotulo }}</span>
          <small>{{ $e->data_emprestimo->diffForHumans() }}</small>
        </div>
      </a>
    @empty
      <div class="vazio"><i class="bi bi-inbox"></i>Nenhum empréstimo registrado ainda.</div>
    @endforelse
  </div>

  <div class="card-mr">
    <div class="card-mr-header">
      <div class="card-mr-title-wrap">
        <h3 class="card-mr-title">Mais emprestados</h3>
        <p class="card-mr-subtitle">Neste mês</p>
      </div>
      <a href="{{ route('catalogo') }}" class="card-mr-link">Ver acervo <i class="bi bi-arrow-right"></i></a>
    </div>
    @forelse ($maisEmprestados as $i => $item)
      <div class="list-item">
        <div class="rank-pill {{ $i === 0 ? 'gold' : '' }}">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
        <div class="list-content">
          <div class="list-title">{{ $item->livro->titulo }}</div>
          <div class="list-subtitle">{{ $item->livro->nomesAutores() }}</div>
        </div>
        <div class="list-meta">
          <b>{{ $item->total }} empréstimo(s)</b>
          <small>{{ $item->livro->quantidade_disponivel }}/{{ $item->livro->quantidade_total }} disponíveis</small>
        </div>
      </div>
    @empty
      <div class="vazio"><i class="bi bi-bar-chart"></i>Ainda não há empréstimos neste mês.</div>
    @endforelse
  </div>
</div>

<div class="card-mr mt-4">
  <div class="card-mr-header">
    <div class="card-mr-title-wrap">
      <h3 class="card-mr-title">Empréstimos atrasados</h3>
      <p class="card-mr-subtitle">Requerem ação</p>
    </div>
    @if ($emprestimosAtrasados > 0)
      <form method="POST" action="{{ route('admin.emprestimos.notificar-todos') }}" data-confirmar="Enviar e-mail de cobrança para todos os empréstimos em atraso?">
        @csrf
        <button type="submit" class="btn-sm-action primary"><i class="bi bi-bell"></i> Notificar todos ({{ $emprestimosAtrasados }})</button>
      </form>
    @endif
  </div>
  @forelse ($atrasados as $e)
    <div class="list-item">
      <div class="list-avatar">{{ $e->usuario->iniciais() }}</div>
      <div class="list-content">
        <div class="list-title">{{ $e->usuario->nome }}</div>
        <div class="list-subtitle">{{ $e->itens->whereNull('data_devolucao')->pluck('livro.titulo')->join(', ') }}</div>
      </div>
      <div class="list-meta">
        <span class="badge-mr danger">{{ $e->diasAtraso() }} dia(s) de atraso</span>
        <a href="{{ route('admin.emprestimos.show', $e) }}" class="small">Ver empréstimo</a>
      </div>
    </div>
  @empty
    <div class="vazio"><i class="bi bi-emoji-smile"></i>Nenhum empréstimo em atraso.</div>
  @endforelse
</div>
@endsection
