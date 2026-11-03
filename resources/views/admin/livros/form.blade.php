@extends('layouts.app')
@php $editando = $livro->exists; @endphp
@section('titulo', $editando ? 'Editar livro' : 'Novo livro')

@section('conteudo')
<div class="page-header">
  <div>
    <h1 class="page-title">{{ $editando ? 'Editar livro' : 'Novo livro' }}</h1>
    <p class="page-subtitle">{{ $editando ? $livro->titulo : 'Cadastro de um título no acervo' }}</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('catalogo') }}" class="btn-mr outline"><i class="bi bi-arrow-left"></i> Voltar ao catálogo</a>
  </div>
</div>

<div class="card-mr form-card">
  <div class="card-mr-body">
    <form method="POST" action="{{ $editando ? route('admin.livros.update', $livro) : route('admin.livros.store') }}" novalidate>
      @csrf
      @if ($editando) @method('PUT') @endif

      <div class="mb-3">
        <label class="form-label" for="titulo">Título *</label>
        <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $livro->titulo) }}" class="form-control @error('titulo') is-invalid @enderror" required maxlength="200">
        @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label" for="isbn">ISBN *</label>
          <input type="text" id="isbn" name="isbn" value="{{ old('isbn', $livro->isbn) }}" class="form-control @error('isbn') is-invalid @enderror" required maxlength="17" placeholder="Somente números (10 ou 13 dígitos)">
          @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label" for="ano_publicacao">Ano</label>
          <input type="number" id="ano_publicacao" name="ano_publicacao" value="{{ old('ano_publicacao', $livro->ano_publicacao) }}" class="form-control @error('ano_publicacao') is-invalid @enderror" min="1450" max="{{ now()->year + 1 }}">
          @error('ano_publicacao')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
          <label class="form-label" for="quantidade_total">Exemplares *</label>
          <input type="number" id="quantidade_total" name="quantidade_total" value="{{ old('quantidade_total', $livro->quantidade_total ?? 1) }}" class="form-control @error('quantidade_total') is-invalid @enderror" required min="1" max="999">
          @error('quantidade_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      @if ($editando)
        <p class="form-text mb-3"><i class="bi bi-info-circle"></i> Disponíveis agora: <b>{{ $livro->quantidade_disponivel }}</b> de {{ $livro->quantidade_total }}. Ao alterar o total, os disponíveis acompanham a diferença.</p>
      @endif

      <div class="mb-3">
        <label class="form-label" for="id_categoria">Categoria *</label>
        <select id="id_categoria" name="id_categoria" class="form-select @error('id_categoria') is-invalid @enderror" required>
          <option value="">Selecione…</option>
          @foreach ($categorias as $c)
            <option value="{{ $c->id_categoria }}" @selected(old('id_categoria', $livro->id_categoria) == $c->id_categoria)>{{ $c->nome_categoria }}</option>
          @endforeach
        </select>
        @error('id_categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Não encontrou? <a href="{{ route('admin.categorias.index') }}">Cadastre a categoria</a>.</div>
      </div>

      @php $selecionados = collect(old('autores', $livro->exists ? $livro->autores->pluck('id_autor')->all() : []))->map(fn ($v) => (int) $v); @endphp
      <div class="mb-4">
        <label class="form-label">Autor(es) *</label>
        <div class="autores-lista @error('autores') border-danger @enderror">
          @forelse ($autores as $a)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="autores[]" value="{{ $a->id_autor }}" id="autor{{ $a->id_autor }}" @checked($selecionados->contains($a->id_autor))>
              <label class="form-check-label" for="autor{{ $a->id_autor }}">{{ $a->nome_autor }}</label>
            </div>
          @empty
            <p class="text-muted small mb-0">Nenhum autor cadastrado.</p>
          @endforelse
        </div>
        @error('autores')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        <div class="form-text">Um livro pode ter vários autores. Não encontrou? <a href="{{ route('admin.autores.index') }}">Cadastre o autor</a>.</div>
      </div>

      <div class="form-acoes">
        <a href="{{ route('catalogo') }}" class="btn-mr outline">Cancelar</a>
        <button type="submit" class="btn-mr primary"><i class="bi bi-check-lg"></i> Salvar</button>
      </div>
    </form>
  </div>
</div>
@endsection
