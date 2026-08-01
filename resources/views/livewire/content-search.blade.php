@php
    $searchIsNotEmpty = str($term)->trim()->isNotEmpty();
    $resultsAreEmpty = empty($results);
    $searchReturnedNothing = $searchIsNotEmpty && $resultsAreEmpty;
@endphp
<div class="w-full ml-10 mx-auto" x-data="handleSearch">
    <button
            class="w-full sm:w-[380px] text-[15px] bg-white text-slate-400 dark:text-slate-500 dark:bg-slate-900/40 dark:border-slate-800 dark:hover:border-slate-700/50 inline-flex items-center justify-between leading-5 pl-3 pr-2 py-[7px] border border-slate-200 hover:border-slate-300 shadow-xs whitespace-nowrap ring-0 rounded-4xl"
            @click.prevent="openSearch"
            @keydown.f3.window.prevent="openSearch"
            aria-controls="search-modal"
    >
        <div class="flex items-center justify-center">
            <svg class="w-4 h-4 fill-slate-500 mr-3 shrink-0 dark:fill-slate-400" width="16"
                 height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                <path
                        d="m14.707 13.293-1.414 1.414-2.4-2.4 1.414-1.414 2.4 2.4ZM6.8 12.6A5.8 5.8 0 1 1 6.8 1a5.8 5.8 0 0 1 0 11.6Zm0-2a3.8 3.8 0 1 0 0-7.6 3.8 3.8 0 0 0 0 7.6Z"/>
            </svg>
            <span>{{ __('cloudcake-documents::documents.search.placeholder') }}</span>
        </div>
        <div
                class="flex items-center justify-center h-5 w-auto px-2 font-medium text-slate-500 rounded-sm border border-slate-200 shadow-xs ml-3 dark:bg-slate-950/5 dark:text-slate-400 dark:border-slate-800">
            F3
        </div>
    </button>
    <div>
        <!-- Modal backdrop -->
        <div
                class="fixed inset-0 bg-slate-950/30 z-50 transition-opacity"
                x-show="searchOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-out duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                aria-hidden="true"
                x-cloak
        ></div>
        <!-- Modal dialog -->
        <div
                id="search-modal"
                class="fixed inset-0 z-50 overflow-hidden flex items-start top-5 md:top-28 justify-center px-4 sm:px-6"
                role="dialog"
                aria-modal="true"
                x-show="searchOpen"
                x-transition:enter="transition ease-in-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in-out duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                x-cloak
        >
            <div
                    class="bg-white dark:bg-slate-900 border border-gray-300 p-3 dark:border-slate-800 overflow-auto max-w-2xl w-full max-h-full rounded-xl shadow-lg "
                    @click.outside="focusInput"
                    @keydown.escape.window="closeSearch"
            >
                <!-- Search form -->
                <form>
                    <div class="flex items-center">
                        <label for="modal-search">
                            <span class="sr-only">{{ __('cloudcake-documents::documents.search.label') }}</span>
                            <svg
                                    wire:loading.delay.remove
                                    class="w-4 h-4 fill-slate-500 shrink-0 ml-4 mr-2 dark:fill-slate-400"
                                    width="16" height="16" viewBox="0 0 16 16"
                                    xmlns="http://www.w3.org/2000/svg">
                                <path
                                        d="m14.707 13.293-1.414 1.414-2.4-2.4 1.414-1.414 2.4 2.4ZM6.8 12.6A5.8 5.8 0 1 1 6.8 1a5.8 5.8 0 0 1 0 11.6Zm0-2a3.8 3.8 0 1 0 0-7.6 3.8 3.8 0 0 0 0 7.6Z"/>
                            </svg>
                            <x-filament::loading-indicator wire:loading.delay class="ml-4 mr-2 h-4 w-4"/>
                        </label>
                        <input
                                wire:model="term"
                                wire:keyup.debounce.1000="search"
                                autocomplete="off"
                                id="modal-search"
                                class="text-sm w-full bg-white border-0 outline-none ring-0 focus:ring-transparent placeholder-slate-400 appearance-none py-3 pl-2 pr-4 dark:bg-slate-900 dark:placeholder:text-slate-500 rounded-xl"
                                type="search" placeholder="{{ __('cloudcake-documents::documents.search.placeholder') }}"
                                x-ref="searchInput"/>
                        <div
                                @click="closeSearch"
                                class="cursor-pointer flex items-center justify-center h-5 w-5 font-medium text-sm text-slate-500 rounded-sm border border-slate-200 shadow-xs px-5 mx-3 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-800">
                            ESC
                        </div>
                    </div>
                </form>
                <script>
                    document.addEventListener('alpine:init', () => {
                        Alpine.data('handleSearch', () => ({
                            searchOpen: false,
                            openSearch() {
                                this.searchOpen = true;
                                document.body.classList.add('overflow-hidden');

                                Alpine.nextTick(() => {
                                    this.focusInput()
                                })
                            },
                            focusInput() {
                                document.querySelector('#modal-search').focus();
                            },
                            closeSearch() {
                                this.searchOpen = false;
                                document.body.classList.remove('overflow-hidden');
                            },
                        }))
                    })
                </script>
                <div class="px-2">
                    @if($searchIsNotEmpty)
                        <div class="py-4 space-y-4 border-t border-slate-200 dark:border-slate-800">
                            @if (!$resultsAreEmpty)
                                <ul>
                                    @foreach($results as $result)
                                        <li>
                                            <a class="outline-hidden"
                                               href="{{ $result->url }}"
                                               wire:navigate
                                            >
                                                <div class="px-2 py-1 leading-6 text-sm text-slate-800 hover:bg-slate-100 rounded-sm dark:text-slate-200 dark:hover:bg-slate-700 focus-within:bg-slate-100 dark:focus-within:bg-slate-700">
                                                    <div class="flex items-center">
                                                        <x-filament::icon
                                                                class="p-0 m-0 text-slate-600 hover:text-purple-500"
                                                                icon="{{ $result->icon ?? 'tabler-book' }}"
                                                                style="margin-inline-end: 12px;"/>
                                                        <span>{{ $result->title }}</span>
                                                    </div>
                                                    <span class="text-xs ml-8" style="margin-inline-end: 12px;">
                                    @foreach($result->crumbs as $index => $crumb)
                                                            <span class="text-slate-500">{{ $crumb }}</span>
                                                            @if ($index < count($result->crumbs)-1)
                                                                <span class="text-slate-700"> / </span>
                                                            @endif
                                                        @endforeach
                                </span>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="px-2">
                                    <span class="text-sm">No results found. Try alternative keywords.</span>
                                    <div>
                                        <span class="text-xs text-slate-500">Searching includes page title and content.</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                    @if (!$searchIsNotEmpty || $resultsAreEmpty)
{{--                        <div--}}
{{--                                class="text-sm font-medium text-slate-500 px-2 mb-2 dark:text-slate-400">--}}
{{--                            Popular--}}
{{--                        </div>--}}
{{--                        <ul>--}}
{{--                            <li>--}}
{{--                                <a class="flex items-center px-2 py-1 leading-6 text-sm text-slate-800 hover:bg-slate-100 rounded-sm dark:text-slate-200 dark:hover:bg-slate-700 focus-within:bg-slate-100 dark:focus-within:bg-slate-700 outline-hidden"--}}
{{--                                   href="#0">--}}
{{--                                    <svg--}}
{{--                                            class="w-3 h-3 fill-slate-400 shrink-0 mr-3 dark:fill-slate-500"--}}
{{--                                            width="12" height="12" viewBox="0 0 12 12"--}}
{{--                                            xmlns="http://www.w3.org/2000/svg">--}}
{{--                                        <path--}}
{{--                                                d="M11.953 4.29a.5.5 0 0 0-.454-.292H6.14L6.984.62A.5.5 0 0 0 6.12.173l-6 7a.5.5 0 0 0 .379.825h5.359l-.844 3.38a.5.5 0 0 0 .864.445l6-7a.5.5 0 0 0 .075-.534Z"/>--}}
{{--                                    </svg>--}}
{{--                                    <span>Alternative Schemas</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                        </ul>--}}
                    @endif
                    <!-- Actions -->
{{--                    <div>--}}
{{--                        <div class="text-sm font-medium text-slate-500 px-2 mb-2">{{ __('cloudcake-documents::documents.search.results.alternative') }}--}}
{{--                        </div>--}}
{{--                        <ul>--}}
{{--                            <li>--}}
{{--                                <a class="flex items-center px-2 py-1 leading-6 text-sm text-slate-800 hover:bg-slate-100 rounded-sm dark:text-slate-200 dark:hover:bg-slate-700 focus-within:bg-slate-100 dark:focus-within:bg-slate-700 outline-hidden"--}}
{{--                                   href="#0">--}}
{{--                                    <svg class="w-3 h-3 fill-purple-600 shrink-0 mr-3"--}}
{{--                                         width="12" height="12" viewBox="0 0 12 12"--}}
{{--                                         xmlns="http://www.w3.org/2000/svg">--}}
{{--                                        <path--}}
{{--                                                d="M11.854.146a.5.5 0 0 0-.525-.116l-11 4a.5.5 0 0 0-.015.934l4.8 1.921 1.921 4.8A.5.5 0 0 0 7.5 12h.008a.5.5 0 0 0 .462-.329l4-11a.5.5 0 0 0-.116-.525Z"/>--}}
{{--                                    </svg>--}}
{{--                                    <span class="font-medium">Contact support</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <a class="flex items-center px-2 py-1 leading-6 text-sm text-slate-800 hover:bg-slate-100 rounded-sm dark:text-slate-200 dark:hover:bg-slate-700 focus-within:bg-slate-100 dark:focus-within:bg-slate-700 outline-hidden"--}}
{{--                                   href="#0">--}}
{{--                                    <svg class="w-3 h-3 fill-purple-500 shrink-0 mr-3"--}}
{{--                                         width="12" height="12" viewBox="0 0 12 12"--}}
{{--                                         xmlns="http://www.w3.org/2000/svg">--}}
{{--                                        <path--}}
{{--                                                d="M6 0C2.691 0 0 2.362 0 5.267c0 2.905 2.691 5.266 6 5.266a6.8 6.8 0 0 0 1.036-.079l2.725 1.485a.5.5 0 0 0 .739-.439V8.711A4.893 4.893 0 0 0 12 5.267C12 2.362 9.309 0 6 0Z"/>--}}
{{--                                    </svg>--}}
{{--                                    <span class="font-medium">Submit feedback</span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                        </ul>--}}
{{--                    </div>--}}
                </div>
            </div>
        </div>
    </div>
</div>