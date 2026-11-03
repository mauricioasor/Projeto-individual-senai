@extends('layouts.app')
@section('titulo', $visaoAdmin ? 'Histórico de '.$usuario->nome : 'Meu histórico')

@section('conteudo')
<div class="page-header">
  <div>
    <h1 class="page-title">{{ $visaoAdmin ? 'Histórico de '.$usuario->nome : 'Meu histórico' }}</h1>
    <p class="page-subtitle">{{ $emprestimos->total() }} empréstimo(s) · {{ $totalLivros }} livro(s) retirado(s) · R$ {{ number_format($totalContribuicoes, 2, ',', '.') }} em contribuições</p>
  </div>
  <div class="page-actions">
    @if ($visaoAdmin)
      <a href="{{ route('admin.usuarios.index') }}" class="btn-mr outline"><i class="bi bi-arrow-left"></i> Voltar</a>
      <a href="{{ route('emprestimos.create', ['usuario' => $usuario->id_usuario]) }}" class="btn-mr primary"><i class="bi bi-plus-lg"></i> Novo empréstimo</a>
    @endif
  </div>
</div>

{{-- R.F. 16 – histórico de empréstimos --}}
<div class="card-mr">
  <div class="table-wrap">
    <table class="table-mr">
      <thead><tr><th>Código</th><th>Livros</th><th>Empréstimo</th><th>Prevista</th><th>Devolução</th><th>Dias de atraso</th><th>Contribuição</th><th>Situação</th></tr></thead>
      <tbody>
      @forelse ($emprestimos as $e)
        @php [$rotulo, $cor] = $e->situacao(); @endphp
        <tr>
          <td>
            @if ($visaoAdmin)<a href="{{ route('admin.emprestimos.show', $e) }}"><code>{{ $e->codigo() }}</code></a>@else<code>{{ $e->codigo() }}</code>@endif
          </td>
          <td>{{ $e->itens->pluck('livro.titulo')->join(', ') }}</td>
          <td>{{ $e->data_emprestimo->format('d/m/Y') }}</td>
          <td>{{ $e->data_prevista_dev->format('d/m/Y') }}</td>
          <td>{{ $e->data_devolucao?->format('d/m/Y') ?? '—' }}</td>
          <td>{{ $e->status === 'ATIVO' ? $e->diasAtraso() : $e->itens->max('dias_atraso') }}</td>
          <td>R$ {{ number_format((float) $e->valor_contribuicao, 2, ',', '.') }}</td>
          <td><span class="badge-mr {{ $cor }}">{{ $rotulo }}</span></td>
        </tr>
      @empty
        <tr><td colspan="8"><div class="vazio"><i class="bi bi-clock-history"></i>Nenhum empréstimo registrado.</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $emprestimos->links() }}
</div>
@endsection
