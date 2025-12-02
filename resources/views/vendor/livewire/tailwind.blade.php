@php
    // Definisci l'elemento di default verso cui fare scroll se non specificato
$scrollTo = $scrollTo ?? 'body';

// Crea uno snippet JS per eseguire lo scroll solo se scrollTo non è false
$scrollIntoViewJsSnippet =
    $scrollTo !== false ? "document.querySelector('{$scrollTo}').scrollIntoView({ behavior: 'smooth' });" : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-end flex-1">
            <div>
                <span class="relative z-0 inline-flex rtl:flex-row-reverse rounded gap-x-2">
                    <span>
                        {{-- Previous Page Link --}}
                        @if ($paginator->onFirstPage())
                            <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                                <span
                                    class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium leading-5 bg-white border rounded-lg cursor-default text-gray-custom-3"
                                    aria-hidden="true">
                                    <flux:icon.chevron-left variant="mini" />
                                </span>
                            </span>
                        @else
                            <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')"
                                x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                                class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium leading-5 transition duration-150 ease-in-out bg-white rounded-lg text-gray-custom-5 hover:bg-azure-custom hover:text-white hover:border-azure-custom focus:outline-none cursor-pointer border"
                                aria-label="{{ __('pagination.previous') }}">
                                <flux:icon.chevron-left variant="mini" />
                            </button>
                        @endif
                    </span>

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span
                                    class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium leading-5 bg-white cursor-default text-gray-custom-3">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                    @if ($page == $paginator->currentPage())
                                        <span aria-current="page">
                                            <span
                                                class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium leading-5 rounded-lg cursor-default bg-azure-custom text-white border border-azure-custom">{{ $page }}</span>
                                        </span>
                                    @else
                                        <button type="button"
                                            wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                            class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium leading-5 transition duration-150 ease-in-out bg-white cursor-pointer rounded-lg border hover:border-azure-custom hover:bg-azure-custom hover:text-white text-gray-custom-5 focus:outline-none"
                                            aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                            {{ $page }}
                                        </button>
                                    @endif
                                </span>
                            @endforeach
                        @endif
                    @endforeach

                    <span>
                        {{-- Next Page Link --}}
                        @if ($paginator->hasMorePages())
                            <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')"
                                x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                                class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium leading-5 transition duration-150 ease-in-out bg-white rounded-lg border cursor-pointer text-gray-custom-5 hover:bg-azure-custom hover:border-azure-custom hover:text-white focus:outline-none"
                                aria-label="{{ __('pagination.next') }}">
                                <flux:icon.chevron-right variant="mini" />
                            </button>
                        @else
                            <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                                <span
                                    class="relative inline-flex items-center justify-center w-8 h-8 text-sm font-medium leading-5 bg-white rounded-lg cursor-default text-gray-custom-3 border"
                                    aria-hidden="true">
                                    <flux:icon.chevron-right variant="mini" />
                                </span>
                            </span>
                        @endif
                    </span>
                </span>
            </div>
        </nav>
    @endif
</div>
