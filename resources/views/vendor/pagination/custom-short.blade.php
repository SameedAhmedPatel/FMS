@if ($paginator->hasPages())
    <nav>
        <ul class="pagination justify-content-center">

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">&laquo; Prev</span>
                </li>
            @else
                <li class="page-item">
                    <a href="#" class="page-link"
                       wire:click.prevent="previousPage"
                       wire:loading.attr="disabled">
                        &laquo; Prev
                    </a>
                </li>
            @endif


            {{-- Page 1 --}}
            <li class="page-item {{ $paginator->currentPage() == 1 ? 'active' : '' }}">
                <a href="#" class="page-link" wire:click.prevent="gotoPage(1)">1</a>
            </li>

            {{-- Page 2 --}}
            @if ($paginator->lastPage() > 2)
                <li class="page-item {{ $paginator->currentPage() == 2 ? 'active' : '' }}">
                    <a href="#" class="page-link" wire:click.prevent="gotoPage(2)">2</a>
                </li>
            @endif


            {{-- Dots --}}
            @if ($paginator->lastPage() > 4)
                <li class="page-item disabled"><span class="page-link">…</span></li>
            @endif


            {{-- Second Last Page --}}
            @if ($paginator->lastPage() - 1 > 2)
                <li class="page-item {{ $paginator->currentPage() == $paginator->lastPage() - 1 ? 'active' : '' }}">
                    <a href="#" class="page-link"
                       wire:click.prevent="gotoPage({{ $paginator->lastPage() - 1 }})">
                        {{ $paginator->lastPage() - 1 }}
                    </a>
                </li>
            @endif


            {{-- Last Page --}}
            @if ($paginator->lastPage() > 1)
                <li class="page-item {{ $paginator->currentPage() == $paginator->lastPage() ? 'active' : '' }}">
                    <a href="#" class="page-link"
                       wire:click.prevent="gotoPage({{ $paginator->lastPage() }})">
                        {{ $paginator->lastPage() }}
                    </a>
                </li>
            @endif


            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a href="#" class="page-link"
                       wire:click.prevent="nextPage"
                       wire:loading.attr="disabled">
                        Next &raquo;
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">Next &raquo;</span>
                </li>
            @endif

        </ul>
    </nav>
@endif
