<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('titulo', 'Painel') · MR Biblioteca</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
