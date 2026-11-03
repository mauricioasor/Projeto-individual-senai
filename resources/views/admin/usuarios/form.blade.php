@extends('layouts.app')
@php $editando = $usuario->exists; @endphp
@section('titulo', $editando ? 'Editar usuário' : 'Novo usuário')

@section('conteudo')
<div class="page-header">
  <div>
    <h1 class="page-title">{{ $editando ? 'Editar usuário' : 'Novo usuário' }}</h1>
    <p class="page-subtitle">{{ $editando ? $usuario->email : 'Cadastro de colaborador ou administrador' }}</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.usuarios.index') }}" class="btn-mr outline"><i class="bi bi-arrow-left"></i> Voltar</a>
  </div>
</div>

<div class="card-mr form-card">
  <div class="card-mr-body">
    <form method="POST" action="{{ $editando ? route('admin.usuarios.update', $usuario) : route('admin.usuarios.store') }}" novalidate>
      @csrf
      @if ($editando) @method('PUT') @endif

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label" for="nome">Nome completo *</label>
          <input type="text" id="nome" name="nome" value="{{ old('nome', $usuario->nome) }}" class="form-control @error('nome') is-invalid @enderror" required maxlength="120">
          @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
          <label class="form-label" for="email">E-mail corporativo *</label>
          <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" class="form-control @error('email') is-invalid @enderror" required maxlength="160">
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label" for="tipo_perfil">Perfil *</label>
          <select id="tipo_perfil" name="tipo_perfil" class="form-select @error('tipo_perfil') is-invalid @enderror">
            <option value="USUARIO" @selected(old('tipo_perfil', $usuario->tipo_perfil) === 'USUARIO')>Colaborador (Usuário)</option>
            <option value="ADMIN" @selected(old('tipo_perfil', $usuario->tipo_perfil) === 'ADMIN')>Administrador</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="situacao">Situação *</label>
          <select id="situacao" name="situacao" class="form-select @error('situacao') is-invalid @enderror">
            <option value="ATIVO" @selected(old('situacao', $usuario->situacao) === 'ATIVO')>Ativo</option>
            <option value="INATIVO" @selected(old('situacao', $usuario->situacao) === 'INATIVO')>Inativo</option>
          </select>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label" for="senha">{{ $editando ? 'Nova senha' : 'Senha *' }}</label>
          <input type="password" id="senha" name="senha" class="form-control @error('senha') is-invalid @enderror" {{ $editando ? '' : 'required' }} maxlength="72" autocomplete="new-password">
          @error('senha')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <div class="form-text">{{ $editando ? 'Deixe em branco para manter a senha atual. ' : '' }}Mínimo de 8 caracteres, com letras e números.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="senha_confirmation">Confirmar senha</label>
          <input type="password" id="senha_confirmation" name="senha_confirmation" class="form-control" maxlength="72" autocomplete="new-password">
        </div>
      </div>

      <div class="form-acoes">
        <a href="{{ route('admin.usuarios.index') }}" class="btn-mr outline">Cancelar</a>
        <button type="submit" class="btn-mr primary"><i class="bi bi-check-lg"></i> Salvar</button>
      </div>
    </form>
  </div>
</div>
@endsection
