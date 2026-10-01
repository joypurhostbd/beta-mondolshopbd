<?php

namespace Modules\Setting\Domain\Enums;

enum SnippetTypeEnum: string
{
    case HTML = 'html';
    case JAVASCRIPT = 'javascript';
    case CSS = 'css';
    case TEXT = 'text';

    public function label(): string
    {
        return match ($this) {
            self::HTML => 'HTML / Mixed Script',
            self::JAVASCRIPT => 'JavaScript (<script>)',
            self::CSS => 'CSS (<style>)',
            self::TEXT => 'Plain Text',
        };
    }
}
