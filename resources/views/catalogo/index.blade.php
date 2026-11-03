@extends('layouts.app')
@section('titulo', 'Catálogo de livros')

@section('conteudo')
@php $admin = auth()->user()->isAdmin(); @endphp
<div class="page-header">
  <div>
    <h1 class="page-title">Catálogo de livros</h1>
    <p class="page-subtitle">{{ $totalTitulos }} títulos cadastrados · {{ $totalExemplares }} exemplares no acervo</p>
  </div>
  <div class="page-actions">
    @if ($admin)
      <a href="{{ route('admin.livros.create') }}" class="btn-mr primary"><i class="bi bi-plus-lg"></i> Novo livro</a>
    @else
      <a href="{{ route('emprestimos.create') }}" class="btn-mr primary"><i class="bi bi-plus-lg"></i> Novo empréstimo</a>
    @endif
  </div>
</div>

{{-- R.F. 15 – filtros por título, autor, ISBN, categoria e disponibilidade --}}
<form method="GET" action="{{ route('catalogo') }}" class="filter-row" role="search">
  <div class="search">
    <i class="bi bi-search"></i>
    <input type="search" name="q" value="{{ $filtros['q'] ?? '' }}" placeholder="Busque por título, autor ou ISBN…" maxlength="100" aria-label="Buscar">
  </div>
  <select name="categoria" class="form-select filtro-select" aria-label="Categoria">
    <option value="">Todas as categorias</option>
    @foreach ($categorias as $c)
      <option value="{{ $c->id_categoria }}" @selected(($filtros['categoria'] ?? null) == $c->id_categoria)>{{ $c->nome_categoria }}</option>
    @endforeach
  </select>
  <select name="disponibilidade" class="form-select filtro-select" aria-label="Disponibilidade">
    <option value="">Qualquer disponibilidade</option>
    <option value="disponivel" @selected(($filtros['disponibilidade'] ?? null) === 'disponivel')>Com exemplar disponível</option>
    <option value="indisponivel" @selected(($filtros['disponibilidade'] ?? null) === 'indisponivel')>Indisponíveis</option>
  </select>
  <button type="submit" class="filter-btn active"><i class="bi bi-funnel"></i> Filtrar</button>
  @if (array_filter($filtros))
    <a href="{{ route('catalogo') }}" class="filter-btn text-decoration-none"><i class="bi bi-x-lg"></i> Limpar</a>
  @endif
</form>

<div class="card-mr">
  <div class="table-wrap">
    <table class="table-mr">
      <thead>
        <tr><th>Livro</th><th>Autor(es)</th><th>ISBN</th><th>Categoria</th><th>Ano</th><th>Exemplares</th><th>Situação</th><th class="text-end">Ações</th></tr>
      </thead>
      <tbody>
      @forelse ($livros as $livro)
        @php [$rotulo, $cor, $icone] = $livro->situacao(); @endphp
        <tr>
          <td><div class="cell-book"><div class="book-cover tech">{{ mb_substr($livro->titulo, 0, 1) }}</div><div class="cell-stack"><b>{{ $livro->titulo }}</b></div></div></td>
          <td>{{ $livro->nomesAutores() }}</td>
          <td><code class="small text-muted">{{ $livro->isbnFormatado() }}</code></td>
          <td><span class="badge-mr neutral">{{ $livro->categoria->nome_categoria }}</span></td>
          <td>{{ $livro->ano_publicacao ?? '—' }}</td>
          <td><b>{{ $livro->quantidade_disponivel }}</b>/{{ $livro->quantidade_total }}</td>
          <td><span class="badge-mr {{ $cor }}"><i class="bi {{ $icone }}"></i>{{ $rotulo }}</span></td>
          <td class="text-end">
            <div class="acoes">
              @if ($livro->quantidade_disponivel > 0)
                <a href="{{ route('emprestimos.create', ['livro' => $livro->id_livro]) }}" class="btn-sm-action outline" title="Emprestar"><i class="bi bi-bookmark-plus"></i></a>
              @endif
              @if ($admin)
                <a href="{{ route('admin.livros.edit', $livro) }}" class="btn-sm-action outline" title="Editar"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="{{ route('admin.livros.destroy', $livro) }}" class="form-inline" data-confirmar="Excluir o livro &quot;{{ $livro->titulo }}&quot;?">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-sm-action outline" title="Excluir"><i class="bi bi-trash"></i></button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="8"><div class="vazio"><i class="bi bi-search"></i>Nenhum livro encontrado com os filtros informados.</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $livros->links() }}
</div>
@endsection
