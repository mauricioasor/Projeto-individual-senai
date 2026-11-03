@extends('layouts.app')
@section('titulo', 'Meu painel')

@section('conteudo')
<div class="page-header">
  <div>
    <h1 class="page-title">Olá, {{ $usuario->primeiroNome() }}</h1>
    <p class="page-subtitle">Seus empréstimos e pendências.</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('catalogo') }}" class="btn-mr outline"><i class="bi bi-search"></i> Buscar no catálogo</a>
    <a href="{{ route('emprestimos.create') }}" class="btn-mr primary"><i class="bi bi-plus-lg"></i> Novo empréstimo</a>
  </div>
</div>

@if ($pendencias)
  <div class="alert-bar warn mb-4"><i class="bi bi-exclamation-triangle-fill"></i>
    <div>Você {{ implode(' e ', $pendencias) }}. Enquanto isso, não é possível fazer novos empréstimos nem renovações.</div>
  </div>
@endif

{{-- R.F. 14 – painel do colaborador --}}
<div class="kpi-grid">
  <div class="kpi">
    <div class="kpi-icon"><i class="bi bi-book"></i></div>
    <div class="kpi-label">Livros em posse</div>
    <div class="kpi-value">{{ $emPosse }} / {{ $maxLivros }}</div>
    <span class="kpi-delta"><i class="bi bi-info-circle"></i>limite de {{ $maxLivros }} livros</span>
  </div>
  <div class="kpi">
    <div class="kpi-icon warning"><i class="bi bi-calendar-event"></i></div>
    <div class="kpi-label">Próxima devolução</div>
    <div class="kpi-value">{{ $proxima ? $proxima->format('d/m') : '—' }}</div>
    <span class="kpi-delta {{ $proxima && $proxima->isPast() ? 'neg' : '' }}"><i class="bi bi-clock"></i>
      @if (! $proxima) sem empréstimos @elseif ($proxima->lt(today())) em atraso @else em {{ (int) today()->diffInDays($proxima) }} dia(s) @endif
    </span>
  </div>
  <div class="kpi">
    <div class="kpi-icon {{ $contribuicaoPendente > 0 ? 'danger' : 'success' }}"><i class="bi bi-heart"></i></div>
    <div class="kpi-label">Contribuição pendente</div>
    <div class="kpi-value">R$ {{ number_format($contribuicaoPendente, 2, ',', '.') }}</div>
    <span class="kpi-delta {{ $contribuicaoPendente > 0 ? 'neg' : '' }}"><i class="bi bi-people"></i>destinada a ações sociais</span>
  </div>
</div>

<div class="card-mr">
  <div class="card-mr-header">
    <div class="card-mr-title-wrap">
      <h3 class="card-mr-title">Meus empréstimos em aberto</h3>
      <p class="card-mr-subtitle">Prazo de {{ config('biblioteca.prazo_dias') }} dias, com uma renovação</p>
    </div>
    <a href="{{ route('historico.meu') }}" class="card-mr-link">Ver histórico <i class="bi bi-arrow-right"></i></a>
  </div>
  <div class="table-wrap">
    <table class="table-mr">
      <thead><tr><th>Livro</th><th>Retirada</th><th>Devolução prevista</th><th>Prazo</th><th class="text-end">Ação</th></tr></thead>
      <tbody>
      @forelse ($emAberto as $e)
        @foreach ($e->itens as $item)
          <tr>
            <td><div class="cell-stack"><b>{{ $item->livro->titulo }}</b><small>{{ $item->livro->nomesAutores() }}</small></div></td>
            <td>{{ $e->data_emprestimo->format('d/m/Y') }}</td>
            <td>{{ $e->data_prevista_dev->format('d/m/Y') }}</td>
            <td>
              @if ($e->estaAtrasado())
                <span class="badge-mr danger">{{ $e->diasAtraso() }} dia(s) de atraso</span>
              @else
                <span class="badge-mr success">{{ $e->diasRestantes() }} dia(s) restante(s)</span>
              @endif
            </td>
            <td class="text-end">
              @if ($loop->first)
                @if ($e->qtd_renovacoes < config('biblioteca.max_renovacoes') && ! $e->estaAtrasado())
                  <form method="POST" action="{{ route('emprestimos.renovar', $e) }}" class="form-inline">
                    @csrf
                    <button type="submit" class="btn-sm-action outline"><i class="bi bi-arrow-repeat"></i> Renovar</button>
                  </form>
                @else
                  <small class="text-muted">{{ $e->estaAtrasado() ? 'Devolva na biblioteca' : 'Já renovado' }}</small>
                @endif
              @endif
            </td>
          </tr>
        @endforeach
      @empty
        <tr><td colspan="5"><div class="vazio"><i class="bi bi-book"></i>Você não tem livros em posse. Que tal escolher um no catálogo?</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
