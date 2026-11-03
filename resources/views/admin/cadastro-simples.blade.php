{{-- Padrão de CRUD simples (R.F. 03 – autores e R.F. 04 – categorias): listagem paginada, busca, inclusão e edição em modal --}}
@extends('layouts.app')
@section('titulo', $titulo)

@section('conteudo')
<div class="page-header">
  <div>
    <h1 class="page-title">{{ $titulo }}</h1>
    <p class="page-subtitle">{{ $registros->total() }} registro(s)</p>
  </div>
  <div class="page-actions">
    <button type="button" class="btn-mr primary" data-bs-toggle="modal" data-bs-target="#modalNovo"><i class="bi bi-plus-lg"></i> Novo {{ $singular }}</button>
  </div>
</div>

<form method="GET" action="{{ route($rotaBase.'.index') }}" class="filter-row" role="search">
  <div class="search">
    <i class="bi bi-search"></i>
    <input type="search" name="q" value="{{ $busca }}" placeholder="Buscar {{ $singular }}…" maxlength="100" aria-label="Buscar">
  </div>
  <button type="submit" class="filter-btn active"><i class="bi bi-funnel"></i> Filtrar</button>
</form>

<div class="card-mr">
  <div class="table-wrap">
    <table class="table-mr">
      <thead><tr><th>Nome</th><th>Livros vinculados</th><th class="text-end">Ações</th></tr></thead>
      <tbody>
      @forelse ($registros as $r)
        <tr>
          <td><b>{{ $r->{$campo} }}</b></td>
          <td>{{ $r->livros_count }}</td>
          <td class="text-end">
            <div class="acoes">
              <button type="button" class="btn-sm-action outline" data-bs-toggle="modal" data-bs-target="#modalEditar{{ $r->getKey() }}" title="Editar"><i class="bi bi-pencil"></i></button>
              <form method="POST" action="{{ route($rotaBase.'.destroy', [$parametro => $r]) }}" class="form-inline" data-confirmar="Excluir &quot;{{ $r->{$campo} }}&quot;?">
                @csrf @method('DELETE')
                <button type="submit" class="btn-sm-action outline" title="Excluir" @disabled($r->livros_count > 0)><i class="bi bi-trash"></i></button>
              </form>
            </div>

            <div class="modal fade text-start" id="modalEditar{{ $r->getKey() }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog"><div class="modal-content">
                <form method="POST" action="{{ route($rotaBase.'.update', [$parametro => $r]) }}">
                  @csrf @method('PUT')
                  <div class="modal-header"><h5 class="modal-title">Editar {{ $singular }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
                  <div class="modal-body">
                    <label class="form-label" for="{{ $campo }}{{ $r->getKey() }}">Nome *</label>
                    <input type="text" id="{{ $campo }}{{ $r->getKey() }}" name="{{ $campo }}" value="{{ $r->{$campo} }}" class="form-control" required maxlength="120">
                  </div>
                  <div class="modal-footer"><button type="button" class="btn-mr outline" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn-mr primary">Salvar</button></div>
                </form>
              </div></div>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="3"><div class="vazio"><i class="bi bi-inbox"></i>Nenhum registro encontrado.</div></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $registros->links() }}
</div>

<div class="modal fade" id="modalNovo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route($rotaBase.'.store') }}">
      @csrf
      <div class="modal-header"><h5 class="modal-title">Novo {{ $singular }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
      <div class="modal-body">
        <label class="form-label" for="novo_{{ $campo }}">Nome *</label>
        <input type="text" id="novo_{{ $campo }}" name="{{ $campo }}" value="{{ old($campo) }}" class="form-control" required maxlength="120">
      </div>
      <div class="modal-footer"><button type="button" class="btn-mr outline" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn-mr primary">Salvar</button></div>
    </form>
  </div></div>
</div>
@endsection
