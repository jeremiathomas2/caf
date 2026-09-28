<?php

namespace App\Enums;

enum RegistrationRole: string
{
    case Singers = 'singers';
    case Others = 'others';

    public function label(): string
    {
        return match ($this) {
            self::Singers => 'Performing group',
            self::Others => 'Volunteer, sponsor, partner or media',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Singers => 'Registering as',
            self::Others => 'I am a',
        };
    }

    /**
     * Allowed member-count bounds for the public registration form.
     *
     * @return array{min: int, max: int}
     */
    public function memberBounds(): array
    {
        return match ($this) {
            self::Singers => ['min' => 3, 'max' => 12],
            self::Others => ['min' => 1, 'max' => 100],
        };
    }

    /**
     * Category options offered for this registration role.
     *
     * @return list<string>
     */
    public function categories(): array
    {
        return match ($this) {
            self::Singers => [
                'A cappella group',
                'Gospel group',
                'Vocal ensemble',
                'Choir',
                'Individual vocal artist',
                'Worship team',
                'Guest artist',
                'Cultural performers',
            ],
            self::Others => [
                'Volunteer',
                'Sponsor',
                'Partner',
                'Media / content creator',
            ],
        };
    }
}
