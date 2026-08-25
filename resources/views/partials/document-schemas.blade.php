<div class="mt-4">
    @foreach($this->getSchemas() as $component)
        @if(class_exists($component['type'] ?? ''))
            @livewire($component['type'], ['data' => $component['data']])
        @endif
    @endforeach
</div>