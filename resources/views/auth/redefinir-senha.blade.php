@extends('layouts.guest')
@section('titulo', 'Nova senha')

@section('conteudo')
<div class="login-headline">
  <h1>Criar nova senha</h1>
  @if ($valido)
    <p>Escolha uma nova senha com no mínimo 8 caracteres, com letras e números.</p>
  @endif
</div>

@if (! $valido)
  <div class="alert-bar warn mb-3"><i class="bi bi-exclamation-triangle-fill"></i>
    <div>Este link é inválido, já foi utilizado ou expirou. Solicite uma nova recuperação de senha.</div>
  </div>
  <a href="{{ route('senha.solicitar') }}" class="btn btn-primary w-100 fw-semibold btn-entrar">Solicitar novo link</a>
@else
  <form method="POST" action="{{ route('senha.atualizar') }}" novalidate>
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="mb-3">
      <label class="form-label small" for="senha">Nova senha</label>
      <div class="input-group">
        <input type="password" id="senha" name="senha" class="form-control" required minlength="8" maxlength="72" autocomplete="new-password" autofocus>
        <button class="btn btn-outline-secondary" type="button" data-mostrar-senha="senha" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button>
      </div>
    </div>
    <div class="mb-4">
      <label class="form-label small" for="senha_confirmation">Confirmar nova senha</label>
      <input type="password" id="senha_confirmation" name="senha_confirmation" class="form-control" required maxlength="72" autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn-primary w-100 fw-semibold btn-entrar">Salvar nova senha</button>
  </form>
@endif
@endsection
