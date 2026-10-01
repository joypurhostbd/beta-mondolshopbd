<?php

return [
    'encoding'  => 'UTF-8',
    'finalize'  => true,
    'cachePath' => storage_path('app/purifier'),
    'settings'  => [
        'default' => [
            'HTML.Doctype'             => 'XHTML 1.0 Transitional',
            'HTML.Allowed'             => 'p,b,strong,i,em,a[href|title],ul,ol,li,br,span[style],h1,h2,h3,h4,h5,h6,img[src|alt|width|height|style|class],table[class|style|border|cellspacing|cellpadding|summary],thead,tbody,tr,th,td,div[class|style],blockquote,pre,code',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,color,text-decoration,text-align,margin,padding,border,background-color,width,height,max-width,min-width',
            'URI.AllowedSchemes'       => [
                'http'   => true,
                'https'  => true,
                'mailto' => true,
                'ftp'    => true,
                'data'   => true,
            ],
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => false,
        ],
        'rich_text' => [
            'HTML.Doctype'             => 'XHTML 1.0 Transitional',
            'HTML.Allowed'             => 'p,b,strong,i,em,a[href|title],ul,ol,li,br,span[style],h1,h2,h3,h4,h5,h6,img[src|alt|width|height|style|class],table[class|style|border|cellspacing|cellpadding|summary],thead,tbody,tr,th,td,div[class|style],blockquote,pre,code,iframe[src|width|height|frameborder]',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,color,text-decoration,text-align,margin,padding,border,background-color,width,height,max-width,min-width',
            'URI.AllowedSchemes'       => ['http', 'https', 'mailto', 'data'],
        ],
        'meta_description' => [
            'HTML.Allowed' => 'b,strong,i,em,br',
        ],
    ],
];