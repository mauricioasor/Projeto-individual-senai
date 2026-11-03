@if (session('status'))
  <div class="alert-bar success mb-4" role="status">
    <i class="bi bi-check-circle-fill"></i>
    <div>{{ session('status') }}</div>
  </div>
@endif

@if ($errors->any())
  <div class="alert-bar danger mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>
      @if ($errors->count() === 1)
        {{ $errors->first() }}
      @else
        <b>Corrija os pontos abaixo:</b>
        <ul class="mb-0 ps-3">
          @foreach ($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach
        </ul>
      @endif
    </div>
  </div>
@endif
