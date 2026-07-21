<?php

namespace Cloudcake\Documents\Pages;

use Cloudcake\Documents\Enums\DocumentAccessAuthRequirementSetting;
use Cloudcake\Documents\Enums\DocumentAccessSetting;
use Cloudcake\Documents\Enums\DocumentRatingSystemSetting;
use Cloudcake\Documents\Enums\GateCheckType;
use Cloudcake\Documents\Enums\RenderState;
use Cloudcake\Documents\Enums\StatKey;
use Cloudcake\Documents\Models\Content;
use Cloudcake\Documents\Models\Document;
use Cloudcake\Documents\Models\Stat;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class ViewDocument extends Page implements HasSchemas, HasActions
{
    use InteractsWithSchemas;
    use InteractsWithActions;

    protected string $view = 'cloudcake-documents::layouts.document';

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    public Content|null $record = null;

    public Document|null $document = null;

    public Collection|null $contentsIndex = null;

    public int $currentContentIndex = 0;

    public Content|null $previousPage = null;
    public Content|null $nextPage     = null;

    public array|null $userRating = null;

    protected static bool $isGloballySearchable = true;
    private RenderState   $renderState          = RenderState::OK;

    public static function getSlug(?Panel $panel = null): string
    {
        return '/{slug1?}/{slug2?}';
    }

    public function getTitle(): string|Htmlable
    {
        if (!$this->isRenderState(RenderState::OK))
            return '';

        return $this->record?->title ?? 'Documentation';
    }

    public function getBreadcrumbs(): array
    {
        if (!$this->isRenderState(RenderState::OK))
            return [];

        $appName = config('app.name');
        $appUrl = url()->to('/');
        $docUrl = \Cloudcake\Documents\Document::viewPanel()->getUrl();

        $crumbs = [
            $appUrl => $appName,
            $docUrl => 'Documentation',
        ];

        if ($this->record->category) {
            $crumbs[] = $this->record->category->name;
        }

        if ($this->record->parent) {
            if ($this->record->parent->category) {
                $crumbs[] = $this->record->parent->category->name;
            }

            $crumbs[] = $this->record->parent->title;
        }

        $crumbs[] = $this->getTitle();

        return $crumbs;
    }

    public function mount(): void
    {
        $this->getDocumentRecord();

        if (!$this->isDocumentAccessible())
            redirect(filament()->getLoginUrl());

        $this->getContentRecord();

        if (!$this->isRenderState(RenderState::OK))
            return;

        $this->buildPagination();
        $this->getRatingRecord();
    }

    private function buildPagination(): void
    {
        if (blank($this->contentsIndex) && $this->record) {
            $contents = Content::fullIndex(GateCheckType::Index);
            $this->contentsIndex = $contents
                ->whereNull('category_id')
                ->sort(fn($a, $b) => $a->sort <=> $b->sort)
                ->flatMap(function ($content) {
                    $children = clone $content->children;

                    return $children->prepend($content);
                });

            $this->contentsIndex = $this->contentsIndex->merge(
                $contents
                    ->whereNotNull('category_id')
                    ->sort(fn($a, $b) => $a->category->sort <=> $b->category->sort)
                    ->flatMap(function ($content) {
                        $children = clone $content->children;

                        return $children->prepend($content);
                    }));

            $this->currentContentIndex = $this->contentsIndex->search(fn($content) => $content->id === $this->record->id);
        }

        if ($this->currentContentIndex > 0 && isset($this->contentsIndex[$this->currentContentIndex - 1])) {
            $this->previousPage = $this->contentsIndex[$this->currentContentIndex - 1];
        } else {
            $this->previousPage = null;
        }

        if (isset($this->contentsIndex[$this->currentContentIndex + 1])) {
            $this->nextPage = $this->contentsIndex[$this->currentContentIndex + 1];
        } else {
            $this->nextPage = null;
        }
    }

    private function getContentRecord(): void
    {
        $this->renderState = RenderState::OK;

        $index = Content::fullIndex(GateCheckType::None)
            ->flatMap(function ($content) {
                /** @var \Illuminate\Database\Eloquent\Collection $children */
                $children = clone $content->children;

                return $children->prepend($content);
            });

        if ($index->isEmpty()) {
            $this->renderState = RenderState::NoContent;
        } else {
            $slug = collect(request()->route()->parameters())->values()->implode('/');

            if (blank($slug)) {
                $firstAllowedRecord = $index
                    ->firstWhere(fn($record) => \Cloudcake\Documents\Document::allow($record, GateCheckType::Access));

                if ($firstAllowedRecord)
                    redirect()->to($firstAllowedRecord->url);
                else
                    $this->renderState = RenderState::NoContent;

                return;
            }

            $record = $index->firstWhere('slug', $slug);

            if (!$record) {
                $this->renderState = RenderState::NotFound;
                $this->incrementMisses();
                return;
            }

            if (!\Cloudcake\Documents\Document::allow($record, GateCheckType::Access)) {
                $this->renderState = RenderState::NotAllowed;
                $this->incrementForbidden();
                return;
            }

            $this->renderState = RenderState::OK;
            $this->record = Content::find($record->id);

            $stat = Stat::firstOrCreate(['key' => StatKey::Visit, 'record_id' => $record->id], ['count' => 0]);
            $stat->increment('count');
        }
    }

    private function incrementMisses(): void
    {
        $stat = Stat::firstOrCreate(['key' => StatKey::Miss], ['count' => 0]);
        $stat->increment('count');
    }

    private function incrementForbidden(): void
    {
        $stat = Stat::firstOrCreate(['key' => StatKey::Forbidden], ['count' => 0]);
        $stat->increment('count');
    }

    public function getSchemas(): array
    {
        return $this->record->schema;
    }

    private function getDocumentRecord(): Document|null
    {
        if ($this->document)
            return $this->document;

        $this->document = Document::current();

        return $this->document;
    }

    private function isDocumentAccessible(): bool
    {
        if (!$this->document)
            return false;

        if ($this->document->setting(DocumentAccessSetting::AuthRequirement) === DocumentAccessAuthRequirementSetting::Auth->value && !auth()->check()) {
            return false;
        }

        return true;
    }

    private function getRatingRecord(): void
    {
        if (!$this->record)
            return;

        $rating = $this->record
            ->ratings()
            ->when(
                fn() => auth()->check(),
                fn($query) => $query->where(['rater_id' => auth()->id(), 'rater_type' => auth()->user()?->getMorphClass()]),
                fn($query) => $query->where('ip_address', request()->ip())
            )
            ->first();

        if ($rating) {
            $this->userRating = collect($this->document->setting(DocumentRatingSystemSetting::Options))->firstWhere('value', $rating->rating);
        }
    }

    public function rate(int $rating): void
    {
        $user = auth()->user();

        $rating = $this->record->ratings()->updateOrCreate([
            'ip_address' => request()->ip(),
            'rater_type' => $user?->getMorphClass(),
            'rater_id' => $user?->getKey(),
        ], [
            'rating' => $rating,
        ]);

        // Only update the counts if the user changed their rating, or it's new.
        if ($rating->wasRecentlyCreated || $rating->isDirty()) {
            $this->record->ratings_score += $rating->rating;
            $this->record->ratings_total += $rating->wasRecentlyCreated ? 1 : 0;
            $this->record->save();
        }

        $this->getRatingRecord();
    }

    public function isRenderState(RenderState $state): bool
    {
        return $this->renderState === $state;
    }
}
