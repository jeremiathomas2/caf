<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

#[Fillable(['label', 'route_name', 'url', 'is_visible', 'sort_order'])]
#[WithoutTimestamps]
class NavItem extends Model
{
    use HasFactory;

    public function href(): string
    {
        if (filled($this->url)) {
            return $this->url;
        }

        return Route::has($this->route_name) ? route($this->route_name) : '#';
    }

    public function isExternal(): bool
    {
        return str_starts_with((string) $this->href(), ['http://', 'https://']);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
