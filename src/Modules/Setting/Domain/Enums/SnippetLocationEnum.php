<?php

namespace Modules\Setting\Domain\Enums;

enum SnippetLocationEnum: string
{
    case HEAD = 'head';
    case BODY_OPEN = 'body_open';
    case FOOTER = 'footer';

    public function label(): string
    {
        return match ($this) {
            self::HEAD => 'Header (<head>...</head>)',
            self::BODY_OPEN => 'Body Open (Immediately after <body>)',
            self::FOOTER => 'Footer (Immediately before </body>)',
        };
    }
}
