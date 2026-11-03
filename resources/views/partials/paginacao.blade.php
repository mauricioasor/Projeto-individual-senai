{{-- Paginação no padrão visual do protótipo (R.N.F. 13) --}}
@if ($paginator->hasPages() || $paginator->total() > 0)
  <div class="pagination-mr">
    <span class="pagination-info">
      Exibindo <b>{{ $paginator->firstItem() ?? 0 }}&ndash;{{ $paginator->lastItem() ?? 0 }}</b> de <b>{{ $paginator->total() }}</b>
    </span>
    @if ($paginator->hasPages())
      <nav class="pagination-buttons" aria-label="Paginação">
        @if ($paginator->onFirstPage())
          <span class="pag-btn disabled"><i class="bi bi-chevron-left"></i></span>
        @else
          <a class="pag-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior"><i class="bi bi-chevron-left"></i></a>
        @endif

        @foreach ($elements as $element)
          @if (is_string($element))
            <span class="pag-btn disabled">&hellip;</span>
          @endif
          @if (is_array($element))
            @foreach ($element as $page => $url)
              @if ($page == $paginator->currentPage())
                <span class="pag-btn active" aria-current="page">{{ $page }}</span>
              @else
                <a class="pag-btn" href="{{ $url }}">{{ $page }}</a>
              @endif
            @endforeach
          @endif
        @endforeach

        @if ($paginator->hasMorePages())
          <a class="pag-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Próxima"><i class="bi bi-chevron-right"></i></a>
        @else
          <span class="pag-btn disabled"><i class="bi bi-chevron-right"></i></span>
        @endif
      </nav>
    @endif
  </div>
@endif
