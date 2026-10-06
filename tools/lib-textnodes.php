<?php
declare(strict_types=1);

/**
 * Finds static text nodes in a PHP template (for moving copy into lyiweb_blocks).
 * Skips <head>, <script>, <style>, comments, PHP blocks and any extra regions given as regexes.
 * Offsets are byte offsets into the original source (masking keeps lengths unchanged).
 *
 * @param list<string> $skipRegions regexes of regions to ignore (e.g. '#<header\b.*?</header>#s')
 * @return list<array{offset: int, length: int, raw: string, lead: string, core: string, trail: string, section: string}>
 */
function find_text_nodes(string $src, array $skipRegions = []): array
{
    $mask = static fn(array $m): string => str_repeat("\x02", strlen($m[0]));
    $masked = preg_replace_callback('#<\?(?:php|=).*?\?>#s', static fn($m) => str_repeat("\x01", strlen($m[0])), $src);
    $bodyAt = strpos($masked, '<body');
    $masked = str_repeat("\x02", $bodyAt) . substr($masked, $bodyAt);
    foreach (array_merge(['#<script\b.*?</script>#s', '#<style\b.*?</style>#s', '#<!--.*?-->#s'], $skipRegions) as $re) {
        $masked = preg_replace_callback($re, $mask, $masked);
    }

    // section name = id of the nearest preceding <section …id="x"> (or "section<N>" when it has none)
    $sections = [];
    preg_match_all('#<section\b([^>]*)>#', $src, $sm, PREG_OFFSET_CAPTURE);
    foreach ($sm[1] as $i => [$attrs, $off]) {
        $sections[] = [$off, preg_match('#\bid="([^"]+)"#', $attrs, $im) ? $im[1] : 'section' . ($i + 1)];
    }

    $nodes = [];
    preg_match_all('#>([^<>]*)<#', $masked, $m, PREG_OFFSET_CAPTURE);
    foreach ($m[1] as [$text, $off]) {
        if ($text === '' || str_contains($text, "\x01") || str_contains($text, "\x02") || !preg_match('/[\p{L}\p{N}]/u', $text)) {
            continue;
        }
        $raw = substr($src, $off, strlen($text));
        preg_match('/^(\s*)(.*?)(\s*)$/su', $raw, $p);
        $section = 'top';
        foreach ($sections as [$sOff, $name]) {
            if ($sOff < $off) {
                $section = $name;
            }
        }
        $nodes[] = ['offset' => $off, 'length' => strlen($raw), 'raw' => $raw, 'lead' => $p[1], 'core' => $p[2], 'trail' => $p[3], 'section' => $section];
    }

    return $nodes;
}
