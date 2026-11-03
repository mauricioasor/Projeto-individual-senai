@extends('layouts.guest')
@section('titulo', 'Acesso negado')
@section('conteudo')
<div class="login-headline">
  <h1>Acesso negado</h1>
  <p>{{ $exception->getMessage() ?: 'Você não tem permissão para acessar esta página.' }}</p>
</div>
<a href="{{ url('/') }}" class="btn btn-primary w-100 fw-semibold btn-entrar">Voltar ao início</a>
@endsection
