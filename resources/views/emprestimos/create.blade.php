@extends('layouts.app')
@section('titulo', 'Novo empréstimo')

@section('conteudo')
@php
    $admin = auth()->user()->isAdmin();
    $selecionados = old('livros', $livroInicial ? [$livroInicial] : ['']);
    $selecionados = $selecionados ?: [''];
    $opcoes = function ($selecionado) use ($livros) {
        $html = '<option value="">Selecione um livro…</option>';
        foreach ($livros as $l) {
            $html .= '<option value="'.$l->id_livro.'"'.((string) $selecionado === (string) $l->id_livro ? ' selected' : '').'>'
                .e($l->titulo).' — '.e($l->nomesAutores()).' ('.$l->quantidade_disponivel.' disp.)</option>';
        }
        return $html;
    };
@endphp

<div class="page-header">
  <div>
    <h1 class="page-title">Novo empréstimo</h1>
    <p class="page-subtitle">Até {{ $maxLivros }} livros por empréstimo · devolução em {{ $prazoDias }} dias ({{ today()->addDays($prazoDias)->format('d/m/Y') }})</p>
  </div>
  <div class="page-actions">
    <a href="{{ $admin ? route('admin.emprestimos.index') : route('painel.colaborador') }}" class="btn-mr outline"><i class="bi bi-arrow-left"></i> Voltar</a>
  </div>
</div>

@if ($pendencias)
  <div class="alert-bar warn mb-4"><i class="bi bi-exclamation-triangle-fill"></i>
    <div>Você {{ implode(' e ', $pendencias) }}. Regularize a situação na biblioteca para fazer novos empréstimos.</div>
  </div>
@endif

{{-- R.F. 08 – formulário mestre-detalhe --}}
<form method="POST" action="{{ route('emprestimos.store') }}" class="form-card" novalidate>
  @csrf

  <div class="card-mr mb-4">
    <div class="card-mr-header"><div class="card-mr-title-wrap"><h3 class="card-mr-title">Dados do empréstimo</h3><p class="card-mr-subtitle">Mestre</p></div></div>
    <div class="card-mr-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="id_usuario">Colaborador *</label>
          @if ($admin)
            <select id="id_usuario" name="id_usuario" class="form-select @error('id_usuario') is-invalid @enderror" required>
              <option value="">Selecione o colaborador…</option>
              @foreach ($colaboradores as $c)
                <option value="{{ $c->id_usuario }}" @selected(old('id_usuario', $colaboradorInicial) == $c->id_usuario)>{{ $c->nome }} — {{ $c->email }}</option>
              @endforeach
            </select>
          @else
            <input type="text" id="id_usuario" class="form-control" value="{{ auth()->user()->nome }}" disabled>
            <div class="form-text">Livros em posse: {{ $emPosse }} de {{ $maxLivros }}.</div>
          @endif
        </div>
        <div class="col-md-3">
          <label class="form-label">Data do empréstimo</label>
          <input type="text" class="form-control" value="{{ today()->format('d/m/Y') }}" disabled>
        </div>
        <div class="col-md-3">
          <label class="form-label">Devolução prevista</label>
          <input type="text" class="form-control" value="{{ today()->addDays($prazoDias)->format('d/m/Y') }}" disabled>
        </div>
      </div>
    </div>
  </div>

  <div class="card-mr mb-4">
    <div class="card-mr-header">
      <div class="card-mr-title-wrap"><h3 class="card-mr-title">Livros do empréstimo</h3><p class="card-mr-subtitle">Detalhe · de 1 a {{ $maxLivros }} livros diferentes</p></div>
      <button type="button" class="btn-sm-action outline" data-detalhe-adicionar><i class="bi bi-plus-lg"></i> Adicionar livro</button>
    </div>
    <div class="card-mr-body">
      @if ($livros->isEmpty())
        <div class="vazio"><i class="bi bi-book"></i>Não há livros com exemplares disponíveis no momento.</div>
      @endif
      <div data-detalhe-lista data-maximo="{{ $maxLivros }}">
        @foreach (array_slice($selecionados, 0, $maxLivros) as $sel)
          <div class="detalhe-linha">
            <span class="num">{{ $loop->iteration }}</span>
            <select name="livros[]" class="form-select" aria-label="Livro">{!! $opcoes($sel) !!}</select>
            <button type="button" class="btn-sm-action outline" data-detalhe-remover title="Remover"><i class="bi bi-trash"></i></button>
          </div>
        @endforeach
      </div>
      <template id="modelo-linha-livro">
        <div class="detalhe-linha">
          <span class="num"></span>
          <select name="livros[]" class="form-select" aria-label="Livro">{!! $opcoes('') !!}</select>
          <button type="button" class="btn-sm-action outline" data-detalhe-remover title="Remover"><i class="bi bi-trash"></i></button>
        </div>
      </template>
    </div>
  </div>

  <div class="form-acoes">
    <a href="{{ $admin ? route('admin.emprestimos.index') : route('painel.colaborador') }}" class="btn-mr outline">Cancelar</a>
    <button type="submit" class="btn-mr primary" @disabled($livros->isEmpty())><i class="bi bi-check-lg"></i> Registrar empréstimo</button>
  </div>
</form>
@endsection
