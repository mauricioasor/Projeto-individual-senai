<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Relatório de empréstimos</title>
<style>
  @page { margin: 28px 32px; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0F172A; }
  h1 { font-size: 16px; margin: 0 0 4px; color: #4338CA; }
  .sub { color: #475569; margin-bottom: 12px; }
  .totais { margin-bottom: 12px; }
  .totais span { display: inline-block; margin-right: 18px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #EEF2FF; color: #4338CA; text-align: left; padding: 5px; border-bottom: 1px solid #C7D2FE; }
  td { padding: 4px 5px; border-bottom: 1px solid #E2E8F0; }
  .rodape { margin-top: 14px; font-size: 9px; color: #94A3B8; }
</style>
</head>
<body>
@use('App\Http\Controllers\Admin\RelatorioController')
@use('Illuminate\Support\Carbon')
<h1>MR Biblioteca – Relatório de empréstimos</h1>
<div class="sub">
  Período: {{ Carbon::parse($filtros['inicio'])->format('d/m/Y') }} a {{ Carbon::parse($filtros['fim'])->format('d/m/Y') }}
  @if ($usuario) · Colaborador: {{ $usuario->nome }} @endif
  @if ($categoria) · Categoria: {{ $categoria->nome_categoria }} @endif
  @if (! empty($filtros['status'])) · Situação: {{ ucfirst($filtros['status']) }} @endif
</div>
<div class="totais">
  <span><b>Empréstimos:</b> {{ $totais['emprestimos'] }}</span>
  <span><b>Livros:</b> {{ $totais['livros'] }}</span>
  <span><b>Contribuições arrecadadas:</b> R$ {{ number_format($totais['contribuicoes'], 2, ',', '.') }}</span>
</div>
<table>
  <thead><tr><th>Código</th><th>Colaborador</th><th>Livro</th><th>Categoria</th><th>Empréstimo</th><th>Prevista</th><th>Devolução</th><th>Atraso</th><th>Contribuição</th><th>Situação</th></tr></thead>
  <tbody>
  @forelse ($linhas as $l)
    <tr>
      <td>#{{ str_pad($l->id_emprestimo, 4, '0', STR_PAD_LEFT) }}</td>
      <td>{{ $l->colaborador }}</td>
      <td>{{ $l->titulo }}</td>
      <td>{{ $l->categoria }}</td>
      <td>{{ Carbon::parse($l->data_emprestimo)->format('d/m/Y') }}</td>
      <td>{{ Carbon::parse($l->data_prevista_dev)->format('d/m/Y') }}</td>
      <td>{{ $l->data_devolucao ? Carbon::parse($l->data_devolucao)->format('d/m/Y') : '—' }}</td>
      <td>{{ $l->dias_atraso }}</td>
      <td>R$ {{ number_format((float) $l->valor_contribuicao_item, 2, ',', '.') }}</td>
      <td>{{ RelatorioController::situacao($l) }}</td>
    </tr>
  @empty
    <tr><td colspan="10">Nenhum empréstimo no período e filtros informados.</td></tr>
  @endforelse
  </tbody>
</table>
<div class="rodape">Gerado em {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->nome }}.</div>
</body>
</html>
