<?php /** @var string $title */ ?>
<!DOCTYPE html>
<html lang="<?= e(locale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Base de dados') ?> · TRIVEX</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: radial-gradient(circle at 20% 20%, #0b3f73, #062a4d 45%, #031a30 100%);
            color: #e8f1fb;
        }
        .card {
            width: min(620px, 100%);
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 22px;
            padding: 38px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .45);
            backdrop-filter: blur(12px);
        }
        .brand { font-size: 24px; font-weight: 800; letter-spacing: .18em; }
        .brand span { color: #4fe0ff; }
        .tag { display: inline-block; margin: 18px 0 10px; padding: 6px 12px; border-radius: 999px; background: rgba(255, 176, 32, .16); color: #ffcf7a; font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        h1 { margin: 0 0 12px; font-size: 28px; line-height: 1.2; }
        p { margin: 0 0 14px; color: #a9c2d9; line-height: 1.7; font-size: 14px; }
        ol { margin: 18px 0 24px; padding-left: 20px; color: #c9dcee; font-size: 14px; line-height: 2; }
        code { background: rgba(255, 255, 255, .1); padding: 3px 8px; border-radius: 6px; font-size: 13px; color: #8fe3ff; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 24px; }
        a { display: inline-block; padding: 12px 20px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 13px; }
        .primary { background: #0b6bff; color: #fff; }
        .ghost { border: 1px solid rgba(255, 255, 255, .2); color: #dbeaf8; }
        .dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #ff5c7a; margin-right: 8px; box-shadow: 0 0 0 4px rgba(255, 92, 122, .18); }
    </style>
</head>
<body>
    <main class="card">
        <div class="brand">TRI<span>VEX</span></div>
        <span class="tag"><span class="dot"></span> database offline</span>
        <h1>A ligação à base de dados falhou</h1>
        <p>Não foi possível ligar à base de dados MySQL <code>trivex</code>. A aplicação precisa dela para carregar o catálogo e o backoffice.</p>
        <ol>
            <li>Inicia o MySQL no Laragon.</li>
            <li>Confirma as credenciais no ficheiro <code>.env</code>.</li>
            <li>Executa <code>php database/install.php</code> para criar e popular a base de dados.</li>
            <li>Recarrega esta página.</li>
        </ol>
        <div class="actions">
            <a class="primary" href="/">Tentar novamente</a>
            <a class="ghost" href="/admin">Backoffice</a>
        </div>
    </main>
</body>
</html>
