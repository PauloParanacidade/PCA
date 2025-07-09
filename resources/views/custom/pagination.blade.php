@if ($paginator->hasPages())
    <nav aria-label="Navegação de páginas">
        <ul class="pagination justify-content-center">
            {{-- Link para página anterior --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">‹ Anterior</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Anterior</a></li>
            @endif

            {{-- Links das páginas --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Link para próxima página --}}
            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Próxima ›</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">Próxima ›</span></li>
            @endif
        </ul>
    </nav>

    {{-- Informações da paginação --}}
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
        </div>
        <div class="text-muted">
            Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
        </div>
    </div>
@endif