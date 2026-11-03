<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('titulo', 'Painel') · MR Biblioteca</title>
<link href="{{ asset('assets/vendor/inter/inter.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/tokens.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/layout.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
@stack('estilos')
</head>
<body>

@include('layouts.partials.sidebar')
<div class="sidebar-backdrop" data-fechar-menu></div>

<main class="main">
  @include('layouts.partials.topbar')

  <div class="page">
    @include('layouts.partials.alertas')
    @yield('conteudo')
  </div>

  <footer class="rodape">
    MR Biblioteca &copy; {{ now()->year }} &middot; Biblioteca interna &middot; Projeto Individual ADS UniSENAI
  </footer>
</main>

<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
