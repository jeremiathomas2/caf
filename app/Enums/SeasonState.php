<?php

namespace App\Enums;

enum SeasonState: string
{
    case Draft = 'draft';
    case Live = 'live';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Live => 'Live',
            self::Archived => 'Archived',
        };
    }
}
