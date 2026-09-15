<?php

namespace Cloudcake\Documents\Livewire;

use Cloudcake\Documents\Document;
use Cloudcake\Documents\Enums\HttpHost;
use Cloudcake\Documents\Enums\HttpMethod;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\CodeEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Support\Enums\FontWeight;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;
use Stringable;

class HttpRequest extends Component
{
    protected static function getLabel(array|null $state = []): string|null
    {
        if ($state['endpoint'] ?? false)
            return "{$state['method']} {$state['endpoint']}";

        return null;
    }

    protected static function getIcon(array|null $state = []): string|Stringable|null
    {
        return 'tabler-api';
    }

    protected static function getEditorSchema(array|null $state): array
    {
        return [
            Hidden::make('base_url')
                ->visible(fn($get) => $get('host_type') === HttpHost::Current),

            Flex::make([
                Select::make('method')
                    ->default(fn() => HttpMethod::GET)
                    ->live()
                    ->required()
                    ->columnSpan(1)
                    ->options(HttpMethod::class)
                    ->grow(false),

                Select::make('host_type')
                    ->required()
                    ->label('Host')
                    ->columnSpan(1)
                    ->live()
                    ->afterStateUpdated(fn($state, $set) => $set('base_url', match ($state) {
                        HttpHost::Current => url()->to('/'),
                        default => null
                    }))
                    ->options(HttpHost::class)
                    ->grow(false),
            ])->columnSpanFull(),

            TextInput::make('base_url')
                ->required()
                ->label('Base URL')
                ->url()
                ->columnSpanFull()
                ->visible(fn($get) => $get('host_type') === HttpHost::External)
                ->grow(),

            TextInput::make('endpoint')
                ->required()
                ->extraAttributes(['spellcheck' => 'false'])
                ->columnSpanFull()
                ->live(debounce: 1000)
                ->afterLabel(fn($state, $get) => [
                    TextEntry::make('endpoint_preview')->hiddenLabel()->default(static::colorizeEndpoint($state))->badge(),
                ])
                ->afterStateUpdated(function ($state, $get, $set) {
                    $set('route_parameters', collect(
                        self::findPlaceholdersInEndpoint($state))
                        ->flatMap(fn($key, $value) => [$key => ''])
                        ->toArray()
                    );

                    $set('query_parameters', collect(
                        self::findQueryParametersInUrl($state))
                        ->toArray()
                    );
                }),

            KeyValue::make('route_parameters')
                ->columnSpanFull()
                ->hiddenLabel()
                ->keyLabel('Route Parameter')
                ->addable(false)
                ->deletable(false)
                ->keyPlaceholder('Parameter Name')
                ->editableKeys(false)
                ->visible(fn($get) => $get('route_parameters'))
                ->valueLabel('Default Value'),

            Checkbox::make('allow_request_route_edits')
                ->visible(fn($get) => $get('route_parameters'))
                ->columnSpanFull()
                ->label("Allow viewers to edit the request route parameters."),

            KeyValue::make('query_parameters')
                ->columnSpanFull()
                ->hiddenLabel()
                ->keyLabel('Query Parameter')
                ->addable(false)
                ->deletable(false)
                ->keyPlaceholder('Parameter Name')
                ->editableKeys(false)
                ->visible(fn($get) => $get('query_parameters'))
                ->valueLabel('Default Value'),

            Checkbox::make('allow_request_query_edits')
                ->visible(fn($get) => $get('query_parameters'))
                ->columnSpanFull()
                ->label("Allow viewers to edit the request query parameters."),

            KeyValue::make('headers')
                ->columnSpanFull()
                ->hiddenLabel()
                ->default([
                    [
                        'key' => 'Content-Type',
                        'value' => 'application/json'
                    ],
                    [
                        'key' => 'Accept',
                        'value' => 'application/json'
                    ]
                ])
                ->keyLabel('Header')
                ->valueLabel('Value'),

            Checkbox::make('allow_request_header_edits')
                ->columnSpanFull()
                ->label("Allow viewers to edit the request headers."),

            CodeEditor::make('request_body')
                ->columnSpanFull()
                ->visible(fn($get) => $get('method') && $get('method')->hasBody())
                ->language(CodeEditor\Enums\Language::Json),

            Checkbox::make('allow_request_body_edits')
                ->visible(fn($get) => $get('method') && $get('method')->hasBody())
                ->columnSpanFull()
                ->label("Allow viewers to edit the request body."),

            Checkbox::make('store_response')
                ->live()
                ->columnSpanFull()
                ->label("Store response as variable"),

            TextInput::make('variable_name')
                ->inlineLabel()
                ->belowContent('A plain string name, no variable characters (e.g., $, {, }, etc.)')
                ->columnSpanFull()
                ->visible(fn($get) => $get('store_response'))
                ->rules(['string', 'regex:/^[a-z0-9]+(?:_[a-z0-9]+)*$/', 'lowercase'])
                ->required()
        ];
    }

    protected static function getRenderSchema(array|null $state): array
    {
        $method = HttpMethod::tryFrom(strtoupper($state['method'] ?? ''));
        $endpoint = static::colorizeEndpoint($state['endpoint'] ?? '');
        $baseUrl = $state['base_url'] ?? url()->to($state['endpoint'] ?? '');

        return [
            Section::make(str("HTTP <span class='font-semibold'>{$method->getLabel()}</span> $endpoint")->toHtmlString())
                ->compact()
                ->contained(false)
                ->description($baseUrl)
                ->collapsible()
                ->collapsed()
                ->components([
                    KeyValue::make('route_parameters')
                        ->hiddenLabel()
                        ->keyLabel('Route Parameter')
                        ->valueLabel('Value')
                        ->valuePlaceholder(fn($state) => "Enter " . array_key_first($state) . " Value")
                        ->editableValues(fn($get) => $get('allow_request_route_edits') ?? false)
                        ->editableKeys(false)
                        ->deletable(false)
                        ->addable(false)
                        ->visible(fn($get) => $get('route_parameters')),

                    KeyValue::make('query_parameters')
                        ->hiddenLabel()
                        ->keyLabel('Query Parameter')
                        ->valueLabel('Value')
                        ->valuePlaceholder(fn($state) => "Enter " . array_key_first($state) . " Value")
                        ->editableValues(fn($get) => $get('allow_request_query_edits') ?? false)
                        ->editableKeys(false)
                        ->deletable(false)
                        ->addable(false)
                        ->visible(fn($get) => $get('query_parameters')),

                    KeyValue::make('headers')
                        ->hiddenLabel()
                        ->keyLabel('Header')
                        ->valueLabel('Value')
                        ->valuePlaceholder(fn($state) => "Enter " . array_key_first($state) . " Value")
                        ->editableValues(fn($get) => $get('allow_request_header_edits') ?? false)
                        ->editableKeys(false)
                        ->addable(false)
                        ->deletable(false)
                        ->visible(fn($get) => $get('headers'))
                        ->belowContent(function ($get) {
                            $components = [];

                            $variables = static::findVariablePlaceholders($get('headers'));

                            foreach ($variables as $variable) {
                                $components[] = Text::make("$variable")
                                    ->badge()
                                    ->inlineLabel()
                                    ->icon('tabler-variable')
                                    ->color('primary')
                                    ->extraAttributes(['class' => 'cursor-pointer'])
                                    ->tooltip(str(Document::getVar($variable))->limit(32));
                            }

                            return $components;
                        }),

                    CodeEditor::make('request_body')
                        ->visible(fn($get) => $get('request_body'))
                        ->hiddenLabel()
                        ->disabled(fn($get) => !$get('allow_request_body_edits'))
                        ->language(CodeEditor\Enums\Language::Json)
                        ->belowContent(function ($get) {
                            $components = [];

                            $variables = static::findVariablePlaceholders($get('request_body'));

                            foreach ($variables as $variable) {
                                $value = str(Document::getVar($variable))->limit(32);

                                $components[] = Text::make("$variable")
                                    ->badge()
                                    ->inlineLabel()
                                    ->icon('tabler-variable')
                                    ->color('primary')
                                    ->extraAttributes(['class' => 'cursor-pointer'])
                                    ->tooltip(!blank($value) ? "$variable: $value" : "$variable has not been set.");
                            }

                            return $components;
                        }),

                    Hidden::make('response_ready'),
                    Hidden::make('response_code'),
                    Hidden::make('response_valid'),
                    Hidden::make('response_time'),

                    Fieldset::make('Response')
                        ->columnSpanFull()
                        ->contained(false)
                        ->columns(1)
                        ->visible(fn($get) => $get('response_ready'))
                        ->components([
                            Flex::make([
                                Text::make(fn($get) => 'HTTP ' . $get('response_code'))
                                    ->grow(false)
                                    ->weight(FontWeight::ExtraBold)
                                    ->extraAttributes(['class' => 'pulse'])
                                    ->visible(fn($get) => $get('response_valid'))
                                    ->badge()
                                    ->icon(function ($get) {
                                        $responseCode = $get('response_code');

                                        $firstCharacter = substr($responseCode, 0, 1);

                                        return match ($firstCharacter) {
                                            '2' => 'tabler-check',
                                            '4', '5' => 'tabler-x',
                                            default => 'tabler-circle-filled',
                                        };
                                    })
                                    ->color(function ($get) {
                                        $responseCode = $get('response_code');

                                        $firstCharacter = substr($responseCode, 0, 1);

                                        return match ($firstCharacter) {
                                            '2' => 'success',
                                            '3' => 'warning',
                                            '4', '5' => 'danger',
                                            default => 'info'
                                        };
                                    }),

                                Text::make(fn($get) => $get('response_time') . 'ms')
                                    ->grow(false)
                                    ->badge()
                                    ->icon('tabler-brand-speedtest')
                                    ->extraAttributes(['class' => 'pulse'])
                                    ->color(function ($get) {
                                        $time = $get('response_time');

                                        if ($time < 150)
                                            return 'success';

                                        if ($time < 250)
                                            return 'warning';

                                        return 'danger';
                                    })
                                    ->visible(fn($get) => $get('response_ready')),
                            ])->columns(1),

                            KeyValue::make('response_headers')
                                ->hiddenLabel()
                                ->keyLabel('Header')
                                ->valueLabel('Value')
                                ->editableKeys(false)
                                ->editableValues(false)
                                ->addable(false)
                                ->deletable(false)
                                ->visible(fn($get) => $get('response_headers')),

                            Hidden::make('response_content_type'),

                            CodeEntry::make('response_body')
                                ->hiddenLabel()
                                ->visible(fn($get) => $get('response_body'))
                                ->grammar(fn($get) => match ($get('response_content_type')) {
                                    'application/xml',
                                    'text/xml',
                                    'xml' => \Phiki\Grammar\Grammar::Xml,
                                    'application/html',
                                    'text/html',
                                    'html',
                                    'text/plain' => \Phiki\Grammar\Grammar::Html,
                                    default => \Phiki\Grammar\Grammar::Json
                                }),
                        ])
                ])
                ->footer([
                    Action::make('run')
                        ->label('Run Now')
                        ->color('primary')->icon('tabler-player-play')
                        ->action(function ($get, $set) {
                            $start = now();
                            $method = HttpMethod::tryFrom($get('method'));
                            $headers = $get('headers');
                            $queryParams = $get('query_parameters');
                            $routeParams = $get('route_parameters');
                            $baseUrl = str($get('base_url'));
                            $endpoint = str($get('endpoint'));

                            foreach ($routeParams as $key => $value) {
                                $endpoint = $endpoint->replace([
                                    ":$key",
                                    "{{$key}}",
                                    "{{{$key}}}",
                                    "\$$key",
                                ], [
                                    $value,
                                    $value,
                                    $value,
                                    $value,
                                ]);
                            }

                            $body = str($get('request_body') ?? '');
                            $headerVariables = self::findVariablePlaceholders($get('headers'), keepVariableWrapper: true);
                            $bodyVariables = self::findVariablePlaceholders($body->toString(), keepVariableWrapper: true);
                            $baseUrlVariables = self::findVariablePlaceholders($baseUrl->toString(), keepVariableWrapper: true);
                            $endpointVariables = self::findVariablePlaceholders($endpoint, keepVariableWrapper: true);

                            $headersString = str(json_encode($headers));

                            foreach ($headerVariables as $variable)
                                $headersString = $headersString->replace(str($variable)->remove(['{{', '}}'])->toString(), Document::getVar($variable));

                            $headers = json_decode($headersString, true);

                            foreach ($bodyVariables as $variable)
                                $body = $body->replace($variable, Document::getVar(str($variable)->remove(['{{', '}}'])->toString()));

                            foreach ($endpointVariables as $variable)
                                $endpoint = $endpoint->replace($variable, Document::getVar(str($variable)->remove(['{{', '}}', '$', ':'])->toString()));

                            foreach ($baseUrlVariables as $variable)
                                $baseUrl = $baseUrl->replace($variable, Document::getVar(str($variable)->remove(['{{', '}}', '$', ':'])->toString()));

                            $url = $baseUrl
                                ->when(!str_ends_with($baseUrl, '/'), fn($str) => $str->append('/'))
                                ->when(str_starts_with($endpoint, '/'), fn($str) => $str->beforeLast('/'))
                                ->append($endpoint)
                                ->toString();

                            $timeout = 20;

                            try {
                                /** @var \Illuminate\Http\Client\Response $response */
                                $response = Http::withHeaders($headers)
                                    ->withUserAgent("Cloudcake HTTP")
                                    ->timeout($timeout)
                                    ->connectTimeout($timeout)
                                    ->withQueryParameters($queryParams)
                                    ->withBody($body)->{$method->value}($url);

                                if ($get('store_response')) {
                                    Document::setVar($get('variable_name'), $response->json() ?? $response->getBody());
                                }

                                $set('response_headers', collect($response->headers())->flatMap(fn($value, $key) => [$key => $value[0]])->toArray());
                                $set('response_content_type', $response->header('Content-Type') ?? 'application/html');
                                $set('response_body', $response->json(default: $response->body(), flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?? 'No Content');
                                $set('response_code', $response->status());
                                $set('response_valid', true);
                                $set('response_time', round($start->diffInMilliseconds()));
                            } catch (ConnectException|ConnectionException  $e) {
                                $set('response_content_type', 'text/plain');
                                $set('response_code', 'Unknown');
                                $set('response_body', $e->getMessage());
                                $set('response_valid', false);
                            } catch (Exception $e) {
                                $set('response_content_type', 'text/plain');
                                $set('response_code', 'Unknown');
                                $set('response_body', "Request encountered an error.");
                                $set('response_valid', false);
                            } finally {
                                $set('response_ready', true);
                            }
                        }),

                    Text::make(fn($get) => $get('variable_name'))
                        ->badge()
                        ->icon('tabler-variable')
                        ->color('primary')
                        ->tooltip(fn($get) => "This HTTP request stores the output response as a variable named {$get('variable_name')}.")
                        ->visible(fn($get) => $get('store_response')),
                ])
                ->footerActions([

                ])
        ];
    }

    private static function findVariablePlaceholders(mixed $data, bool $keepVariableWrapper = false): array
    {
        if (blank($data)) {
            return [];
        }

        if (is_array($data))
            $data = implode('', $data);

        preg_match_all('/\{\{([^}]+)}}/', $data, $matches);

        return collect($matches)
            ->flatten()
            ->values()
            ->unique()
            ->filter(fn($match) => !str($match)->contains('{{'))
            ->when($keepVariableWrapper)
            ->map(fn($match) => str($match)->wrap('{{', '}}'))
            ->toArray();
    }

    private static function findPlaceholdersInEndpoint(?string $endpoint = ''): array
    {
        if (empty($endpoint)) {
            return [];
        }

        $path = parse_url($endpoint, PHP_URL_PATH) ?? $endpoint;

        if (empty($path)) {
            return [];
        }

        $pattern = '/(?<=\/){([a-zA-Z][a-zA-Z0-9_-]*)}|(?<=\/[:$])([a-zA-Z][a-zA-Z0-9_-]*)(?=\/|$)/';

        preg_match_all($pattern, $path, $matches);

        $pathParameters = array_filter(array_merge($matches[1] ?? [], $matches[2] ?? []));

        return collect($pathParameters)->filter(fn($param) => !empty($param))->all();
    }

    private static function findQueryParametersInUrl(?string $url = ''): array
    {
        if (empty($url)) {
            return [];
        }

        $queryString = parse_url($url, PHP_URL_QUERY);

        if (empty($queryString)) {
            return [];
        }

        parse_str($queryString, $params);

        return $params;
    }

    private static function colorizeEndpoint(string|null $endpoint = null, bool $withQueryParameters = false): HtmlString|string|null
    {
        if (empty($endpoint))
            return $endpoint;

        $placeholders = self::findPlaceholdersInEndpoint($endpoint);
        $string = str($endpoint)->when(!$withQueryParameters, fn($str) => $str->before('?'));

        foreach ($placeholders as $parameter) {
            $string = $string->replace([
                '$' . $parameter,
                ':' . $parameter,
                '{' . $parameter . '}',
                '{{' . $parameter . '}}',
            ],
                "<span class='text-primary-600 font-mono px-[3px] bg-gray-200 dark:bg-gray-950 rounded-lg'>$parameter</span>"
            );
        }

        if ($withQueryParameters) {
            $queryParams = self::findQueryParametersInUrl($endpoint);

            foreach ($queryParams as $queryParam => $value) {
                $string = $string->replace([
                    "$queryParam=",
                ], [
                    "<span class='text-primary-600'>$queryParam</span>=",
                ]);
            }
        }

        $string = $string
            ->prepend('<span class="text-default">')
            ->append('</span>');

        return $string->toHtmlString();
    }
}