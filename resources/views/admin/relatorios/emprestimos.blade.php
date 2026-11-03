@extends('layouts.app')
@section('titulo', 'Relatório de empréstimos')

@section('conteudo')
@use('App\Http\Controllers\Admin\RelatorioController')
@php
    $exportar = request()->only(['inicio', 'fim', 'usuario', 'categoria', 'status']);
@endphp
<div class="page-header">
  <div>
    <h1 class="page-title">Relatório de empréstimos</h1>
    <p class="page-subtitle">Período de {{ \Illuminate\Support\Carbon::parse($filtros['inicio'])->format('d/m/Y') }} a {{ \Illuminate\Support\Carbon::parse($filtros['fim'])->format('d/m/Y') }}</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.relatorios.exportar', ['formato' => 'csv'] + $exportar) }}" class="btn-mr outline"><i class="bi bi-filetype-csv"></i> Exportar CSV</a>
    <a href="{{ route('admin.relatorios.exportar', ['formato' => 'pdf'] + $exportar) }}" class="btn-mr outline"><i class="bi bi-filetype-pdf"></i> Gerar PDF</a>
  </div>
</div>

{{-- R.F. 17 – filtros --}}
<form method="GET" action="{{ route('admin.relatorios.emprestimos') }}" class="filter-row">
  <label class="small fw-semibold" for="inicio">De</label>
  <input type="date" id="inicio" name="inicio" value="{{ $filtros['inicio'] }}" class="form-control filtro-data">
  <label class="small fw-semibold" for="fim">até</label>
  <input type="date" id="fim" name="fim" value="{{ $filtros['fim'] }}" class="form-control filtro-data">
  <select name="usuario" class="form-select filtro-select" aria-label="Colaborador">
    <option value="">Todos os colaboradores</option>
    @foreach ($usuarios as $u)
      <option value="{{ $u->id_usuario }}" @selected(($filtros['usuario'] ?? null) == $u->id_usuario)>{{ $u->nome }}</option>
    @endforeach
  </select>
  <select name="categoria" class="form-select filtro-select" aria-label="Categoria">
    <option value="">Todas as categorias</option>
    @foreach ($categorias as $c)
      <option value="{{ $c->id_categoria }}" @selected(($filtros['categoria'] ?? null) == $c->id_categoria)>{{ $c->nome_categoria }}</option>
    @endforeach
  </select>
  <select name="status" class="form-select filtro-select" aria-label="Situação">
    <option value="">Qualquer situação</option>
    <option value="ativo" @selected(($filtros['status'] ?? null) === 'ativo')>Ativo (no prazo)</option>
    <option value="atrasado" @selected(($filtros['status'] ?? null) === 'atrasado')>Atrasado</option>
    <option value="devolvido" @selected(($filtros['status'] ?? null) === 'devolvido')>Devolvido</option>
  </select>
  <button type="submit" class="filter-btn active"><i class="bi bi-funnel"></i> Filtrar</button>
</form>

<div class="kpi-grid">
  <div class="kpi"><div class="kpi-icon"><i class="bi bi-arrow-left-right"></i></div><div class="kpi-label">Empréstimos</div><div class="kpi-value">{{ $totais['emprestimos'] }}</div></div>
  <div class="kpi"><div class="kpi-icon info"><i class="bi bi-book"></i></div><div class="kpi-label">Livros emprestados</div><div class="kpi-value">{{ $totais['livros'] }}</div></div>
  <div class="kpi"><div class="kpi-icon success"><i class="bi bi-heart"></i></div><div class="kpi-label">Contribuições (ações sociais)</div><div class="kpi-value">R$ {{ number_format($totais['contribuicoes'], 2, ',', '.') }}</div></div>
</div>

<div class="card-mr">
  <div class="table-wrap">
    <table class="table-mr">
      <thead><tr><th>Código</th><th>Colaborador</th><th>Livro</th><th>Categoria</th><th>Empréstimo</th><th>Prevista</th><th>Devolução</th><th>Atraso</th><th>Contribuição</th><th>Situação</th></tr></thead>
      <tbody>
      @forelse ($linhas as $l)
        @php $situacao = RelatorioController::situacao($l); @endphp
        <tr>
          <td><a href="{{ route('admin.emprestimos.show', $l->id_emprestimo) }}"><code>#{{ str_pad($l->id_emprestimo, 4, '0', STR_PAD_LEFT) }}</code></a></td>
          <td>{{ $l->colaborador }}</td>
          <td>{{ $l->titulo }}</td>
          <td>{{ $l->categoria }}</td>
          <td>{{ \Illuminate\Support\Carbon::parse($l->data_emprestimo)->format('d/m/Y') }}</td>
          <td>{{ \Illuminate\Support\Carbon::parse($l->data_prevista_dev)->format('d/m/Y') }}</td>
          <td>{{ $l->data_devolucao ? \Illuminate\Support\Carbon::parse($l->data_devolucao)->format('d/m/Y') : '—' }}</td>
          <td>{{ RelatorioController::diasAtraso($l) }}</td>
          <td>R$ {{ number_format((float) $l->valor_contribuicao_item, 2, ',', '.') }}</td>
          <td><span class="badge-mr {{ ['Devolvido' => 'info', 'Atrasado' => 'danger', 'Ativo' => 'success'][$situacao] }}">{{ $situacao }}</span></td>
        </tr>
      @empty
        <tr><td colspan="10"><div class="vazio"><i class="bi bi-file-earmark-bar-graph"></i>Nenhum empréstimo no período e filtros informados.</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $linhas->links() }}
</div>
@endsection
