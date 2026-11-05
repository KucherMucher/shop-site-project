<?php
$pt = require __DIR__ . '/../lang/pt.php';
function walk_keys(array $a, string $p = ''): array {
    $out = [];
    foreach ($a as $k => $v) {
        $key = $p === '' ? $k : $p . '.' . $k;
        if (is_array($v)) {
            $out = array_merge($out, walk_keys($v, $key));
        } else {
            $out[] = $key;
        }
    }
    return $out;
}
$defined = walk_keys($pt);
$used = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../src/Views'));
foreach ($rii as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $c = file_get_contents($f->getPathname());
        if (preg_match_all("/\bt\('([^']+)'/u", $c, $m)) {
            foreach ($m[1] as $k) {
                $used[$k] = true;
            }
        }
    }
}
foreach (glob(__DIR__ . '/../src/Controllers/*.php') as $f) {
    $c = file_get_contents($f);
    if (preg_match_all("/\bt\('([^']+)'/u", $c, $m)) {
        foreach ($m[1] as $k) {
            $used[$k] = true;
        }
    }
}
$sources = array_merge(
    glob(__DIR__ . '/../src/Models/*.php'),
    glob(__DIR__ . '/../src/Support/*.php'),
    glob(__DIR__ . '/../src/Core/*.php'),
    glob(__DIR__ . '/../src/*.php')
);
foreach ($sources as $f) {
    $c = file_get_contents($f);
    if (preg_match_all("/\bt\('([^']+)'/u", $c, $m)) {
        foreach ($m[1] as $k) {
            $used[$k] = true;
        }
    }
}
$missing = array_diff(array_keys($used), $defined);
sort($missing);
echo "MISSING (" . count($missing) . "):\n" . implode("\n", $missing) . "\n";
