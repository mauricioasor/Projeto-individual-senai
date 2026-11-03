@extends('layouts.guest')
@section('titulo', 'Criar conta')

@section('conteudo')
<div class="login-headline">
  <h1>Criar conta</h1>
  <p>Use o seu e-mail corporativo (&#64;{{ $dominio }}). A conta é criada com o perfil de colaborador.</p>
</div>

<form method="POST" action="{{ route('cadastro.store') }}" novalidate>
  @csrf
  <div class="mb-3">
    <label class="form-label small" for="nome">Nome completo</label>
    <input type="text" id="nome" name="nome" value="{{ old('nome') }}" class="form-control @error('nome') is-invalid @enderror" required minlength="3" maxlength="120" autocomplete="name">
  </div>
  <div class="mb-3">
    <label class="form-label small" for="email">E-mail corporativo</label>
    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror"
           placeholder="voce&#64;{{ $dominio }}" required maxlength="160" autocomplete="email">
  </div>
  <div class="mb-3">
    <label class="form-label small" for="senha">Senha</label>
    <div class="input-group">
      <input type="password" id="senha" name="senha" class="form-control @error('senha') is-invalid @enderror" required minlength="8" maxlength="72" autocomplete="new-password">
      <button class="btn btn-outline-secondary" type="button" data-mostrar-senha="senha" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button>
    </div>
    <div class="form-text">Mínimo de 8 caracteres, com letras e números.</div>
  </div>
  <div class="mb-4">
    <label class="form-label small" for="senha_confirmation">Confirmar senha</label>
    <input type="password" id="senha_confirmation" name="senha_confirmation" class="form-control" required maxlength="72" autocomplete="new-password">
  </div>

  <button type="submit" class="btn btn-primary w-100 fw-semibold mb-3 btn-entrar">Criar conta</button>
  <p class="text-center small mb-0">Já tem conta? <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Entrar</a></p>
</form>
@endsection
