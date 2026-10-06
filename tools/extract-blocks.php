<?php
declare(strict_types=1);

/**
 * One-off migration: moves static copy of a page template into blocks.
 *   php tools/extract-blocks.php index.php home "section2=trust"
 * - replaces every static text node with <?= b('<slug>.<section>.<nn>') ?>
 * - writes the original text to includes/blocks/<slug>.php (fallback + seed source)
 * <header> and <aside> (shared navigation) are left alone.
 */

if (PHP_SAPI !== 'cli' || $argc < 3) {
    fwrite(STDERR, "usage: php tools/extract-blocks.php <template.php> <slug> [old=new,...]\n");
    exit(1);
}
require __DIR__ . '/lib-textnodes.php';

$root = dirname(__DIR__);
[$template, $slug] = [$argv[1], $argv[2]];
$rename = [];
foreach (array_filter(explode(',', $argv[3] ?? '')) as $pair) {
    [$from, $to] = explode('=', $pair);
    $rename[$from] = $to;
}

$path = $root . '/' . $template;
$src = file_get_contents($path);
$nodes = find_text_nodes($src, ['#<header\b.*?</header>#s', '#<aside\b.*?</aside>#s']);

$counters = $blocks = [];
foreach ($nodes as $i => $n) {
    $section = $rename[$n['section']] ?? $n['section'];
    $counters[$section] = ($counters[$section] ?? 0) + 1;
    $key = sprintf('%s.%s.%02d', $slug, $section, $counters[$section]);
    $text = html_entity_decode($n['core'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') !== $n['core']) {
        fwrite(STDERR, "ABORT: '$key' would not re-escape to the same bytes: {$n['core']}\n");
        exit(1);
    }
    $nodes[$i]['key'] = $key;
    $blocks[$key] = $text;
}

// replace from the end so earlier offsets stay valid; PHP drops one newline after a closing tag, so add it back
for ($i = count($nodes) - 1; $i >= 0; $i--) {
    $n = $nodes[$i];
    $trail = (str_starts_with($n['trail'], "\n") || str_starts_with($n['trail'], "\r\n")) ? "\n" . $n['trail'] : $n['trail'];
    $src = substr_replace($src, $n['lead'] . "<?= b('{$n['key']}') ?>" . $trail, $n['offset'], $n['length']);
}

$out = "<?php\ndeclare(strict_types=1);\n\n/**\n * Built-in copy for {$template} — fallback when lyiweb_blocks has no row/value, and the source\n"
     . " * for docs/sql (php tools/build-seed-blocks.php). Keys: page.section.nn. Once seeded, edit text in the\n"
     . " * admin, not here.\n *\n * @return array<string, string>\n */\nreturn [\n";
$last = null;
foreach ($blocks as $key => $text) {
    $section = explode('.', $key)[1];
    if ($section !== $last) {
        $out .= ($last === null ? '' : "\n") . "    // {$section}\n";
        $last = $section;
    }
    $out .= '    ' . var_export($key, true) . ' => ' . var_export($text, true) . ",\n";
}
$out .= "];\n";

@mkdir($root . '/includes/blocks', 0775, true);
file_put_contents($root . "/includes/blocks/{$slug}.php", $out);
file_put_contents($path, $src);
echo count($blocks) . " blocks → includes/blocks/{$slug}.php; {$template} updated\n";
foreach ($counters as $s => $c) {
    echo "  {$s}: {$c}\n";
}
