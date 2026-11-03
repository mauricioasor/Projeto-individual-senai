@extends('layouts.guest')
@section('titulo', 'Entrar')

@section('conteudo')
<div class="login-headline">
  <h1>Bem-vindo de volta</h1>
  <p>Entre com seu e-mail corporativo para acessar a biblioteca.</p>
</div>

<form method="POST" action="{{ route('login.store') }}" novalidate>
  @csrf
  <div class="mb-3">
    <label class="form-label small" for="email">E-mail</label>
    <div class="input-group">
      <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
      <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control"
             placeholder="voce&#64;{{ config('biblioteca.dominio_email') }}" autocomplete="email" required maxlength="160" autofocus>
    </div>
  </div>

  <div class="mb-4">
    <div class="d-flex justify-content-between mb-1">
      <label class="form-label small mb-0" for="senha">Senha</label>
      <a href="{{ route('senha.solicitar') }}" class="small fw-semibold text-decoration-none">Esqueceu a senha?</a>
    </div>
    <div class="input-group">
      <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
      <input type="password" id="senha" name="senha" class="form-control" autocomplete="current-password" required maxlength="72">
      <button class="btn btn-outline-secondary" type="button" data-mostrar-senha="senha" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button>
    </div>
  </div>

  <button type="submit" class="btn btn-primary w-100 fw-semibold mb-3 btn-entrar">Entrar</button>

  <p class="text-center small mb-0">Ainda não tem conta? <a href="{{ route('cadastro') }}" class="fw-semibold text-decoration-none">Cadastre-se</a></p>
</form>
@endsection
