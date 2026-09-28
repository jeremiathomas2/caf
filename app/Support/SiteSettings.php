<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Typed access to the `settings` table, cached briefly so the public layout
 * does not query on every request.
 */
class SiteSettings
{
    /**
     * @var Collection<string, Setting>|null
     */
    private ?Collection $items = null;

    /**
     * @return Collection<string, Setting>
     */
    public function all(): Collection
    {
        return $this->items ??= Cache::remember(
            'caf.settings',
            300,
            fn (): Collection => Setting::query()->get()->keyBy('key'),
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = $this->all()->get($key);

        return $setting === null ? $default : $setting->typed($default);
    }

    public function string(string $key, string $default = ''): string
    {
        return (string) $this->get($key, $default);
    }

    public function bool(string $key, bool $default = false): bool
    {
        return (bool) $this->get($key, $default);
    }

    public function int(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    /**
     * Forget the cache after a settings write.
     */
    public function flush(): void
    {
        $this->items = null;
        Cache::forget('caf.settings');
    }
}
