@php
    use Cloudcake\Documents\Enums\DocumentAuthorshipSetting;
    use Cloudcake\Documents\Enums\DocumentRatingSystemSetting;
    use Cloudcake\Documents\Enums\DocumentMetaSetting;
    use Cloudcake\Documents\Enums\RenderState;
    use Cloudcake\Documents\Models\Content;
@endphp
<x-filament-panels::page class="document-page">
        <div class="cloudcake-document">

            @if(blank($this->record) || blank($this->record->schema))
                @include('cloudcake-documents::partials.document-empty')

            @elseif ($this->isRenderState(RenderState::NotAllowed))
                @include('cloudcake-documents::partials.document-empty', [
                    'icon' => 'tabler-file-text-shield',
                    'title' => 'Sorry! Permission Denied!',
                    'message' => "The content you're trying to access is private & protected. If you believe this is an error, or you should have access, please contact the administrator.",
                    'footerMessage' => 'TRACE ' . strtoupper(str()->uuid())
                ])

            @elseif ($this->isRenderState(RenderState::NotFound))
                @include('cloudcake-documents::partials.document-empty', [
                   'icon' => 'tabler-file-text-shield',
                   'title' => ' Page Not Found',
                   'message' => "The page you requested doesn't exist. If you believe this is an error, or you should have access, please contact the administrator.",
                   'footerMessage' => 'TRACE ' . strtoupper(str()->uuid())
               ])

            @elseif ($this->isRenderState(RenderState::NoContent))
                @include('cloudcake-documents::partials.document-empty', [
                  'icon' => 'tabler-file-text-shield',
                  'title' => ' Sorry. No Content.',
                  'message' => "There's nothing for you to view here yet. If you believe this is an error, or you should have access, please contact the administrator.",
                  'footerMessage' => 'TRACE ' . strtoupper(str()->uuid())
              ])

            @else
                <div class="mb-8">
                    @include('cloudcake-documents::partials.document-author')
                    @include('cloudcake-documents::partials.document-tags')
                </div>
                @include('cloudcake-documents::partials.document-schemas')
                @include('cloudcake-documents::partials.document-ratings')
                @include('cloudcake-documents::partials.document-pagination')
            @endif
        </div>
</x-filament-panels::page>