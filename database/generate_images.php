<?php

declare(strict_types=1);

/**
 * One-off asset generator for TRIVEX.
 * Produces lightweight SVG product shots, detail shots, lifestyle shots
 * and category illustrations. Run: php database/generate_images.php
 */

$root = dirname(__DIR__) . '/public/assets/img';
@mkdir($root . '/products', 0775, true);
@mkdir($root . '/categories', 0775, true);

$products = [
    'wetsuit-aero' => ['type' => 'wetsuit', 'bg1' => '#e8f3ff', 'bg2' => '#cfe4fb', 'a' => '#0b2a4a', 'b' => '#0b6bff', 'cat' => 'swim'],
    'goggles-speed' => ['type' => 'goggles', 'bg1' => '#e6f7ff', 'bg2' => '#c4e9f8', 'a' => '#0b3d66', 'b' => '#00b4d8', 'cat' => 'swim'],
    'pull-buoy' => ['type' => 'buoy', 'bg1' => '#eef7ff', 'bg2' => '#d5e9fb', 'a' => '#0b6bff', 'b' => '#57c2ff', 'cat' => 'swim'],
    'swim-cap' => ['type' => 'cap', 'bg1' => '#eaf4ff', 'bg2' => '#cfe3f7', 'a' => '#0b2a4a', 'b' => '#0b6bff', 'cat' => 'swim'],
    'bike-road' => ['type' => 'bike', 'bg1' => '#eef4fb', 'bg2' => '#d4e2f2', 'a' => '#0b2a4a', 'b' => '#ff5a3c', 'cat' => 'bike'],
    'helmet-aero' => ['type' => 'helmet', 'bg1' => '#edf3fa', 'bg2' => '#d7e3f0', 'a' => '#f8fbff', 'b' => '#0b6bff', 'cat' => 'bike'],
    'cycling-shoes' => ['type' => 'cycling-shoe', 'bg1' => '#f0f4f9', 'bg2' => '#dbe4ee', 'a' => '#151b25', 'b' => '#0b6bff', 'cat' => 'bike'],
    'bike-computer' => ['type' => 'computer', 'bg1' => '#edf3fa', 'bg2' => '#d6e3f0', 'a' => '#151b25', 'b' => '#0b6bff', 'cat' => 'bike'],
    'bottle' => ['type' => 'bottle', 'bg1' => '#eef8ff', 'bg2' => '#d2eafa', 'a' => '#0b6bff', 'b' => '#8ad4ff', 'cat' => 'bike'],
    'running-shoes' => ['type' => 'running-shoe', 'bg1' => '#fff2ec', 'bg2' => '#ffdccd', 'a' => '#0b2a4a', 'b' => '#ff5a3c', 'cat' => 'run'],
    'trail-shoes' => ['type' => 'trail-shoe', 'bg1' => '#eef5ef', 'bg2' => '#d3e7d8', 'a' => '#1e4738', 'b' => '#7bc47f', 'cat' => 'run'],
    'gps-watch' => ['type' => 'watch', 'bg1' => '#eef2f7', 'bg2' => '#d8e0ea', 'a' => '#2d3743', 'b' => '#0b6bff', 'cat' => 'run'],
    'race-belt' => ['type' => 'belt', 'bg1' => '#f3f5f8', 'bg2' => '#e1e6ee', 'a' => '#151b25', 'b' => '#ffd166', 'cat' => 'run'],
    'energy-gel' => ['type' => 'gel', 'bg1' => '#fbfff0', 'bg2' => '#e9f6c9', 'a' => '#d6f35a', 'b' => '#3d5a1e', 'cat' => 'nutrition'],
    'isotonic' => ['type' => 'tub', 'bg1' => '#fff7ec', 'bg2' => '#ffe6c7', 'a' => '#ff8a3d', 'b' => '#0b2a4a', 'cat' => 'nutrition'],
    'recovery-bar' => ['type' => 'bar', 'bg1' => '#faf3ec', 'bg2' => '#eddcc9', 'a' => '#8b5e3c', 'b' => '#ffd166', 'cat' => 'nutrition'],
    'tri-suit' => ['type' => 'trisuit', 'bg1' => '#eaf2ff', 'bg2' => '#cddffc', 'a' => '#0b2a4a', 'b' => '#ff5a3c', 'cat' => 'equipment'],
    'transition-bag' => ['type' => 'bag', 'bg1' => '#edf3fa', 'bg2' => '#d7e3f0', 'a' => '#0b3d66', 'b' => '#0b6bff', 'cat' => 'equipment'],
    'laces' => ['type' => 'laces', 'bg1' => '#f2f5f9', 'bg2' => '#dfe6ef', 'a' => '#0b6bff', 'b' => '#ffd166', 'cat' => 'equipment'],
    'placeholder' => ['type' => 'placeholder', 'bg1' => '#edf3fa', 'bg2' => '#d7e3f0', 'a' => '#0b2a4a', 'b' => '#0b6bff', 'cat' => 'equipment'],
];

function head(string $bg1, string $bg2, string $extra = ''): string
{
    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="640" height="640" role="img">
<defs>
  <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
    <stop offset="0" stop-color="{$bg1}"/><stop offset="1" stop-color="{$bg2}"/>
  </linearGradient>
  <linearGradient id="obj" x1="0" y1="0" x2="1" y2="1">
    <stop offset="0" stop-color="var(--a)"/><stop offset="1" stop-color="var(--b)"/>
  </linearGradient>
  <filter id="sh" x="-30%" y="-30%" width="160%" height="160%">
    <feDropShadow dx="0" dy="22" stdDeviation="20" flood-color="#062a4d" flood-opacity=".18"/>
  </filter>
  <radialGradient id="glow" cx=".5" cy=".4" r=".6">
    <stop offset="0" stop-color="#ffffff" stop-opacity=".85"/><stop offset="1" stop-color="#ffffff" stop-opacity="0"/>
  </radialGradient>
  {$extra}
</defs>
<rect width="640" height="640" fill="url(#bg)"/>
<circle cx="320" cy="300" r="250" fill="url(#glow)"/>
<path d="M0 560 Q160 520 320 552 T640 540 V640 H0Z" fill="#ffffff" opacity=".26"/>
<ellipse cx="320" cy="548" rx="185" ry="26" fill="#062a4d" opacity=".10"/>
SVG;
}

function foot(): string
{
    return '</svg>';
}

function art(string $type): string
{
    return match ($type) {
        'wetsuit' => <<<'SVG'
<g transform="translate(0,0)" class="object">
  <path d="M257 176 Q282 156 320 156 Q358 156 383 176 L415 214 L465 318 Q472 336 454 345 L431 352 Q418 356 411 339 L382 274 L390 470 L355 548 Q349 562 334 560 L312 556 Q299 554 303 538 L323 444 L317 444 L337 538 Q341 554 328 556 L306 560 Q291 562 285 548 L250 470 L258 274 L229 339 Q222 356 209 352 L186 345 Q168 336 175 318 L225 214 Z" fill="url(#obj)" filter="url(#sh)"/>
  <path d="M320 160 L320 548" stroke="#ffffff" stroke-opacity=".22" stroke-width="4"/>
  <path d="M321 176 L321 430" stroke="#e9f4ff" stroke-opacity=".85" stroke-width="5" stroke-dasharray="5 6"/>
  <path d="M245 246 L395 246" stroke="#ffffff" stroke-opacity=".38" stroke-width="14"/>
  <path d="M252 270 L388 270" stroke="#ffffff" stroke-opacity=".20" stroke-width="5"/>
  <text x="465" y="235" font-family="Arial" font-size="20" font-weight="700" fill="#fff" opacity=".55" transform="rotate(90 465 235)">AERO 3.8</text>
</g>
SVG,
        'goggles' => <<<'SVG'
<g filter="url(#sh)">
  <path d="M142 300 Q120 262 152 242 Q184 224 209 254" fill="none" stroke="#0b3d66" stroke-width="16" stroke-linecap="round"/>
  <path d="M498 300 Q520 262 488 242 Q456 224 431 254" fill="none" stroke="#0b3d66" stroke-width="16" stroke-linecap="round"/>
  <path d="M208 296 Q246 230 320 238 Q394 230 432 296 Q430 360 368 374 Q320 382 272 374 Q210 360 208 296Z" fill="#0b2a4a"/>
  <path d="M232 296 Q260 254 316 258 Q372 254 408 296 Q406 344 356 352 Q320 358 284 352 Q234 344 232 296Z" fill="url(#obj)"/>
  <path d="M240 292 Q266 262 312 264" fill="none" stroke="#fff" stroke-opacity=".65" stroke-width="10" stroke-linecap="round"/>
  <path d="M328 266 Q366 268 398 294" fill="none" stroke="#fff" stroke-opacity=".32" stroke-width="8" stroke-linecap="round"/>
  <path d="M400 330 Q452 340 472 386" fill="none" stroke="#00b4d8" stroke-width="9" stroke-linecap="round"/>
  <path d="M240 330 Q188 340 168 386" fill="none" stroke="#00b4d8" stroke-width="9" stroke-linecap="round"/>
  <circle cx="320" cy="308" r="12" fill="#00b4d8"/>
</g>
SVG,
        'buoy' => <<<'SVG'
<g filter="url(#sh)" transform="rotate(-12 320 330)">
  <path d="M320 170 C246 170 218 226 240 278 C260 326 250 370 236 402 C214 456 254 500 320 500 C386 500 426 456 404 402 C390 370 380 326 400 278 C422 226 394 170 320 170Z" fill="url(#obj)"/>
  <path d="M286 190 C264 240 282 286 300 330" fill="none" stroke="#fff" stroke-opacity=".5" stroke-width="10"/>
  <path d="M354 190 C376 240 358 286 340 330" fill="none" stroke="#fff" stroke-opacity=".25" stroke-width="8"/>
  <path d="M250 392 Q320 350 390 392" fill="none" stroke="#062a4d" stroke-opacity=".35" stroke-width="6"/>
  <text x="288" y="470" font-family="Arial" font-size="24" font-weight="800" fill="#fff" opacity=".7">PRO</text>
</g>
SVG,
        'cap' => <<<'SVG'
<g filter="url(#sh)">
  <path d="M176 372 Q176 208 320 186 Q464 208 464 372 Q464 414 420 418 L220 418 Q176 414 176 372Z" fill="url(#obj)"/>
  <path d="M176 368 Q248 346 320 350 Q392 346 464 368 L464 398 Q392 376 320 380 Q248 376 176 398Z" fill="#0b2a4a"/>
  <path d="M230 300 Q260 240 320 230" fill="none" stroke="#fff" stroke-opacity=".5" stroke-width="14" stroke-linecap="round"/>
  <path d="M350 238 Q396 260 414 300" fill="none" stroke="#fff" stroke-opacity=".2" stroke-width="10" stroke-linecap="round"/>
  <path d="M260 380 h120" stroke="#fff" stroke-opacity=".25" stroke-width="5"/>
</g>
SVG,
        'bike' => <<<'SVG'
<g filter="url(#sh)">
  <circle cx="196" cy="426" r="112" fill="none" stroke="#1a2633" stroke-width="16"/>
  <circle cx="452" cy="426" r="112" fill="none" stroke="#1a2633" stroke-width="16"/>
  <circle cx="196" cy="426" r="92" fill="none" stroke="#9db3c8" stroke-width="3" stroke-dasharray="4 9"/>
  <circle cx="452" cy="426" r="92" fill="none" stroke="#9db3c8" stroke-width="3" stroke-dasharray="4 9"/>
  <path d="M196 426 L268 306 L342 426 L196 426" fill="none" stroke="url(#obj)" stroke-width="16" stroke-linejoin="round"/>
  <path d="M268 306 L392 300 L452 426" fill="none" stroke="url(#obj)" stroke-width="16" stroke-linejoin="round"/>
  <path d="M342 426 L392 300" stroke="url(#obj)" stroke-width="14"/>
  <path d="M268 306 L222 250" stroke="#1a2633" stroke-width="12" stroke-linecap="round"/>
  <path d="M198 244 L250 252" stroke="#1a2633" stroke-width="14" stroke-linecap="round"/>
  <path d="M356 292 Q392 268 430 274" fill="none" stroke="#1a2633" stroke-width="13" stroke-linecap="round"/>
  <path d="M420 262 L456 284" stroke="#1a2633" stroke-width="12" stroke-linecap="round"/>
  <circle cx="342" cy="426" r="22" fill="#1a2633"/>
  <circle cx="342" cy="426" r="9" fill="#ff5a3c"/>
  <path d="M330 440 L316 472" stroke="#1a2633" stroke-width="8" stroke-linecap="round"/>
</g>
SVG,
        'helmet' => <<<'SVG'
<g filter="url(#sh)">
  <path d="M150 390 Q140 250 250 196 Q320 164 390 196 Q500 250 490 390 Q486 430 446 430 L194 430 Q154 430 150 390Z" fill="url(#obj)" stroke="#0b2a4a" stroke-width="6"/>
  <path d="M210 260 Q250 210 320 204" fill="none" stroke="#0b6bff" stroke-width="18" stroke-linecap="round"/>
  <path d="M262 226 L286 330" stroke="#0b2a4a" stroke-opacity=".35" stroke-width="10" stroke-linecap="round"/>
  <path d="M358 222 L382 330" stroke="#0b2a4a" stroke-opacity=".25" stroke-width="10" stroke-linecap="round"/>
  <path d="M432 260 L404 330" stroke="#0b2a4a" stroke-opacity=".18" stroke-width="10" stroke-linecap="round"/>
  <path d="M230 430 Q238 486 286 492" fill="none" stroke="#151b25" stroke-width="9" stroke-linecap="round"/>
  <path d="M410 430 Q402 486 354 492" fill="none" stroke="#151b25" stroke-width="9" stroke-linecap="round"/>
  <path d="M150 398 L490 398" stroke="#0b2a4a" stroke-opacity=".25" stroke-width="5"/>
  <circle cx="286" cy="492" r="8" fill="#0b6bff"/>
</g>
SVG,
        'cycling-shoe' => <<<'SVG'
<g filter="url(#sh)">
  <path d="M160 382 Q168 320 230 306 L292 292 Q332 284 350 250 L390 190 Q414 202 416 240 L418 320 Q494 340 518 394 L528 434 Q530 464 496 466 L196 466 Q158 462 158 430Z" fill="url(#obj)"/>
  <path d="M158 430 L530 428 L530 462 Q528 480 500 482 L192 482 Q160 478 158 452Z" fill="#151b25"/>
  <path d="M420 320 L446 400" stroke="#0b6bff" stroke-width="12" stroke-linecap="round"/>
  <circle cx="430" cy="350" r="24" fill="#f4f7fb" stroke="#0b6bff" stroke-width="7"/>
  <path d="M262 324 Q318 304 368 312" fill="none" stroke="#ffffff" stroke-opacity=".55" stroke-width="9" stroke-linecap="round"/>
  <path d="M250 362 Q312 342 378 352" fill="none" stroke="#ffffff" stroke-opacity=".25" stroke-width="7" stroke-linecap="round"/>
  <path d="M282 430 L330 430 L338 458 L274 458Z" fill="#3a4654"/>
  <path d="M190 466 Q220 442 252 466" fill="none" stroke="#0b6bff" stroke-opacity=".5" stroke-width="5"/>
</g>
SVG,
        'computer' => <<<'SVG'
<g filter="url(#sh)">
  <rect x="176" y="170" width="288" height="340" rx="46" fill="#151b25"/>
  <rect x="196" y="194" width="248" height="292" rx="30" fill="#0b2a4a"/>
  <path d="M228 402 Q264 350 296 370 T356 318 T406 336" fill="none" stroke="#4fe0ff" stroke-width="7" stroke-linecap="round"/>
  <circle cx="406" cy="336" r="8" fill="#ff5a3c"/>
  <text x="232" y="272" font-family="Arial" font-size="46" font-weight="800" fill="#ffffff">34.2</text>
  <text x="234" y="298" font-family="Arial" font-size="15" fill="#8ad4ff" letter-spacing="2">KM/H</text>
  <text x="232" y="350" font-family="Arial" font-size="17" fill="#ffffff" opacity=".7">128 bpm</text>
  <text x="232" y="378" font-family="Arial" font-size="17" fill="#ffffff" opacity=".5">12.4 km</text>
  <rect x="232" y="430" width="72" height="28" rx="14" fill="#0b6bff"/>
  <text x="248" y="450" font-family="Arial" font-size="14" font-weight="700" fill="#fff">LAP</text>
  <rect x="316" y="430" width="72" height="28" rx="14" fill="#ffffff" opacity=".12"/>
  <circle cx="320" cy="526" r="10" fill="#0b6bff"/>
</g>
SVG,
        'bottle' => <<<'SVG'
<g filter="url(#sh)">
  <path d="M262 176 L378 176 L386 230 Q426 264 426 326 L426 468 Q426 510 382 510 L258 510 Q214 510 214 468 L214 326 Q214 264 254 230Z" fill="url(#obj)"/>
  <rect x="272" y="136" width="96" height="52" rx="18" fill="#151b25"/>
  <path d="M300 138 L300 110 Q300 94 320 94 Q340 94 340 110 L340 138" fill="#151b25"/>
  <path d="M214 330 L426 330 L426 402 L214 402Z" fill="#ffffff" opacity=".9"/>
  <text x="262" y="378" font-family="Arial" font-size="28" font-weight="800" fill="#0b2a4a">750</text>
  <text x="336" y="376" font-family="Arial" font-size="16" fill="#0b6bff">ml</text>
  <path d="M248 246 Q266 232 280 224" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="9" stroke-linecap="round"/>
</g>
SVG,
        'running-shoe' => <<<'SVG'
<g filter="url(#sh)">
  <path d="M138 420 Q140 366 196 344 L290 310 Q340 292 366 250 Q382 226 406 238 Q432 252 434 292 L436 348 Q506 368 528 416 Q536 444 508 460 L184 460 Q144 456 138 420Z" fill="url(#obj)"/>
  <path d="M130 436 Q320 402 540 432 L540 468 Q540 496 504 496 L170 496 Q134 492 130 462Z" fill="#f8fbff"/>
  <path d="M134 468 Q320 440 536 466" fill="none" stroke="#0b2a4a" stroke-opacity=".25" stroke-width="4"/>
  <path d="M252 334 Q314 326 372 300" fill="none" stroke="#ffffff" stroke-opacity=".65" stroke-width="8" stroke-linecap="round"/>
  <path d="M262 362 Q330 350 394 318" fill="none" stroke="#ffffff" stroke-opacity=".35" stroke-width="7" stroke-linecap="round"/>
  <path d="M280 320 L314 348 M314 310 L348 338 M348 300 L382 326" stroke="#ffffff" stroke-opacity=".8" stroke-width="5" stroke-linecap="round"/>
  <path d="M420 370 Q474 388 498 424" fill="none" stroke="#ff5a3c" stroke-width="10" stroke-linecap="round"/>
  <path d="M410 270 Q424 280 426 300" fill="none" stroke="#0b2a4a" stroke-opacity=".3" stroke-width="6"/>
</g>
SVG,
        'trail-shoe' => <<<'SVG'
<g filter="url(#sh)">
  <path d="M138 408 Q142 354 196 334 L286 302 Q336 284 360 244 Q376 220 402 232 Q428 246 430 286 L432 340 Q504 360 526 406 Q534 434 506 448 L184 448 Q144 446 138 408Z" fill="url(#obj)"/>
  <path d="M128 432 L540 432 L540 462 L128 462Z" fill="#1b2a22"/>
  <path d="M150 462 l10 22 l14 -22 l16 22 l14 -22 l18 22 l16 -22 l18 22 l16 -22 l18 22 l16 -22 l18 22 l16 -22 l18 22 l16 -22 l18 22 l16 -22 l18 22 l14 -22" fill="#1b2a22"/>
  <path d="M250 326 Q312 318 370 292" fill="none" stroke="#ffffff" stroke-opacity=".5" stroke-width="8" stroke-linecap="round"/>
  <path d="M280 312 L314 340 M314 302 L348 330 M348 292 L382 318" stroke="#ffffff" stroke-opacity=".7" stroke-width="5" stroke-linecap="round"/>
  <path d="M420 362 Q470 380 494 414" fill="none" stroke="#7bc47f" stroke-width="9" stroke-linecap="round"/>
  <path d="M178 420 Q240 400 286 412" fill="none" stroke="#0b2a4a" stroke-opacity=".4" stroke-width="5"/>
</g>
SVG,
        'watch' => <<<'SVG'
<g filter="url(#sh)">
  <path d="M264 156 L376 156 L392 264 L248 264Z" fill="#2d3743"/>
  <path d="M248 432 L392 432 L376 540 L264 540Z" fill="#2d3743"/>
  <path d="M250 196 L390 196 M246 226 L394 226" stroke="#7a8794" stroke-width="4" stroke-opacity=".5"/>
  <path d="M250 482 L390 482 M254 512 L386 512" stroke="#7a8794" stroke-width="4" stroke-opacity=".5"/>
  <rect x="188" y="236" width="264" height="232" rx="58" fill="#3a4654"/>
  <rect x="206" y="252" width="228" height="200" rx="44" fill="#0a0f15"/>
  <circle cx="320" cy="352" r="72" fill="none" stroke="#4fe0ff" stroke-width="4" opacity=".45"/>
  <circle cx="320" cy="352" r="84" fill="none" stroke="#4fe0ff" stroke-width="2" stroke-dasharray="2 10" opacity=".7"/>
  <path d="M320 352 L320 300" stroke="#ffffff" stroke-width="7" stroke-linecap="round"/>
  <path d="M320 352 L362 376" stroke="#0b6bff" stroke-width="6" stroke-linecap="round"/>
  <circle cx="320" cy="352" r="7" fill="#ff5a3c"/>
  <text x="282" y="288" font-family="Arial" font-size="14" fill="#8ad4ff" letter-spacing="2">S9</text>
  <rect x="412" y="314" width="18" height="58" rx="8" fill="#7a8794"/>
  <circle cx="490" cy="410" r="12" fill="#ff5a3c"/>
</g>
SVG,
        'belt' => <<<'SVG'
<g filter="url(#sh)" transform="rotate(-6 320 330)">
  <path d="M112 290 Q112 256 148 256 L470 256 Q506 256 506 290 L506 336 Q506 370 470 370 L148 370 Q112 370 112 336Z" fill="#151b25"/>
  <path d="M130 300 L488 300" stroke="#5b6875" stroke-width="4" stroke-dasharray="6 7" opacity=".7"/>
  <rect x="436" y="262" width="54" height="100" rx="12" fill="#f4f7fb"/>
  <rect x="448" y="286" width="30" height="8" rx="4" fill="#0b2a4a"/>
  <path d="M208 370 L286 370 L298 474 Q300 506 266 506 L236 506 Q204 506 206 474Z" fill="#ffd166"/>
  <path d="M206 404 L298 404 L300 446 L204 446Z" fill="#0b2a4a" opacity=".12"/>
  <text x="228" y="438" font-family="Arial" font-size="24" font-weight="800" fill="#0b2a4a">07</text>
  <rect x="330" y="382" width="120" height="58" rx="14" fill="#0b6bff"/>
  <path d="M352 408 h76 M352 424 h46" stroke="#fff" stroke-width="5" stroke-linecap="round"/>
</g>
SVG,
        'gel' => <<<'SVG'
<g filter="url(#sh)" transform="rotate(-8 320 330)">
  <path d="M232 174 L408 174 L432 226 L432 500 Q432 532 400 532 L240 532 Q208 532 208 500 L208 226Z" fill="url(#obj)"/>
  <path d="M232 174 L408 174 L400 148 L240 148Z" fill="#c5e94f"/>
  <path d="M240 148 L248 120 L400 120 L408 148Z" fill="#f4fbdf"/>
  <rect x="208" y="288" width="224" height="132" fill="#ffffff"/>
  <text x="234" y="332" font-family="Arial" font-size="24" font-weight="800" fill="#3d5a1e">HYDRO</text>
  <text x="234" y="362" font-family="Arial" font-size="15" font-weight="700" fill="#3d5a1e" letter-spacing="2">ENERGY GEL</text>
  <text x="234" y="396" font-family="Arial" font-size="16" fill="#6a7a51">30 g carbs</text>
  <circle cx="396" cy="388" r="20" fill="#3d5a1e"/>
  <path d="M388 388 h16 M396 380 v16" stroke="#d6f35a" stroke-width="3"/>
  <path d="M208 500 L432 500" stroke="#3d5a1e" stroke-opacity=".2" stroke-width="3"/>
</g>
SVG,
        'tub' => <<<'SVG'
<g filter="url(#sh)">
  <path d="M204 228 L436 228 L420 506 Q418 534 390 534 L250 534 Q222 534 220 506Z" fill="url(#obj)"/>
  <ellipse cx="320" cy="228" rx="116" ry="34" fill="#ffb37a"/>
  <ellipse cx="320" cy="222" rx="98" ry="24" fill="#0b2a4a" opacity=".88"/>
  <rect x="206" y="304" width="228" height="150" fill="#ffffff" opacity=".94"/>
  <text x="232" y="348" font-family="Arial" font-size="24" font-weight="800" fill="#0b2a4a">ENDURANCE</text>
  <text x="232" y="378" font-family="Arial" font-size="15" font-weight="700" fill="#ff8a3d" letter-spacing="2">ISOTONIC MIX</text>
  <text x="232" y="414" font-family="Arial" font-size="15" fill="#6b7785">1 kg · 40 doses</text>
  <path d="M232 436 h120" stroke="#0b6bff" stroke-width="6" stroke-linecap="round"/>
  <path d="M320 120 Q380 150 390 220" fill="none" stroke="#ffd166" stroke-width="10" stroke-linecap="round"/>
  <circle cx="320" cy="112" r="20" fill="#ffd166"/>
</g>
SVG,
        'bar' => <<<'SVG'
<g filter="url(#sh)" transform="rotate(-10 320 330)">
  <path d="M168 252 L472 252 L472 414 L168 414Z" fill="url(#obj)"/>
  <path d="M168 252 L142 268 L142 398 L168 414Z" fill="#6d452b"/>
  <path d="M472 252 L498 268 L498 398 L472 414Z" fill="#6d452b"/>
  <path d="M142 280 L142 300 M154 272 L154 306 M486 272 L486 306 M498 280 L498 300" stroke="#3f2718" stroke-width="5"/>
  <rect x="196" y="292" width="246" height="82" fill="#fff7e8" opacity=".94"/>
  <text x="218" y="326" font-family="Arial" font-size="24" font-weight="800" fill="#6d452b">RECOVERY</text>
  <text x="218" y="354" font-family="Arial" font-size="14" font-weight="700" fill="#a5764a" letter-spacing="2">20 g PROTEIN</text>
  <path d="M168 252 L472 290" stroke="#ffd166" stroke-width="24" opacity=".85"/>
  <circle cx="458" cy="400" r="6" fill="#3f2718"/>
</g>
SVG,
        'trisuit' => <<<'SVG'
<g filter="url(#sh)">
  <path d="M258 176 Q274 158 304 158 L336 158 Q366 158 382 176 L424 218 L400 298 L386 284 L390 500 Q390 528 362 532 L278 532 Q250 528 250 500 L254 284 L240 298 L216 218Z" fill="url(#obj)"/>
  <path d="M304 158 L320 194 L336 158" fill="#0b2a4a"/>
  <path d="M320 194 L320 470" stroke="#ff5a3c" stroke-width="5" stroke-dasharray="4 5"/>
  <path d="M252 320 Q320 302 388 320" stroke="#ffffff" stroke-opacity=".4" stroke-width="12"/>
  <path d="M268 400 L292 400 L292 456 L268 456Z" fill="#ffffff" opacity=".16"/>
  <path d="M348 400 L372 400 L372 456 L348 456Z" fill="#ffffff" opacity=".16"/>
  <path d="M366 216 L390 262" stroke="#ff5a3c" stroke-width="8" stroke-linecap="round"/>
  <text x="286" y="516" font-family="Arial" font-size="16" font-weight="800" fill="#fff" opacity=".55" letter-spacing="3">RACE</text>
</g>
SVG,
        'bag' => <<<'SVG'
<g filter="url(#sh)">
  <path d="M150 280 Q150 242 190 238 L450 238 Q490 242 490 280 L490 456 Q490 494 450 496 L190 496 Q150 494 150 456Z" fill="url(#obj)"/>
  <path d="M150 280 Q150 242 190 238 L450 238 Q490 242 490 280 L490 306 L150 306Z" fill="#0b2a4a"/>
  <path d="M238 244 Q238 176 320 176 Q402 176 402 244" fill="none" stroke="#0b2a4a" stroke-width="18" stroke-linecap="round"/>
  <path d="M252 240 Q252 196 320 196 Q388 196 388 240" fill="none" stroke="#0b6bff" stroke-width="8" stroke-linecap="round"/>
  <path d="M150 342 L490 342" stroke="#ffffff" stroke-opacity=".28" stroke-width="4" stroke-dasharray="7 7"/>
  <rect x="268" y="356" width="104" height="58" rx="12" fill="#0b6bff"/>
  <text x="288" y="392" font-family="Arial" font-size="20" font-weight="800" fill="#fff">40 L</text>
  <circle cx="438" cy="412" r="12" fill="#ff5a3c"/>
  <path d="M168 452 Q320 430 472 452" fill="none" stroke="#0b2a4a" stroke-opacity=".25" stroke-width="6"/>
</g>
SVG,
        'laces' => <<<'SVG'
<g filter="url(#sh)">
  <path d="M150 300 Q220 190 310 250 T480 240" fill="none" stroke="#0b6bff" stroke-width="20" stroke-linecap="round"/>
  <path d="M150 360 Q230 460 330 390 T490 400" fill="none" stroke="#ffd166" stroke-width="20" stroke-linecap="round"/>
  <path d="M170 270 L198 300 M202 238 L230 268 M418 220 L446 250" stroke="#0b2a4a" stroke-width="8" stroke-linecap="round"/>
  <rect x="420" y="230" width="64" height="46" rx="16" fill="#151b25"/>
  <rect x="436" y="246" width="32" height="14" rx="7" fill="#0b6bff"/>
  <path d="M470 430 Q500 400 488 360" fill="none" stroke="#0b2a4a" stroke-width="9" stroke-linecap="round"/>
  <circle cx="492" cy="352" r="12" fill="#ff5a3c"/>
  <path d="M140 410 L170 380 M152 452 L182 422" stroke="#0b2a4a" stroke-width="8" stroke-linecap="round"/>
</g>
SVG,
        default => <<<'SVG'
<g filter="url(#sh)">
  <path d="M320 150 L470 450 L170 450Z" fill="none" stroke="url(#obj)" stroke-width="26" stroke-linejoin="round"/>
  <path d="M245 450 L320 300 L395 450" fill="none" stroke="#ff5a3c" stroke-width="16" stroke-linejoin="round"/>
  <circle cx="320" cy="226" r="16" fill="#0b6bff"/>
  <text x="256" y="512" font-family="Arial" font-size="24" font-weight="800" fill="#0b2a4a" opacity=".6" letter-spacing="6">TRIVEX</text>
</g>
SVG,
    };
}

function detailShot(array $p): string
{
    $a = $p['a']; $b = $p['b']; $bg1 = $p['bg1']; $bg2 = $p['bg2'];
    return head($bg1, $bg2, '<style>:root{--a:' . $a . ';--b:' . $b . ';}</style>') . <<<SVG
<g transform="translate(320 310) rotate(-24)">
  <rect x="-190" y="-150" width="380" height="300" rx="60" fill="url(#obj)" filter="url(#sh)"/>
  <path d="M-170 -120 Q0 -40 170 -120 M-170 -70 Q0 10 170 -70 M-170 -20 Q0 60 170 -20 M-170 30 Q0 110 170 30 M-170 80 Q0 160 170 80" fill="none" stroke="#ffffff" stroke-opacity=".18" stroke-width="3"/>
  <path d="M-140 60 Q-40 20 50 70 T160 40" fill="none" stroke="#ffffff" stroke-opacity=".6" stroke-width="2" stroke-dasharray="6 7"/>
  <circle cx="-120" cy="160" r="46" fill="#062a4d" opacity=".15"/>
</g>
<g transform="translate(0 540)">
  <circle cx="250" cy="0" r="18" fill="{$b}"/>
  <circle cx="300" cy="0" r="18" fill="{$a}"/>
  <circle cx="350" cy="0" r="18" fill="#ffffff" stroke="{$a}" stroke-opacity=".3"/>
  <text x="392" y="6" font-family="Arial" font-size="14" font-weight="700" fill="{$a}" letter-spacing="3">PREMIUM BUILD</text>
</g>
SVG . foot();
}

function lifestyleShot(array $p): string
{
    $cat = $p['cat'];
    $accent = $p['b'];
    $sil = match ($cat) {
        'swim' => '<path d="M150 410 Q230 340 310 380 Q370 408 450 372" fill="none" stroke="#fff" stroke-width="16" stroke-linecap="round"/><circle cx="452" cy="360" r="26" fill="#fff"/><path d="M400 384 Q440 410 480 396" fill="none" stroke="#fff" stroke-width="12" stroke-linecap="round"/><path d="M80 480 Q160 450 240 478 T400 478 T560 470" fill="none" stroke="#fff" stroke-opacity=".35" stroke-width="8"/>',
        'bike' => '<circle cx="230" cy="450" r="70" fill="none" stroke="#fff" stroke-width="10"/><circle cx="450" cy="450" r="70" fill="none" stroke="#fff" stroke-width="10"/><path d="M230 450 L300 330 L380 450 L230 450 M300 330 L420 320 L450 450 M380 450 L420 320" fill="none" stroke="#fff" stroke-width="10" stroke-linejoin="round"/><circle cx="330" cy="250" r="24" fill="#fff"/><path d="M330 274 L300 330 M330 290 L380 310" stroke="#fff" stroke-width="10" stroke-linecap="round"/>',
        'run' => '<circle cx="360" cy="220" r="30" fill="#fff"/><path d="M344 252 L318 330 L360 360 L338 450 M318 330 L270 390 M360 360 L414 330 M338 450 L390 480" fill="none" stroke="#fff" stroke-width="18" stroke-linecap="round" stroke-linejoin="round"/>',
        'nutrition' => '<path d="M250 470 Q280 300 340 270 Q400 240 430 330 Q450 410 410 470Z" fill="none" stroke="#fff" stroke-width="12"/><path d="M300 470 Q310 380 350 340" fill="none" stroke="#fff" opacity=".5" stroke-width="8"/><circle cx="450" cy="250" r="22" fill="#fff"/><path d="M200 460 Q260 420 320 460" fill="none" stroke="#fff" stroke-opacity=".4" stroke-width="8"/>',
        default => '<path d="M320 190 L470 470 L170 470Z" fill="none" stroke="#fff" stroke-width="16" stroke-linejoin="round"/><path d="M250 470 L320 320 L390 470" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="10"/>',
    };
    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="640" height="640" role="img">
<defs>
  <linearGradient id="life" x1="0" y1="0" x2="1" y2="1">
    <stop offset="0" stop-color="#062a4d"/><stop offset=".55" stop-color="#0b3d66"/><stop offset="1" stop-color="#0b6bff"/>
  </linearGradient>
  <radialGradient id="sun" cx=".5" cy=".5" r=".5">
    <stop offset="0" stop-color="{$accent}" stop-opacity=".95"/><stop offset="1" stop-color="{$accent}" stop-opacity="0"/>
  </radialGradient>
</defs>
<rect width="640" height="640" fill="url(#life)"/>
<circle cx="320" cy="300" r="250" fill="url(#sun)" opacity=".55"/>
<circle cx="470" cy="180" r="88" fill="{$accent}" opacity=".9"/>
<path d="M0 520 Q160 470 320 510 T640 500 V640 H0Z" fill="#041e36" opacity=".8"/>
<g opacity=".95">{$sil}</g>
<path d="M0 250 Q120 210 240 250 T480 250 T720 250" fill="none" stroke="#fff" stroke-opacity=".08" stroke-width="2"/>
<path d="M0 300 Q120 260 240 300 T480 300 T720 300" fill="none" stroke="#fff" stroke-opacity=".06" stroke-width="2"/>
<text x="48" y="92" font-family="Arial" font-size="18" font-weight="700" fill="#fff" opacity=".55" letter-spacing="6">TRIVEX</text>
<text x="48" y="122" font-family="Arial" font-size="13" fill="#8ad4ff" opacity=".8" letter-spacing="3">SWIM · BIKE · RUN</text>
</svg>
SVG;
}

foreach ($products as $slug => $p) {
    $extra = '<style>:root{--a:' . $p['a'] . ';--b:' . $p['b'] . ';}</style>';
    $svg = head($p['bg1'], $p['bg2'], $extra) . art($p['type']) . foot();
    file_put_contents("$root/products/$slug.svg", $svg);
    file_put_contents("$root/products/$slug-2.svg", detailShot($p));
    file_put_contents("$root/products/$slug-3.svg", lifestyleShot($p));
    echo "✓ products/$slug.svg\n";
}

/* ----------------------------- Category art ----------------------------- */
$categories = [
    'swim' => ['#062a4d', '#0b6bff', 'NATAÇÃO', 'SWIM', '<path d="M80 430 Q200 340 330 400 Q450 456 570 390" fill="none" stroke="#fff" stroke-width="14" stroke-linecap="round"/><circle cx="560" cy="368" r="24" fill="#4fe0ff"/><path d="M470 404 Q530 448 590 424" fill="none" stroke="#fff" stroke-width="10" stroke-linecap="round"/><path d="M40 520 Q160 480 280 512 T520 508 T760 496" fill="none" stroke="#8ad4ff" stroke-opacity=".35" stroke-width="6"/>'],
    'bike' => ['#062a4d', '#0b3d66', 'CICLISMO', 'BIKE', '<circle cx="240" cy="440" r="92" fill="none" stroke="#fff" stroke-width="11"/><circle cx="540" cy="440" r="92" fill="none" stroke="#fff" stroke-width="11"/><path d="M240 440 L330 280 L430 440 L240 440 M330 280 L490 268 L540 440 M430 440 L490 268" fill="none" stroke="#fff" stroke-width="12" stroke-linejoin="round"/><circle cx="350" cy="200" r="26" fill="#ff5a3c"/><path d="M350 226 L330 280 M350 240 L410 260" stroke="#fff" stroke-width="11" stroke-linecap="round"/>'],
    'run' => ['#0b2a4a', '#0b6bff', 'CORRIDA', 'RUN', '<circle cx="380" cy="190" r="34" fill="#fff"/><path d="M360 232 L320 330 L380 370 L350 480 M320 330 L250 410 M380 370 L450 320 M350 480 L420 520" fill="none" stroke="#fff" stroke-width="21" stroke-linecap="round" stroke-linejoin="round"/><path d="M60 540 Q180 500 300 532 T560 528 T800 520" fill="none" stroke="#ffd166" stroke-opacity=".5" stroke-width="8"/>'],
    'nutrition' => ['#0b2a4a', '#155680', 'NUTRIÇÃO', 'FUEL', '<path d="M280 470 Q310 290 390 250 Q460 216 490 320 Q514 418 460 480Z" fill="none" stroke="#fff" stroke-width="12"/><path d="M330 470 Q350 370 400 320" fill="none" stroke="#d6f35a" stroke-width="9"/><circle cx="530" cy="220" r="28" fill="#ffd166"/><circle cx="170" cy="300" r="18" fill="#4fe0ff"/><circle cx="140" cy="380" r="10" fill="#ffd166"/>'],
    'equipment' => ['#062a4d', '#0b2a4a', 'EQUIPAMENTO', 'GEAR', '<path d="M360 170 L560 540 L160 540Z" fill="none" stroke="#fff" stroke-width="16" stroke-linejoin="round"/><path d="M260 540 L360 350 L460 540" fill="none" stroke="#ff5a3c" stroke-width="11" stroke-linejoin="round"/><circle cx="360" cy="300" r="22" fill="#4fe0ff"/>'],
];

foreach ($categories as $slug => [$c1, $c2, $pt, $en, $art]) {
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 640" width="800" height="640" role="img">
<defs>
  <linearGradient id="cg" x1="0" y1="0" x2="1" y2="1">
    <stop offset="0" stop-color="{$c1}"/><stop offset="1" stop-color="{$c2}"/>
  </linearGradient>
  <radialGradient id="cglow" cx=".75" cy=".3" r=".7">
    <stop offset="0" stop-color="#ffffff" stop-opacity=".16"/><stop offset="1" stop-color="#ffffff" stop-opacity="0"/>
  </radialGradient>
</defs>
<rect width="800" height="640" fill="url(#cg)"/>
<circle cx="600" cy="190" r="320" fill="url(#cglow)"/>
<g opacity=".18" stroke="#fff" stroke-width="1">
  <path d="M0 480 L800 300 M0 540 L800 360 M0 600 L800 420"/>
  <path d="M100 0 L400 640 M300 0 L600 640 M500 0 L800 640"/>
</g>
<g>{$art}</g>
<text x="56" y="92" font-family="Arial" font-size="15" font-weight="700" fill="#8ad4ff" letter-spacing="6">TRIVEX</text>
<text x="56" y="570" font-family="Arial" font-size="62" font-weight="800" fill="#ffffff" letter-spacing="1">{$pt}</text>
<text x="58" y="606" font-family="Arial" font-size="16" font-weight="700" fill="#8ad4ff" letter-spacing="8">{$en}</text>
<path d="M56 540 L160 540" stroke="#ff5a3c" stroke-width="6" stroke-linecap="round"/>
</svg>
SVG;
    file_put_contents("$root/categories/$slug.svg", $svg);
    echo "✓ categories/$slug.svg\n";
}

echo "\nDone. Assets written to public/assets/img\n";
