@php
    use Cloudcake\Documents\Enums\DocumentAuthorshipSetting;
    use Cloudcake\Documents\Enums\DocumentRatingSystemSetting;
    use Cloudcake\Documents\Enums\DocumentMetaSetting;

    $userHasRated = !blank($this->userRating);
@endphp
@if($this->document->setting(DocumentRatingSystemSetting::Enabled) && (auth()->check() || $this->document->setting(DocumentRatingSystemSetting::AllowAnonymous)))
    <div class="rating-system-block font-medium select-none">
        <div class="flex items-center space-x-3">
            <x-filament::icon-button class="inline" color="primary" size="lg" icon="tabler-star-half-filled"/>
            <span class="text-md">
                Was this content helpful to you?<br>
                <span class="text-sm font-light">
                    @if ($this->document->setting(DocumentRatingSystemSetting::ShowRatingCount))
                        @php
                            $totalRatings = $this->record->ratings_total;
                            $prettyRatings = $totalRatings > 0 ? \Illuminate\Support\Number::format($totalRatings) : 'No'
                        @endphp
                        {{ $prettyRatings }} {{ str('rating')->plural($totalRatings)->toString() }}.
                    @endif
                    @if ($this->userRating['label'] ?? false)
                        You rated this content <b>{{ strtolower($this->userRating['label']) }}</b>.
                    @else
                        Please rate this content to help us improve where necessary.
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
                                         color="{{ $shouldHighlight ? 'primary' : 'default' }}"
                                         size="sm"
                                         wire:click="rate({{$option['value']}})"
                                         label="{{$option['label']}}"
                                         :icon="$option['icon']"/>
            @endforeach
        </div>
    </div>
@endif