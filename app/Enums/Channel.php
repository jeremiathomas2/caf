<?php

namespace App\Enums;

enum Channel: string
{
    case Whatsapp = 'whatsapp';
    case Sms = 'sms';
    case Email = 'email';
    case Portal = 'portal';

    public function label(): string
    {
        return match ($this) {
            self::Whatsapp => 'WhatsApp',
            self::Sms => 'SMS',
            self::Email => 'Email',
            self::Portal => 'Portal',
        };
    }

    /**
     * Short code used by the CSS channel tag modifiers.
     */
    public function tagModifier(): string
    {
        return match ($this) {
            self::Whatsapp => 'wa',
            self::Sms => 'sms',
            self::Email => 'email',
            self::Portal => '',
        };
    }
}
