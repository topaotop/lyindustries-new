<?php
declare(strict_types=1);

/**
 * Orange line-icons (24×24) used on the homepage tiles. Content stores the key; the template
 * turns it into a data: URI with icon_uri(). Add new shapes here to offer them in the admin.
 *
 * @return array<string, string> key => inner SVG shapes
 */
function icon_shapes(): array
{
    return [
        'neck'   => "<path d='M4 7c3 3 13 3 16 0'/><path d='M7 9l1 8h8l1-8'/>",
        'band'   => "<rect x='3' y='8' width='18' height='8' rx='2'/><path d='M3 12h18' stroke-dasharray='2.5 2.5'/>",
        'stripe' => "<path d='M4 20L13 4'/><path d='M9 20L18 4'/><path d='M14 20L21 7.5'/>",
        'cord'   => "<path d='M5.5 7c7-3 6 10 13 7'/><circle cx='5.5' cy='7' r='1.7'/><circle cx='18.5' cy='14' r='1.7'/>",
        'collar' => "<path d='M7 4v6a5 5 0 0 0 10 0V4'/><path d='M7 4h3M14 4h3'/>",
        'cap'    => "<path d='M3 12c0-4 4-6 9-6s9 2 9 6'/><path d='M3 12c0 2 4 3 9 3s9-1 9-3'/>",
    ];
}

/** Icon key → data: URI for <img src>; unknown keys give an empty string. */
function icon_uri(string $key): string
{
    $shapes = icon_shapes()[$key] ?? null;
    if ($shapes === null) {
        return '';
    }
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='#f26b1d'"
         . " stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'>$shapes</svg>";

    return 'data:image/svg+xml;charset=utf-8,' . rawurlencode($svg);
}
