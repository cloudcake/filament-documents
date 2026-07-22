<?php

namespace Cloudcake\Documents\Pages;

use Cloudcake\Components\IconPicker\Forms\Components\IconPicker;
use Cloudcake\Documents\Enums\DocumentAccessAuthRequirementSetting;
use Cloudcake\Documents\Enums\DocumentAccessSetting;
use Cloudcake\Documents\Enums\DocumentAuthorshipSetting;
use Cloudcake\Documents\Enums\DocumentRatingSystemSetting;
use Cloudcake\Documents\Enums\DocumentMetaSetting;
use Cloudcake\Documents\Models\Category;
use Cloudcake\Documents\Models\Content;
use Cloudcake\Documents\Models\Document;
use Cloudcake\Documents\Models\Rating;
use Cloudcake\Documents\Models\Revision;
use Cloudcake\Documents\Resources\Contents\ContentResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use ZipArchive;

class Settings extends Page implements HasSchemas, HasActions
{
    use InteractsWithSchemas;
    use InteractsWithActions;

    protected string $view = 'cloudcake-documents::layouts.page';

    protected static string|BackedEnum|null $navigationIcon = 'tabler-certificate';

    protected static ?string $title = 'Settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $navigationParentItem = Documentation::NAV_LABEL;

    protected Width|string|null $maxContentWidth = Width::SevenExtraLarge;

    protected static ?int $navigationSort = 100;

    public Document|null $record = null;

    public array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_document')
                ->label('View Document')
                ->color('gray')
                ->url(fn() => url()->to(filament()->getCurrentPanel()->getPlugin(\Cloudcake\Documents\Document::$id)->getAdminFor()), shouldOpenInNewTab: true),

            Action::make('import_document')
                ->label('Import Document')
                ->modal()
                ->modalWidth(Width::Medium)
                ->modalHeading("Import Document")
                ->modalIcon('tabler-download')
                ->modalDescription("Import a document to overwrite or merge in with your existing documentation.")
                ->schema([
                    Select::make('current_content_resolution')
                        ->required()
                        ->live()
                        ->label("What should happen to existing content?")
                        ->options([
                            'keep' => 'Keep it',
                            'delete' => 'Delete it'
                        ]),

                    Select::make('conflict_resolution')
                        ->required()
                        ->visible(fn($get) => $get('current_content_resolution') === 'keep')
                        ->label("When a conflict is found:")
                        ->options([
                            'replace' => 'Overwrite It',
                            'nothing' => 'Skip the item',
                        ]),

                    FileUpload::make('import_file')
                        ->fetchFileInformation(false)
                        ->storeFiles(false)
                        ->required()
                        ->hiddenLabel(),
                ])
                ->modalSubmitActionLabel('Import')
                ->action(function ($livewire, array $data) {
                    /** @var TemporaryUploadedFile $temporaryFile */
                    $temporaryFile = $data['import_file'];

                    if ($data['current_content_resolution'] === 'delete') {
                        $panelId = Document::current()->id;

                        // All scoped to current panel
                        Category::where('panel_id', $panelId)->forceDelete();
                        Rating::where('panel_id', $panelId)->forceDelete();
                        Content::where('panel_id', $panelId)->forceDelete();
                        Revision::where('panel_id', $panelId)->forceDelete();

                        Document::current()->forceDelete();
                    }

                    $conflictResolution = $data['conflict_resolution'] ?? 'nothing';

                    $document = Document::current();

                    try {
                        $zip = new ZipArchive;

                        if ($zip->open($temporaryFile->getRealPath())) {
                            for ($i = 0; $i < $zip->numFiles; $i++) {
                                $fileInfo = $zip->statIndex($i);
                                $fileName = $fileInfo['name'];

                                if (str_ends_with($fileName, '/'))
                                    continue;

                                if ($fileName === 'settings.dat') {
                                    $document->settings = unserialize($zip->getFromIndex($i));
                                    $document->save();
                                } elseif ($fileName === 'categories.dat') {
                                    $content = unserialize($zip->getFromIndex($i));
                                    $content->each(function ($record) use ($document, $conflictResolution) {
                                        $where = [
                                            'panel_id' => $document->id,
                                            'name' => $record->name,
                                        ];

                                        $data = $record->toArray();

                                        if ($conflictResolution === 'replace') {
                                            Category::withoutGlobalScopes()->updateOrCreate($where, $data);
                                        } else {
                                            Category::withoutGlobalScopes()->firstOrCreate($where, $data);
                                        }
                                    });
                                } elseif ($fileName === 'revisions.dat') {
                                    $content = unserialize($zip->getFromIndex($i));
                                    $content->each(function ($record) use ($document, $conflictResolution) {
                                        $where = ['id' => $record->id];
                                        $data = $record->toArray();

                                        if ($conflictResolution === 'replace') {
                                            Revision::withoutGlobalScopes()->updateOrCreate($where, $data);
                                        } else {
                                            Revision::withoutGlobalScopes()->firstOrCreate($where, $data);
                                        }
                                    });
                                } elseif ($fileName === 'ratings.dat') {
                                    $content = unserialize($zip->getFromIndex($i));
                                    $content->each(function ($record) use ($document, $conflictResolution) {
                                        $where = ['id' => $record->id];
                                        $data = $record->toArray();

                                        if ($conflictResolution === 'replace') {
                                            Rating::withoutGlobalScopes()->updateOrCreate($where, $data);
                                        } else {
                                            Rating::withoutGlobalScopes()->firstOrCreate($where, $data);
                                        }
                                    });
                                } elseif ($fileName === 'content.dat') {
                                    $content = unserialize($zip->getFromIndex($i));
                                    $content->each(function ($record) use ($document, $conflictResolution) {
                                        if ($record->category_id) {
                                            if (!Category::query()->where('id', $record->category_id)->exists())
                                                $record->category_id = null;
                                        }

                                        $where = [
                                            'panel_id' => $document->id,
                                            'parent_id' => $record->parent_id,
                                            'slug' => $record->slug
                                        ];

                                        $data = $record->toArray();

                                        if ($conflictResolution === 'replace') {
                                            Content::withoutGlobalScopes()->updateOrCreate($where, $data);
                                        } else {
                                            Content::withoutGlobalScopes()->firstOrCreate($where, $data);
                                        }
                                    });
                                }
                            }
                        }

                        Notification::make()
                            ->title('Import Successful')
                            ->success()
                            ->icon('tabler-check')
                            ->color('success')
                            ->send();

                        $livewire->js('window.location.reload()');
                    } catch (\Exception $e) {
                        //
                    }
                }),

            Action::make('export_document')
                ->label('Export Document')
                ->modal()
                ->modalWidth(Width::Medium)
                ->modalHeading("Export Document")
                ->modalIcon('tabler-upload')
                ->modalDescription("Export the current document to a zip file to be backed up and/or imported into a different environment.")
                ->modalSubmitActionLabel('Download')
                ->schema([
                    Select::make('export_items')
                        ->label("What should be exported?")
                        ->placeholder("Select Components")
                        ->multiple()
                        ->required()
                        ->options([
                            'categories' => 'Categories',
                            'content' => 'Content',
                            'ratings' => 'Ratings',
                            'revisions' => 'Revisions',
                            'settings' => 'Settings',
                        ]),

                    Checkbox::make('strip_variables')
                        ->default(true)
                        ->label("Strip any secret values and/or variables from content")
                        ->belowLabel("This will remove any secret values and/or variables from the exported content, including passwords, API keys, and any other sensitive information."),

                    Checkbox::make('strip_user')
                        ->default(true)
                        ->label("Strip all user/authorship information")
                        ->belowLabel("This will remove all user information from the exported content, including rater IP addresses, content writers, editors, and any other user-specific information."),
                ])
                ->action(function (array $data) {
                    $files = [];
                    $fileDir = sys_get_temp_dir();
                    $fileExt = 'dat';
                    $filePath = fn(string $path) => $fileDir . $path;

                    $exportPanelId = \Cloudcake\Documents\Document::viewPanel()->getId();
                    $exportsSettings = in_array('settings', $data['export_items']);
                    $exportCategories = in_array('categories', $data['export_items']);
                    $exportContent = in_array('content', $data['export_items']);
                    $exportRatings = in_array('ratings', $data['export_items']);
                    $exportRevisions = in_array('revisions', $data['export_items']);

                    $document = Document::current();

                    if ($exportsSettings)
                        file_put_contents($files[] = $filePath("/settings.$fileExt"), serialize($document->settings));

                    if ($exportCategories)
                        file_put_contents($files[] = $filePath("/categories.$fileExt"), serialize(
                            Category::withoutGlobalScopes()->where('panel_id', $exportPanelId)->get()
                        ));

                    if ($exportContent)
                        file_put_contents($files[] = $filePath("/content.$fileExt"), serialize(
                            Content::withoutGlobalScopes()->where('panel_id', $exportPanelId)->get()
                        ));

                    if ($exportRevisions)
                        file_put_contents($files[] = $filePath("/revisions.$fileExt"), serialize(
                            Revision::withoutGlobalScopes()->get()
                        ));

                    if ($exportRatings)
                        file_put_contents($files[] = $filePath("/ratings.$fileExt"), serialize(
                            Rating::withoutGlobalScopes()->where('panel_id', $exportPanelId)->get()
                        ));

                    $zipFileName = str(config('app.name') . '-document-' . time())->slug()->append('.zip')->toString();
                    $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

                    $zip = new ZipArchive;

                    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                        foreach ($files as $file) {
                            if (File::exists($file)) {
                                $zip->addFile($file, str($file)->afterLast('/')->toString());
                            }
                        }
                        $zip->close();
                    }

                    return response()->download($zipFilePath)->deleteFileAfterSend();
                }),

            Action::make('edit_content')
                ->label('Content')
                ->url(fn() => ContentResource::getNavigationUrl()),
        ];
    }

    public function mount(): void
    {
        $this->record = Document::current();
        $this->data = $this->record->settings ?? [];
        $this->schema->fill($this->data);
    }

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Configure who can access content')
                    ->icon('tabler-key')
                    ->iconColor('gray')
                    ->description(new HtmlString("Choose the required access level, specify the user attribute used for identification, and control whether login and registration remain available regardless of the selected access mode. These settings determine how visitors authenticate and access your content."))
                    ->aside()
                    ->components([
                        Flex::make([
                            Select::make(DocumentAccessSetting::AuthRequirement->statePath())
                                ->label(DocumentAccessSetting::AuthRequirement->getLabel())
                                ->options(DocumentAccessSetting::AuthRequirement->getOptions())
                                ->hintIcon(DocumentAccessSetting::AuthRequirement->getHintIcon())
                                ->hintIconTooltip(DocumentAccessSetting::AuthRequirement->getHintTooltip())
                                ->default(fn() => DocumentAccessAuthRequirementSetting::Auth->value)
                                ->required()
                                ->inlineLabel()
                                ->grow(false),
                        ])->from('md'),

                        Flex::make([
                            TextInput::make(DocumentAccessSetting::AuthNameAttribute->statePath())
                                ->hintIcon(DocumentAccessSetting::AuthNameAttribute->getHintIcon())
                                ->hintIconTooltip(DocumentAccessSetting::AuthNameAttribute->getHintTooltip())
                                ->required()
                                ->inlineLabel()
                                ->label('Name Attribute')
                                ->default(fn() => 'name'),
                        ])->maxWidth(Width::Medium),

//                           Checkbox::make(DocumentAccessSetting::AuthAllowLogin->statePath())
//                                   ->label(DocumentAccessSetting::AuthAllowLogin->getLabel())
//                                   ->belowLabel(DocumentAccessSetting::AuthAllowLogin->getDescription())
//                                   ->live(),
//
//                           Checkbox::make(DocumentAccessSetting::AuthAllowRegister->statePath())
//                                   ->label(DocumentAccessSetting::AuthAllowRegister->getLabel())
//                                   ->belowLabel(DocumentAccessSetting::AuthAllowRegister->getDescription())
//                                   ->live(),
                    ]),

                Section::make('Configure content rating')
                    ->icon('tabler-star-half-filled')
                    ->iconColor('gray')
                    ->description(new HtmlString("Control how ratings behave across your site, including visibility, anonymity, cumulative scoring, and the rating options available to users. These settings determine how visitors interact with and evaluate your content."))
                    ->aside()
                    ->components([
                        Checkbox::make(DocumentRatingSystemSetting::Enabled->statePath())
                            ->live()
                            ->label(DocumentRatingSystemSetting::Enabled->getLabel())
                            ->belowLabel(DocumentRatingSystemSetting::Enabled->getDescription()),

                        Checkbox::make(DocumentRatingSystemSetting::ShowRatingCount->statePath())
                            ->label(DocumentRatingSystemSetting::ShowRatingCount->getLabel())
                            ->belowLabel(DocumentRatingSystemSetting::ShowRatingCount->getDescription())
                            ->visible(fn($get) => $get(DocumentRatingSystemSetting::Enabled->statePath())),

                        Checkbox::make(DocumentRatingSystemSetting::AllowAnonymous->statePath())
                            ->label(DocumentRatingSystemSetting::AllowAnonymous->getLabel())
                            ->belowLabel(DocumentRatingSystemSetting::AllowAnonymous->getLabel())
                            ->visible(fn($get) => $get(DocumentRatingSystemSetting::Enabled->statePath())),

                        Checkbox::make(DocumentRatingSystemSetting::AccumulativeMode->statePath())
                            ->label(DocumentRatingSystemSetting::AccumulativeMode->getLabel())
                            ->belowLabel(DocumentRatingSystemSetting::AccumulativeMode->getDescription())
                            ->visible(fn($get) => $get(DocumentRatingSystemSetting::Enabled->statePath())),

                        Repeater::make(DocumentRatingSystemSetting::Options->statePath())
                            ->visible(fn($get) => $get(DocumentRatingSystemSetting::Enabled->statePath()))
                            ->required()
                            ->addActionLabel('Add Rating Option')
                            ->addActionAlignment(Alignment::Left)
                            ->minItems(1)
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(function ($state, $index) {
                                if (($state['label'] ?? false) && (isset($state['value']))) {
                                    $i = $index + 1;

                                    return "$i. {$state['label']} ({$state['value']})";
                                }

                                return null;
                            })
                            ->columns(3)
                            ->expandAllAction(fn($action) => $action->visible(false))
                            ->collapseAllAction(fn($action) => $action->visible(false))
                            ->components([
                                TextInput::make('label')->inlineLabel()->required(),
                                IconPicker::make('icon')->inlineLabel()->required(),
                                TextInput::make('value')->inlineLabel()->numeric()->required(),
                            ])
                            ->afterStateHydrated(function (Repeater $component, $state) {
                                if (blank($state)) {
                                    $component->state([
                                        ['label' => 'Useless', 'icon' => 'tabler-star-filled', 'value' => -3],
                                        ['label' => 'Very Unhelpful', 'icon' => 'tabler-star-filled', 'value' => -2],
                                        ['label' => 'Somewhat Unhelpful', 'icon' => 'tabler-star-filled', 'value' => -1],
                                        ['label' => 'Somewhat Helpful', 'icon' => 'tabler-star-filled', 'value' => 0],
                                        ['label' => 'Very Helpful', 'icon' => 'tabler-star-filled', 'value' => 1],
                                        ['label' => 'Extremely Helpful', 'icon' => 'tabler-star-filled', 'value' => 2],
                                    ]);
                                }

                                return $state;
                            }),
                    ]),

                Section::make('Control document authorship display')
                    ->icon('tabler-ballpen')
                    ->iconColor('gray')
                    ->description(new HtmlString("Choose whether writer and editor names are shown within documents, giving you control over how contributor information is presented."))
                    ->aside()
                    ->components([
                        Checkbox::make(DocumentAuthorshipSetting::ShowWriter->statePath())
                            ->label(DocumentAuthorshipSetting::ShowWriter->getLabel())
                            ->belowLabel(DocumentAuthorshipSetting::ShowWriter->getDescription()),

                        Checkbox::make(DocumentAuthorshipSetting::ShowEditor->statePath())
                            ->label(DocumentAuthorshipSetting::ShowEditor->getLabel())
                            ->belowLabel(DocumentAuthorshipSetting::ShowEditor->getDescription()),
                    ]),

                Section::make('Control meta display')
                    ->icon('tabler-tag')
                    ->iconColor('gray')
                    ->description(new HtmlString("Choose what meta to use and how it is delivered to documentation viewers."))
                    ->aside()
                    ->components([
                        Checkbox::make(DocumentMetaSetting::ShowTags->statePath())
                            ->label(DocumentMetaSetting::ShowTags->getLabel())
                            ->belowLabel(DocumentMetaSetting::ShowTags->getDescription()),
                    ]),

                Form::make([EmbeddedSchema::make('content')])
                    ->id('content')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label(__('filament-panels::auth/pages/edit-profile.form.actions.save.label'))
                                ->submit('save')
                                ->keyBindings(['mod+s'])
                        ])->alignment($this->getFormActionsAlignment()),
                    ]),
            ]);
    }

    public function save()
    {
        $this->record = Document::current();

        $this->callHook('beforeValidate');

        $data = $this->schema->getState();

        $this->callHook('afterValidate');

        $this->record->settings = $data;
        $this->record->save();

        Notification::make()
            ->icon(static::$navigationIcon)
            ->title(static::getNavigationLabel() . ' Updated')
            ->body('Successfully updated.')
            ->send();
    }
}