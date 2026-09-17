@php
    use Cloudcake\Documents\Enums\DocumentAuthorshipSetting;
    use Cloudcake\Documents\Enums\DocumentRatingSystemSetting;

    if ($writerNameEnabled = $this->document->setting(DocumentAuthorshipSetting::ShowWriter))
         $writerName = $this->record->writer_name;

    if ($editorNamesEnabled = $this->document->setting(DocumentAuthorshipSetting::ShowEditor))
         $editorNames = collect($this->record->editor_names)->filter(fn($editor) => $editor !== $writerName)->all();
@endphp

<div class="text-sm font-medium text-gray-600 dark:text-gray-400/80 select-none mb-2 -mt-6">
    @if($writerNameEnabled)
        Written by <span class="text-primary-500">{{ $writerName }}</span>
        on {{ $this->record->created_at->format('l, F j, Y') }}
    @endif
    @if($editorNamesEnabled && count($editorNames) > 0)
        Edited by {!! $editorNames->map(fn($editorName) => '<span class="ml-1 text-primary-500">' . $editorName . '</span>')->implode(', ') !!}
    @endif
</div>