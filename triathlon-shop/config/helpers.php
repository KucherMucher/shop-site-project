<?php
declare(strict_types=1);

/** Escapes text before printing it in HTML. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/** Formats a price like the JavaScript side does: €12.90 */
function money(float $v): string
{
    return '€' . number_format($v, 2, '.', ',');
}
