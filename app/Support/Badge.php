<?php

namespace App\Support;

use BackedEnum;
use Illuminate\Support\Str;

/**
 * Maps status values onto the badge colour tones defined in admin.css.
 */
class Badge
{
    /**
     * @var array<string, string>
     */
    private const TONES = [
        'green' => [
            'approved', 'confirmed', 'paid', 'resolved', 'success',
            'live', 'operational', 'published', 'active',
        ],
        'blue' => [
            'under review', 'submitted', 'open', 'in progress',
        ],
        'amber' => [
            'pending', 'waitlisted', 'partial', 'partially paid', 'scheduled',
        ],
        'purple' => [
            'shortlisted', 'round 1', 'round 2', 'final', 'draft review',
        ],
        'red' => [
            'rejected', 'disqualified', 'overdue', 'failed', 'spam', 'expired',
        ],
        'gray' => [
            'not selected', 'archived', 'waived', 'cancelled', 'inactive',
            'unpaid', 'no entries',
        ],
    ];

    public static function tone(BackedEnum|string|int|null $value, string $default = 'gray'): string
    {
        $label = self::label($value);

        foreach (self::TONES as $tone => $labels) {
            if (in_array($label, $labels, true)) {
                return $tone;
            }
        }

        return $default;
    }

    /**
     * The human label for a status value, taken from the enum when there is one.
     */
    public static function label(BackedEnum|string|int|null $value): string
    {
        if ($value instanceof BackedEnum) {
            $method = 'label';

            if (method_exists($value, $method)) {
                return Str::lower((string) $value->{$method}());
            }

            return Str::lower(str_replace('_', ' ', (string) $value->value));
        }

        return Str::lower(str_replace(['_', '-'], ' ', (string) $value));
    }

    /**
     * Render a badge with the status dot the stylesheet expects.
     */
    public static function render(BackedEnum|string|int|null $value, bool $dot = true): string
    {
        $label = self::label($value);

        return sprintf(
            '<span class="badge %s">%s%s</span>',
            self::tone($value),
            $dot ? '<span class="dot"></span>' : '',
            e($label),
        );
    }
}
