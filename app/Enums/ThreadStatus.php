<?php

namespace App\Enums;

enum ThreadStatus: string
{
    case Open = 'open';
    case Pending = 'pending';
    case Resolved = 'resolved';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Pending => 'Pending',
            self::Resolved => 'Resolved',
            self::Spam => 'Spam',
        };
    }
}
