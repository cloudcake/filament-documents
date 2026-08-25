<nav aria-label="Page navigation" class="my-10 w-full border-t border-t-primary-400/10 pt-20">
    <div class="flex items-center gap-6">

        @if ($this->previousPage)
            <a href="{{ $this->previousPage->url }}" wire:navigate
               class="group pagination-button pagination-previous-button">

                <x-filament::icon-button color="primary" size="lg" icon="tabler-arrow-left"/>

                @php
                    $previousCategory = $this->previousPage->relationLoaded('category') && $this->previousPage->category
                        ? $this->previousPage->category->name
                        : ($this->previousPage->relationLoaded('parent') && $this->previousPage->parent
                            ? $this->previousPage->parent->title
                            : null);
                @endphp

                <div>
                    <p class="pagination-category">
                        {{ !blank($previousCategory) ? $previousCategory : 'Previous' }}
                    </p>

                    <h3 class="pagination-title">
                        {{ $this->previousPage->title }}
                    </h3>
                </div>
            </a>
        @endif
        @if ($this->nextPage)
            <a href="{{ $this->nextPage->url }}" wire:navigate class="group pagination-button pagination-next-button">

                @php
                    $nextCategory = $this->nextPage->relationLoaded('category') && $this->nextPage->category
                        ? $this->nextPage->category->name
                        : ($this->nextPage->relationLoaded('parent') && $this->nextPage->parent
                            ? $this->nextPage->parent->title
                            : null);
                @endphp

                <div>
                    <p class="pagination-category">
                        {{ !blank($nextCategory) ? $nextCategory : 'Next' }}
                    </p>

                    <h3 class="pagination-title">
                        {{ $this->nextPage->title }}
                    </h3>
                </div>

                <x-filament::icon-button color="primary" size="lg" icon="tabler-arrow-right"/>
            </a>
        @endif
    </div>
</nav>