@extends('layouts.guest')
@section('titulo', 'Sessão expirada')
@section('conteudo')
<div class="login-headline">
  <h1>Sessão expirada</h1>
  <p>Por segurança, a página expirou. Volte, atualize a página e tente novamente.</p>
</div>
<a href="{{ url('/') }}" class="btn btn-primary w-100 fw-semibold btn-entrar">Voltar ao início</a>
@endsection
