@if ($paginator->hasPages())
    <nav class="flex items-center justify-between">

        <div class="flex items-center gap-6">

            {{-- RESULTS --}}
            <p class="text-sm font-semibold text-gray-500">
                Showing
                <span class="font-black text-gray-900">{{ $paginator->firstItem() }}</span>
                to
                <span class="font-black text-gray-900">{{ $paginator->lastItem() }}</span>
                of
                <span class="font-black text-gray-900">{{ $paginator->total() }}</span>
                results
            </p>

            {{-- PAGINATION --}}
            <div class="inline-flex overflow-hidden rounded-[1.5rem] border border-[#D9D9D9] bg-white shadow-sm">

                {{-- PREVIOUS --}}
                @if ($paginator->onFirstPage())
                    <span class="flex h-12 w-12 items-center justify-center border-r border-[#D9D9D9] text-gray-300">
                        <i data-lucide="chevron-left" class="h-5 w-5"></i>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}"
                       class="flex h-12 w-12 items-center justify-center border-r border-[#D9D9D9] text-gray-600 hover:bg-[#F7F7F7] transition">
                        <i data-lucide="chevron-left" class="h-5 w-5"></i>
                    </a>
                @endif

                {{-- PAGES --}}
                @foreach ($elements as $element)

                    @if (is_string($element))
                        <span class="flex h-12 w-12 items-center justify-center border-r border-[#D9D9D9] text-gray-400">
                            {{ $element }}
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)

                            @if ($page == $paginator->currentPage())

                                <span class="flex h-12 min-w-[48px] items-center justify-center border-r border-[#D9D9D9] bg-[#E5E7EB] px-4 font-black text-gray-900">
                                    {{ $page }}
                                </span>

                            @else

                                <a href="{{ $url }}"
                                   class="flex h-12 min-w-[48px] items-center justify-center border-r border-[#D9D9D9] px-4 font-semibold text-gray-700 hover:bg-[#F7F7F7] transition">
                                    {{ $page }}
                                </a>

                            @endif

                        @endforeach
                    @endif

                @endforeach

                {{-- NEXT --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}"
                       class="flex h-12 w-12 items-center justify-center text-gray-600 hover:bg-[#F7F7F7] transition">
                        <i data-lucide="chevron-right" class="h-5 w-5"></i>
                    </a>
                @else
                    <span class="flex h-12 w-12 items-center justify-center text-gray-300">
                        <i data-lucide="chevron-right" class="h-5 w-5"></i>
                    </span>
                @endif

            </div>

        </div>

    </nav>

    <script>
        if (window.lucide) lucide.createIcons();
    </script>
@endif