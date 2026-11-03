@extends('layouts.app')
@section('titulo', 'Empréstimo '.$emprestimo->codigo())

@section('conteudo')
@php
    [$rotulo, $cor] = $emprestimo->situacao();
    $pendentes = $emprestimo->itens->whereNull('data_devolucao');
    $diasHoje = $emprestimo->diasAtraso();
@endphp
<div class="page-header">
  <div>
    <h1 class="page-title">Empréstimo {{ $emprestimo->codigo() }} <span class="badge-mr {{ $cor }} align-middle">{{ $rotulo }}</span></h1>
    <p class="page-subtitle">{{ $emprestimo->usuario->nome }} · {{ $emprestimo->usuario->email }}</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.emprestimos.index') }}" class="btn-mr outline"><i class="bi bi-arrow-left"></i> Voltar</a>
    @if ($emprestimo->status === 'ATIVO' && ! $emprestimo->estaAtrasado() && $emprestimo->qtd_renovacoes < $maxRenovacoes)
      <form method="POST" action="{{ route('emprestimos.renovar', $emprestimo) }}" class="form-inline">
        @csrf
        <button type="submit" class="btn-mr outline"><i class="bi bi-arrow-repeat"></i> Renovar por {{ config('biblioteca.prazo_dias') }} dias</button>
      </form>
    @endif
    @if ($emprestimo->estaAtrasado())
      <form method="POST" action="{{ route('admin.emprestimos.notificar', $emprestimo) }}" class="form-inline">
        @csrf
        <button type="submit" class="btn-mr outline"><i class="bi bi-bell"></i> Notificar atraso</button>
      </form>
    @endif
  </div>
</div>

<div class="card-mr mb-4">
  <div class="card-mr-header"><div class="card-mr-title-wrap"><h3 class="card-mr-title">Dados do empréstimo</h3></div></div>
  <div class="card-mr-body">
    <dl class="dl-mr">
      <div><dt>Data do empréstimo</dt><dd>{{ $emprestimo->data_emprestimo->format('d/m/Y H:i') }}</dd></div>
      <div><dt>Devolução prevista</dt><dd>{{ $emprestimo->data_prevista_dev->format('d/m/Y') }}</dd></div>
      <div><dt>Renovações</dt><dd>{{ $emprestimo->qtd_renovacoes }} de {{ $maxRenovacoes }}</dd></div>
      <div><dt>Devolução concluída</dt><dd>{{ $emprestimo->data_devolucao?->format('d/m/Y H:i') ?? '—' }}</dd></div>
      <div><dt>Contribuição</dt><dd>R$ {{ number_format((float) $emprestimo->valor_contribuicao, 2, ',', '.') }}</dd></div>
      <div><dt>Pagamento</dt><dd>{{ $emprestimo->data_pagamento_contribuicao?->format('d/m/Y H:i') ?? ((float) $emprestimo->valor_contribuicao > 0 ? 'Pendente' : '—') }}</dd></div>
    </dl>
  </div>
</div>

{{-- R.F. 10 – devolução, livro a livro --}}
<form method="POST" action="{{ route('admin.emprestimos.devolver', $emprestimo) }}" data-confirmar="Confirmar o recebimento dos livros selecionados?">
  @csrf
  <div class="card-mr mb-4">
    <div class="card-mr-header">
      <div class="card-mr-title-wrap"><h3 class="card-mr-title">Livros do empréstimo</h3>
        <p class="card-mr-subtitle">
          @if ($pendentes->isNotEmpty() && $diasHoje > 0)
            Devolução hoje: {{ $diasHoje }} dia(s) de atraso · R$ {{ number_format($diasHoje * $valorDia, 2, ',', '.') }} por livro
          @else
            Marque os livros recebidos para registrar a devolução
          @endif
        </p>
      </div>
      @if ($pendentes->isNotEmpty())
        <button type="submit" class="btn-sm-action primary"><i class="bi bi-box-arrow-in-down"></i> Registrar devolução</button>
      @endif
    </div>
    <div class="table-wrap">
      <table class="table-mr">
        <thead><tr><th style="width:40px"></th><th>Livro</th><th>ISBN</th><th>Devolvido em</th><th>Dias de atraso</th><th>Contribuição</th></tr></thead>
        <tbody>
        @foreach ($emprestimo->itens as $item)
          <tr>
            <td>
              @unless ($item->devolvido())
                <input class="form-check-input" type="checkbox" name="itens[]" value="{{ $item->id_item }}" id="item{{ $item->id_item }}" checked aria-label="Devolver {{ $item->livro->titulo }}">
              @endunless
            </td>
            <td><label for="item{{ $item->id_item }}" class="cell-stack"><b>{{ $item->livro->titulo }}</b><small>{{ $item->livro->nomesAutores() }}</small></label></td>
            <td><code class="small text-muted">{{ $item->livro->isbnFormatado() }}</code></td>
            <td>{!! $item->devolvido() ? e($item->data_devolucao->format('d/m/Y H:i')) : '<span class="badge-mr warning">Em posse</span>' !!}</td>
            <td>{{ $item->devolvido() ? $item->dias_atraso : '—' }}</td>
            <td>{{ $item->devolvido() ? 'R$ '.number_format((float) $item->valor_contribuicao_item, 2, ',', '.') : '—' }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</form>

{{-- R.F. 11 – pagamento da contribuição --}}
@if ($emprestimo->temContribuicaoPendente())
  <div class="card-mr">
    <div class="card-mr-header">
      <div class="card-mr-title-wrap">
        <h3 class="card-mr-title">Contribuição por atraso: R$ {{ number_format((float) $emprestimo->valor_contribuicao, 2, ',', '.') }}</h3>
        <p class="card-mr-subtitle">Valor destinado às ações sociais apoiadas pela empresa</p>
      </div>
      @if ($emprestimo->status === 'DEVOLVIDO')
        <form method="POST" action="{{ route('admin.emprestimos.pagamento', $emprestimo) }}" data-confirmar="Registrar o recebimento da contribuição?">
          @csrf
          <button type="submit" class="btn-sm-action primary"><i class="bi bi-cash-coin"></i> Registrar pagamento</button>
        </form>
      @else
        <small class="text-muted">O pagamento é registrado após a devolução de todos os livros.</small>
      @endif
    </div>
  </div>
@endif
@endsection
