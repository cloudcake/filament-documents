@php
    use Cloudcake\Documents\Enums\DocumentAuthorshipSetting;
    use Cloudcake\Documents\Enums\DocumentRatingSystemSetting;
    use Cloudcake\Documents\Enums\DocumentMetaSetting;
    use Cloudcake\Documents\Enums\RenderState;
    use Cloudcake\Documents\Models\Content;

    $homePage = Content::getFirstIndex();
    $userHasRated = !blank($this->userRating);
@endphp
<x-filament-panels::page class="document-page">
    @if($this->isRenderState(RenderState::OK))
        @if(blank($this->record))
            <x-filament::empty-state class="my-20" :contained="false" icon="tabler-cloud-exclamation">
                <x-slot name="heading">
                    No Content
                </x-slot>
                <x-slot name="description">
                    The page you requested doesn't exist.
                </x-slot>
                @if ($homePage)
                    <x-slot name="footer">
                        <x-filament::button icon="tabler-home" tag="a" href="{{ $homePage->url }}" wire:navigate>
                            Back to {{ $homePage->title }}
                        </x-filament::button>
                    </x-slot>
                @endif
            </x-filament::empty-state>
        @else
            <div class="cloudcake-document">
                @php
                    if ($writerNameEnabled = $this->document->setting(DocumentAuthorshipSetting::ShowWriter))
                         $writerName = $this->record->writer_name;

                    if ($editorNamesEnabled = $this->document->setting(DocumentAuthorshipSetting::ShowEditor))
                         $editorNames = collect($this->record->editor_names)->filter(fn($editor) => $editor !== $writerName)->all();
                @endphp
                <span class="text-sm font-medium text-gray-500 dark:text-gray-500 select-none">
                @if($writerNameEnabled)
                        Written by <span class="text-primary-500">{{ $writerName }}</span>
                        on {{ $this->record->created_at->format('l, F j, Y') }}
                    @endif
                    @if($editorNamesEnabled && count($editorNames) > 0)
                        &amp; edited
                        by {!! $editorNames->map(fn($editorName) => '<span class="text-primary-500">' . $editorName . '</span>')->implode(', ') !!}
                    @endif
            </span>
                @if (count($this->record->tags ?? []) && $this->document->setting(DocumentMetaSetting::ShowTags))
                    <div class="mt-2">
                        @foreach($this->record->tags as $tag)
                            <x-filament::badge size="sm">
                                #{{ $tag }}
                            </x-filament::badge>
                        @endforeach
                    </div>
                @endif
                @if(blank($this->record->schema))
                    <x-filament::empty-state class="my-20" :contained="false" icon="tabler-cloud-exclamation">
                        <x-slot name="heading">
                            No Content
                        </x-slot>
                        <x-slot name="description">
                            This page doesn't contain any content at the moment.
                        </x-slot>
                        @if ($homePage->id !== $this->record->id)
                            <x-slot name="footer">
                                <x-filament::button icon="tabler-home" tag="a" href="{{ $homePage->url }}"
                                                    wire:navigate>
                                    Back to {{ $homePage->title }}
                                </x-filament::button>
                            </x-slot>
                        @endif
                    </x-filament::empty-state>
                @else
                    <div class="mt-4">
                        @foreach($this->getSchemas() as $component)
                            @if(class_exists($component['type'] ?? ''))
                                @livewire($component['type'], ['data' => $component['data']])
                            @endif
                        @endforeach
                    </div>
                @endif

                @if($this->document->setting(DocumentRatingSystemSetting::Enabled) && (auth()->check() || $this->document->setting(DocumentRatingSystemSetting::AllowAnonymous)))
                    <div class="rating-system-block font-medium select-none">
                        <div class="flex items-center space-x-3">
                            <x-filament::icon-button class="inline"
                                                     color="primary"
                                                     size="lg"
                                                     icon="tabler-star-half-filled"/>
                            <span class="text-md">
                    Was this content helpful to you?<br>
                    <span class="text-sm">
                        @if ($this->document->setting(DocumentRatingSystemSetting::ShowRatingCount))
                            @php
                                $totalRatings = $this->record->ratings_total;
                                $prettyRatings = $totalRatings > 0 ? \Illuminate\Support\Number::format($totalRatings) : 'No'
                            @endphp
                            {{ $prettyRatings }} {{ str('rating')->plural($totalRatings)->toString() }}.
                        @endif
                        @if ($this->userRating['label'] ?? false)
                            You rated this content <b>{{ strtolower($this->userRating['label']) }}</b>.
                        @endif
                    </span>
                    </span>
                        </div>
                        <div class="inline-flex">
                            @php
                                $accumulativeRating = $this->document->setting(DocumentRatingSystemSetting::AccumulativeMode);
                            @endphp
                            @foreach($this->document->setting(DocumentRatingSystemSetting::Options, []) as $option)
                                @php
                                    $userHasRated = ($this->userRating['value'] ?? false) !== false;
                                    $userRating = $userHasRated ? $this->userRating['value'] : 0;
                                    $isUserRating = $userHasRated && $option['value'] === $userRating;

                                    if ($accumulativeRating)
                                        $shouldHighlight = $userHasRated && $userRating >= $option['value'];
                                    else
                                        $shouldHighlight = $userHasRated && $userRating == $option['value'];
                                @endphp
                                <x-filament::icon-button class="ml-3"
                                                         color="{{ $shouldHighlight ? 'primary' : 'gray' }}"
                                                         size="lg"
                                                         wire:click="rate({{$option['value']}})"
                                                         label="{{$option['label']}}"
                                                         :icon="$option['icon']">
                                </x-filament::icon-button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <nav aria-label="Page navigation" class="my-20 w-full border-t border-t-primary-400/10 pt-20">
                    <div class="flex items-center gap-6">

                        @if ($this->previousPage)
                            <a href="{{ $this->previousPage->url }}" wire:navigate
                               class="group flex mr-auto w-full max-w-md items-center gap-5 rounded-2xl border border-gray-300 dark:border-gray-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg pagination-previous-button">

                                <x-filament::icon-button
                                        color="primary"
                                        size="lg"
                                        icon="tabler-arrow-left">
                                </x-filament::icon-button>

                                @php
                                    $previousCategory = $this->previousPage->relationLoaded('category') && $this->previousPage->category ? $this->previousPage->category->name : ($this->previousPage->relationLoaded('parent') && $this->previousPage->parent ? $this->previousPage->parent->title : null);
                                    $hasPreviousCategory = !blank($previousCategory);
                                @endphp
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.15em] text-primary">
                                        Previous @if($hasPreviousCategory)
                                            · {{ $previousCategory }}
                                        @endif
                                    </p>

                                    <h3 class="mt-2 text-lg font-semibold group-hover:text-primary">
                                        {{ $this->previousPage->title }}
                                    </h3>
                                </div>
                            </a>
                        @endif
                        @if ($this->nextPage)
                            <a href="{{ $this->nextPage->url }}" wire:navigate
                               class="group flex ml-auto max-w-md w-full max-w-md items-center justify-end gap-5 rounded-2xl border border-gray-300 dark:border-gray-800 p-6 text-right transition-all duration-300 hover:-translate-y-1 hover:shadow-lg pagination-next-button">

                                @php
                                    $nextCategory = $this->nextPage->relationLoaded('category') && $this->nextPage->category ? $this->nextPage->category->name : ($this->nextPage->relationLoaded('parent') && $this->nextPage->parent ? $this->nextPage->parent->title : null);
                                    $hasNextCategory = !blank($nextCategory);
                                @endphp
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.15em] text-primary">
                                        Next @if($hasNextCategory)
                                            · {{ $nextCategory }}
                                        @endif
                                    </p>

                                    <h3 class="mt-2 text-lg font-semibold group-hover:text-primary">
                                        {{ $this->nextPage->title }}
                                    </h3>
                                </div>

                                <x-filament::icon-button
                                        color="primary"
                                        size="lg"
                                        icon="tabler-arrow-right">
                                </x-filament::icon-button>
                            </a>
                        @endif
                    </div>
                </nav>
            </div>
        @endif
    @elseif ($this->isRenderState(RenderState::NotAllowed))
        <x-filament::empty-state class="my-20" :contained="false" icon-color="danger" icon-size="2xl"
                                 icon="tabler-file-text-shield">
            <x-slot name="heading">
                Sorry. Permission Denied.
            </x-slot>
            <x-slot name="description">
                The content you're trying to access is private & protected.
                If you believe this is an error, or you should have access, please contact the administrator.
            </x-slot>
            <x-slot name="footer">
                <x-filament::badge color="danger" icon="tabler-contract">
                    TRACE {{ strtoupper(str()->uuid()) }}
                </x-filament::badge>
            </x-slot>
        </x-filament::empty-state>
    @elseif ($this->isRenderState(RenderState::NotFound))
        <x-filament::empty-state class="my-20" :contained="false" icon-color="primary" icon-size="2xl"
                                 icon="tabler-file-text-shield">
            <x-slot name="heading">
                Page Not Found
            </x-slot>
            <x-slot name="description">
                The page you requested doesn't exist.
            </x-slot>
            <x-slot name="footer">
                <x-filament::badge color="primary" icon="tabler-contract">
                    TRACE {{ strtoupper(str()->uuid()) }}
                </x-filament::badge>
            </x-slot>
        </x-filament::empty-state>
    @elseif ($this->isRenderState(RenderState::NoContent))
        <x-filament::empty-state class="my-20" :contained="false" icon-color="primary" icon-size="2xl"
                                 icon="tabler-file-text-shield">
            <x-slot name="heading">
                Sorry. No Content.
            </x-slot>
            <x-slot name="description">
                There's nothing for you to view here yet.
            </x-slot>
            <x-slot name="footer">
                <x-filament::badge color="primary" icon="tabler-contract">
                    TRACE {{ strtoupper(str()->uuid()) }}
                </x-filament::badge>
            </x-slot>
        </x-filament::empty-state>
    @endif
</x-filament-panels::page>