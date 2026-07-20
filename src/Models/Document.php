<?php

namespace Cloudcake\Documents\Models;

use Cloudcake\Documents\Document as Plugin;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $primaryKey = 'id';

    public $incrementing = false;

    private static Document|null $cachedDocument = null;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Document $model) {
            if (($panel = filament()->getCurrentPanel()) && $panel->hasPlugin(Plugin::$id)) {

                /** @var Plugin $plugin */
                $plugin = $panel->getPlugin(Plugin::$id);

                if ($plugin->isAdmin()) {
                    $model->id = $plugin->getAdminFor();
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'id'       => 'string',
            'custom'   => 'array',
            'settings' => 'array',
        ];
    }

    protected $fillable = [
        'id',
        'custom',
        'settings',
    ];

    public function getTable(): string
    {
        return Plugin::getSettingsTableName();
    }

    public static function current(string|null $panelId = null): static
    {
        if (static::$cachedDocument)
            return static::$cachedDocument;

        if (!$panelId)
            $panelId = filament()->getCurrentPanel()?->getId();

        if (($panel = filament()->getCurrentPanel()) && $panel->hasPlugin(Plugin::$id)) {

            /** @var Plugin $plugin */
            $plugin = $panel->getPlugin(Plugin::$id);

            if ($plugin->isAdmin()) {
                $panelId = $plugin->getAdminFor();
            }
        }

        static::$cachedDocument = static::query()->firstOrCreate([
            'id' => $panelId
        ]);

        return static::$cachedDocument;
    }

    /**
     * @param \BackedEnum $key
     * @param mixed|null $default
     * @return mixed
     */
    public function setting(\BackedEnum $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key->statePath(), $default);
    }

    public function set(\BackedEnum $key, mixed $value, bool $save = true): void
    {
        $settings = $this->settings;

        data_set($settings, $key->statePath(), $value);

        $this->settings = $settings;

        if ($save)
            $this->save();
    }
}