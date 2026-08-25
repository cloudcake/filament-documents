@php
    use Cloudcake\Documents\Enums\DocumentMetaSetting;
@endphp
@if (count($this->record->tags ?? []) && $this->document->setting(DocumentMetaSetting::ShowTags))
    <div>
        @foreach($this->record->tags as $tag)
            <x-filament::badge size="sm">
                #{{ $tag }}
            </x-filament::badge>
        @endforeach
    </div>
@endif