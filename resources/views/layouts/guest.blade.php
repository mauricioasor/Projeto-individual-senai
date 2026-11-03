<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('titulo', 'Entrar') · MR Biblioteca</title>
<link href="{{ asset('assets/vendor/inter/inter.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body>
<div class="login-wrap">
  <div class="login-blob blob-1"></div>
  <div class="login-blob blob-2"></div>

  <div class="login-card">
    <div class="login-brand-top">
      <div class="logo">MR</div>
      <div>
        <div class="brand-name">MR Biblioteca</div>
        <div class="brand-sub">Biblioteca interna</div>
      </div>
    </div>

    @if (session('status'))
      <div class="alert-bar success mb-3" role="status"><i class="bi bi-check-circle-fill"></i><div>{{ session('status') }}</div></div>
    @endif
    @if ($errors->any())
      <div class="alert-bar danger mb-3" role="alert"><i class="bi bi-exclamation-triangle-fill"></i>
        <div>@foreach ($errors->all() as $erro)<div>{{ $erro }}</div>@endforeach</div>
      </div>
    @endif

    @yield('conteudo')
  </div>

  <div class="login-footer">
    <div><i class="bi bi-shield-lock"></i> Acesso restrito aos colaboradores da empresa</div>
    <div>&copy; {{ now()->year }} MR Biblioteca</div>
  </div>
</div>
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
