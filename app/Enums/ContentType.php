<?php

namespace App\Enums;

enum ContentType: string
{
    case Page = 'page';
    case News = 'news';
    case Document = 'document';

    public function label(): string
    {
        return match ($this) {
            self::Page => 'Page',
            self::News => 'News',
            self::Document => 'Document',
        };
    }
}
