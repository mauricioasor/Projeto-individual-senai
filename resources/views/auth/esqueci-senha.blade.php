@extends('layouts.guest')
@section('titulo', 'Recuperar senha')

@section('conteudo')
<div class="login-headline">
  <h1>Recuperar senha</h1>
  <p>Informe seu e-mail. Se ele estiver cadastrado, enviaremos um link válido por {{ config('biblioteca.token_minutos') }} minutos para você criar uma nova senha.</p>
</div>

<form method="POST" action="{{ route('senha.enviar') }}" novalidate>
  @csrf
  <div class="mb-4">
    <label class="form-label small" for="email">E-mail</label>
    <div class="input-group">
      <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
      <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required maxlength="160" autocomplete="email" autofocus>
    </div>
  </div>
  <button type="submit" class="btn btn-primary w-100 fw-semibold mb-3 btn-entrar">Enviar link</button>
  <p class="text-center small mb-0"><a href="{{ route('login') }}" class="fw-semibold text-decoration-none"><i class="bi bi-arrow-left"></i> Voltar ao login</a></p>
</form>
@endsection
