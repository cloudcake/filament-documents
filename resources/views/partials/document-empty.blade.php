<x-filament::empty-state class="my-20" icon="{{ $icon ?? 'tabler-cloud-exclamation' }}">
    <x-slot name="heading">
        {{ $title ?? "No Content" }}
    </x-slot>
    <x-slot name="description">
        {{ $message ?? "This page doesn't contain any content at the moment." }}
    </x-slot>
    @if($footerMessage ?? false)
        <x-slot name="footer">
            <x-filament::badge color="primary" icon="{{ $footerIcon ?? 'tabler-contract' }}">
                {!! $footerMessage !!}
            </x-filament::badge>
        </x-slot>
    @endif
</x-filament::empty-state>