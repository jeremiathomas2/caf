<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key', 'name', 'description', 'initials', 'gradient',
    'is_enabled', 'last_checked_at', 'last_success_at', 'config', 'sort_order',
])]
class Integration extends Model
{
    use HasFactory;

    /**
     * Brand gradient per integration key, matching the prototype's tiles.
     *
     * @var array<string, string>
     */
    private const GRADIENTS = [
        'mpesa' => 'linear-gradient(135deg,#1F7A4D,#4CC38A)',
        'sms' => 'linear-gradient(135deg,#5E181D,#E4572E)',
        'email' => 'linear-gradient(135deg,#7A2228,#B8391A)',
        'whatsapp' => 'linear-gradient(135deg,#B8860B,#E8B368)',
        'storage' => 'linear-gradient(135deg,#D9913F,#E4572E)',
        'analytics' => 'linear-gradient(135deg,#3A0C10,#7A2228)',
    ];

    /**
     * The CSS background for this integration's logo tile.
     */
    public function gradientCss(): string
    {
        return self::GRADIENTS[$this->key]
            ?? (is_string($this->gradient) && str_starts_with($this->gradient, 'linear-gradient')
                ? $this->gradient
                : 'linear-gradient(135deg,#E4572E,#D9913F)');
    }

    public function isHealthy(): bool
    {
        return $this->last_success_at !== null
            && $this->last_success_at->greaterThan(now()->subDay());
    }

    public function configured(): bool
    {
        return filled($this->config);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_enabled' => 'boolean',
            'last_checked_at' => 'datetime',
            'last_success_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }
}
