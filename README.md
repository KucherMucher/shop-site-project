# TRIVEX — Triathlon Store

Loja de e-commerce de triatlo inspirada na experiência da Decathlon, construída de raiz em **PHP 8 (OOP/MVC)**, **MySQL** e **JavaScript puro**. O projeto é autónomo: não depende de Composer, CDNs, frameworks de frontend ou imagens externas. Toda a arte visual é SVG próprio.

A interface está disponível em **Português (PT-PT)** e **Inglês**, com temas **claro/escuro**, promoções sazonais de **Halloween** e três áreas distintas: loja, conta de cliente e backoffice.

---

## Índice

- [Destaques](#destaques)
- [Stack técnica](#stack-técnica)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Contas de demonstração](#contas-de-demonstração)
- [Funcionalidades](#funcionalidades)
- [Perfis e permissões](#perfis-e-permissões)
- [Fluxos de negócio](#fluxos-de-negócio)
- [Arquitetura](#arquitetura)
- [Estrutura de pastas](#estrutura-de-pastas)
- [Configuração de e-mail](#configuração-de-e-mail)
- [Base de dados](#base-de-dados)
- [Segurança](#segurança)
- [Testes rápidos](#testes-rápidos)
- [Diagramas Mermaid](#diagramas-mermaid)
- [Documentação adicional](#documentação-adicional)

---

## Destaques

- **Loja completa**: homepage editorial, catálogo com filtros, pesquisa instantânea, detalhe de produto, variantes, stock em tempo real, promoções e páginas CMS.
- **Compra sem atrito**: carrinho persistente, checkout como convidado ou autenticado, moradas guardadas, pagamentos simulados (cartão, MB WAY, Multibanco), confirmação e e-mail transacional.
- **Conta de cliente**: perfil, moradas, histórico de encomendas, detalhe de encomenda e avaliações.
- **Backoffice por perfis**: administrador, moderador e empresa, com fluxos de aprovação diferentes.
- **Chat de suporte em tempo real** por polling, com fila de moderadores, estados, conclusão e arquivo automático.
- **Alterações de moderador sujeitas a aprovação**: stock, encomendas, utilizadores, empresas, propostas e páginas passam por `pending_modifications`; o admin aplica de imediato.
- **Empresas parceiras**: registo, aprovação manual, painel de empresa, submissão e edição de produtos com variantes, imagem principal, galeria e fotos por cor, e acompanhamento de propostas.
- **Internacionalização nativa** com ficheiros PHP (`lang/pt.php` e `lang/en.php`) e conteúdo de produto em colunas `_pt` / `_en`.
- **Tema claro/escuro**, layout responsivo, acessibilidade básica, SVGs locais e design system com variáveis CSS.
- **E-mails locais**: driver `log` por omissão, com mailbox no backoffice; suporte opcional para SMTP (Mailpit) e `mail()` nativo.
- **Segurança**: PDO com prepared statements, CSRF em formulários, passwords com `password_hash`, sessões `HttpOnly`/`SameSite`, throttling em login/registo/checkout e validação de uploads.

---

## Stack técnica

| Camada | Tecnologia |
|---|---|
| Linguagem | PHP 8.3+ (OOP, `declare(strict_types=1)`) |
| Arquitetura | MVC próprio, PSR-4, front controller, router com parâmetros e middleware |
| Base de dados | MySQL 8.x / MariaDB 10.6+, PDO, `utf8mb4` |
| Frontend | HTML5, CSS3 com variáveis, JavaScript vanilla (ES6+) |
| Internacionalização | Dicionários nativos `lang/pt.php` e `lang/en.php` |
| E-mail | Driver `log` (padrão), SMTP ou `mail()` nativo |
| Arte | 60 SVGs de produto, 5 SVGs de categoria e logótipo, todos locais |
| Servidor local | Laragon (Apache/Nginx) ou PHP built-in server |

Sem Composer, npm, Tailwind, jQuery ou bibliotecas de terceiros em runtime.

---

## Requisitos

- PHP **8.1+** (recomendado 8.3) com:
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `fileinfo` (recomendado para uploads)
- MySQL **8.0+** ou MariaDB **10.6+**
- Apache com `mod_rewrite` **ou** o servidor embutido do PHP
- Extensão `gd` não é necessária; as imagens são SVGs e os uploads validam o tipo real com `getimagesize()`

---

## Instalação

### 1. Colocar o projeto no Laragon

Coloca a pasta em:

```text
C:\laragon\www\projeto
```

O virtual host do Laragon pode apontar para a raiz do projeto. O `.htaccess` da raiz reescreve tudo para `public/index.php` e expõe apenas os assets públicos.

Se preferires apontar o DocumentRoot diretamente para `public/`, também funciona.

### 2. Configurar o ambiente

Copia `.env.example` para `.env` (se ainda não existir) e ajusta os valores:

```dotenv
APP_NAME="TRIVEX"
APP_ENV=local
APP_DEBUG=true
APP_URL=
APP_TIMEZONE=Europe/Lisbon

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=trivex
DB_USERNAME=root
DB_PASSWORD=

MAIL_DRIVER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_FROM=hello@trivex.test
MAIL_FROM_NAME="TRIVEX"

CURRENCY=EUR
```

> `APP_URL` vazio faz o sistema detetar automaticamente o `BASE_URL`. Em produção, define o URL absoluto.

### 3. Criar e popular a base de dados

Com o MySQL do Laragon a correr:

```bash
php database/install.php
```

O script **apaga e recria** a base `trivex`, importa `database/database.sql` e mostra as contas de demonstração. No fim, deverás ver:

```text
roles        4
users        8
products     19
variants     44
orders       5
```

> Atenção: `database/install.php` é destrutivo. Não executar sobre dados reais.

### 4. Arrancar a aplicação

**Com Laragon/Apache**, abre:

```text
http://projeto.test
```

**Com o servidor embutido do PHP** (recomendado para desenvolvimento rápido):

```bash
php -S 127.0.0.1:8090 -t public public/router.php
```

Depois abre `http://127.0.0.1:8090`.

> No servidor embutido é obrigatório indicar `public/router.php`; caso contrário, as rotas amigáveis devolvem 404.

### 5. (Opcional) Mailpit

Para ver os e-mails numa caixa local em vez de apenas no log:

```dotenv
MAIL_DRIVER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
```

O Laragon inclui normalmente o Mailpit em `http://127.0.0.1:8025`. Sem SMTP, todos os e-mails ficam registados em `email_log` e em `storage/mail/`, e podem ser consultados em **Admin → Mailbox**.

### 6. Enviar e-mails reais pelo Gmail

O envio autenticado por SMTP está implementado em `src/Core/Mailer.php` (AUTH LOGIN + STARTTLS/SSL). Para entregar em caixas Gmail reais, cria uma **palavra-passe de aplicação** na conta Google (não uses a palavra-passe normal) e preenche o `.env`:

```dotenv
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=a-tua-conta@gmail.com
MAIL_PASSWORD=xxxxxxxxxxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM=a-tua-conta@gmail.com
MAIL_FROM_NAME="TRIVEX"
```

Para a porta 465 usa `MAIL_PORT=465` e `MAIL_ENCRYPTION=ssl`. O endereço em `MAIL_FROM` deve ser a conta autenticada ou um alias verificado nessa conta. Remove os espaços da palavra-passe de aplicação antes de a colar no `.env`. Depois de alterar o `.env`, reinicia o servidor PHP e confirma o estado em **Admin → Mailbox** (`sent` ou `failed`).

---

## Contas de demonstração

| Perfil | E-mail | Password | Notas |
|---|---|---|---|
| Administrador | `admin@trivex.pt` | `Admin123!` | Acesso total, aprovações, moderadores, mailbox |
| Moderador | `moderador@trivex.pt` | `Mod12345!` | Backoffice operacional; alterações ficam pendentes |
| Cliente | `cliente@trivex.pt` | `Cliente123!` | Conta verificada, encomendas e chat |
| Empresa | `empresa@trivex.pt` | `Empresa123!` | Empresa aprovada, painel e propostas |
| Empresa pendente | `tiago@oceanonorte.pt` | `Empresa123!` | Não inicia sessão até aprovação do admin |

Sugestão de demonstração: entra como **admin**, aprova a empresa *Oceano Norte Sports* em **Admin → Empresas**, e depois inicia sessão com `tiago@oceanonorte.pt`.

---

## Funcionalidades

### Loja

- **Homepage**: hero, disciplinas, categorias, produtos em destaque, novidades, bloco de promoções de Halloween, prova social, vantagens e newsletter.
- **Catálogo** (`/loja`): pesquisa por texto, filtros por categoria, preço mínimo/máximo, marca, tamanho, disponibilidade e promoção; ordenação e paginação.
- **Categorias** (`/categoria/{slug}`): navegação por natação, ciclismo, corrida, nutrição e equipamento.
- **Pesquisa** (`/pesquisa?q=...`): resultados com filtros e endpoint JSON para sugestões instantâneas (`/api/pesquisa`).
- **Produto** (`/produto/{slug}`): galeria com thumbnails e zoom, variantes de cor/tamanho, preço e stock atualizados, especificações, badges, avaliações e produtos relacionados.
- **Promoções** (`/promocoes`): campanha de Halloween com contagem decrescente, descontos e produtos em promoção.
- **Páginas CMS** (`/pagina/{slug}`): Sobre, Devoluções, Guia de Transição e páginas adicionais geridas no backoffice.
- **Carrinho**: quantidades, remoção, subtotal, envio grátis a partir do limite configurado e validação de stock.
- **Checkout**: convidado ou autenticado, morada de envio e faturação, notas, métodos simulados de pagamento, criação transacional da encomenda, redução de stock e e-mail de confirmação.
- **Confirmação**: página de sucesso protegida por sessão ou proprietário da encomenda.
- **Newsletter**: subscrição simulada com feedback e throttling.

### Conta de cliente

- Painel com resumo de encomendas, total gasto e produtos comprados.
- Histórico de encomendas e detalhe com linhas, moradas, pagamento, estado e código de seguimento.
- Edição de perfil e password.
- Gestão de moradas: criar, eliminar e definir predefinida.
- Avaliações: criar ou atualizar review por produto (uma por cliente).
- Suporte: abrir conversa, listar conversas e responder em tempo real.

### Backoffice — Administrador

- Dashboard com métricas de vendas, encomendas, utilizadores, stock e chats.
- Utilizadores: listagem com pesquisa, estatísticas de compras e alteração de estado.
- Stock: pesquisa e atualização de variantes, com alertas de stock baixo.
- Encomendas: listagem, detalhe, alteração de estado (`placed`, `shipped`, `received`, `lost`, `not_received`) e tracking.
- Empresas: aprovação/rejeição, ativação do proprietário e nota de revisão.
- Propostas: aprovação/rejeição de produtos submetidos por empresas.
- Páginas: ativação/desativação de páginas CMS; a homepage está protegida e não pode ser desativada.
- Moderadores: criação, listagem e ativação/suspensão.
- Aprovações: fila de alterações submetidas por moderadores, com aprovar/rejeitar e nota.
- Notificações: feed de eventos do sistema.
- Mailbox: leitura de todos os e-mails registados.
- Chats: fila ativa, arquivo, entrada/saída, resposta, heartbeat e conclusão.

### Backoffice — Moderador

- Acesso ao dashboard e às áreas operacionais.
- Pode propor alterações de stock, estado de encomendas, estado de utilizadores, empresas, propostas e páginas.
- As alterações **não são aplicadas diretamente**: ficam em `pending_modifications` até aprovação de um admin.
- O chat é a exceção: pode entrar, responder, sair e concluir sem aprovação.
- Não tem acesso a **Moderadores**, **Aprovações** nem **Mailbox**.

### Empresa

- Registo com dados fiscais e de contacto; conta fica `pending`.
- Aprovação manual pelo admin; o proprietário é ativado e notificado.
- Painel com produtos, stock, unidades vendidas, receita e encomendas relacionadas.
- Submissão de novos produtos com nome PT/EN, categoria, preço, promoção, marca, descrição, especificações, variantes e imagem.
- Edição de produtos existentes, incluindo informação, materiais, variantes, stock e fotografias.
- Imagem principal, galeria com múltiplas imagens e fotografias específicas por cor/variante.
- Pré-visualização imediata das imagens, remoção de imagens existentes e repetidores funcionais para variantes e especificações.
- Produtos submetidos ficam `pending` até aprovação; o estado e a nota de rejeição são visíveis no painel.
- Ao editar, um produto `active` mantém-se `active`; um produto `rejected` volta a `pending`.

---

## Perfis e permissões

| Capacidade | Visitante | Cliente | Empresa | Moderador | Administrador |
|---|---:|---:|---:|---:|---:|
| Navegar catálogo, pesquisa, promoções e CMS | Sim | Sim | Sim | Sim | Sim |
| Carrinho e checkout (convidado ou autenticado) | Sim | Sim | Sim | Sim | Sim |
| Histórico, perfil e moradas | Não | Sim | Sim | Sim | Sim |
| Avaliações de produtos | Não | Sim | Sim | Sim | Sim |
| Chat de suporte | Não¹ | Sim | Sim | Sim² | Sim² |
| Painel de empresa e propostas | Não | Não | Sim³ | Não | Não |
| Backoffice operacional | Não | Não | Não | Sim | Sim |
| Alterações operacionais | Não | Não | Não | Pendentes | Imediatas |
| CRUD de moderadores | Não | Não | Não | Não | Sim |
| Fila de aprovações | Não | Não | Não | Não | Sim |
| Mailbox | Não | Não | Não | Não | Sim |

¹ O visitante pode ver a página de suporte, mas para abrir conversa tem de iniciar sessão.
² Staff responde no backoffice; o cliente vê a conversa na loja.
³ Apenas após aprovação manual da empresa.

A matriz visual e os detalhes de implementação estão em [`docs/diagrams.md`](docs/diagrams.md).

---

## Fluxos de negócio

### Compra

1. O cliente adiciona variantes ao carrinho (sessão ou conta).
2. O checkout valida dados, stock e método de pagamento.
3. A encomenda é criada numa transação: cabeçalho, linhas e redução de stock com `FOR UPDATE`.
4. O pagamento é simulado: cartão e MB WAY ficam `paid`; Multibanco fica `pending`.
5. O e-mail de confirmação é renderizado e registado/enviado.
6. O carrinho é limpo e o cliente vê a página de sucesso.
7. Clientes autenticados acompanham a encomenda em `/conta/encomendas`; convidados usam a confirmação da sessão.
8. O admin/moderador atualiza o estado e o cliente recebe uma notificação.

### Aprovação de alterações

- **Admin** → `ModificationService::submit()` aplica imediatamente.
- **Moderador** → grava em `pending_modifications`, notifica os admins e aguarda decisão.
- **Admin aprova** → aplica a alteração, marca como `approved` e notifica o moderador.
- **Admin rejeita** → marca como `rejected` e notifica o moderador.
- O chat **não** passa por este fluxo.

### Chat

- O cliente abre conversa (`open`).
- Um moderador/admin entra (`in_progress`) e faz heartbeat.
- Se sair, a conversa volta a `open`.
- Sem heartbeat durante 2 minutos, o sweep também a devolve a `open`.
- “Concluir” passa a `concluded`.
- Após 10 minutos, o sweep arquiva (`archived`).

---

## Arquitetura

```text
Browser
   │
   ▼
public/index.php  ──►  src/bootstrap.php  ──►  Router + Middleware
                                                   │
                                                   ▼
                                             Controllers
                                              │        │
                                              ▼        ▼
                                           Models    Services
                                              │        │
                                              ▼        ▼
                                         MySQL/PDO   Mailer / View
                                                       │
                                                       ▼
                                              storage/mail + email_log
```

- **Front controller** em `public/index.php`.
- **Router** com padrões `{param}`, nomes de rota e middleware (`auth`, `guest`, `role`, `company`, `throttle`).
- **Controllers** finos, com validação de input e renderização de views.
- **Models** encapsulam SQL com PDO e prepared statements.
- **Services** concentram regras transversais (`ModificationService`, `MailService`).
- **Views** PHP com layouts (`app`, `auth`, `account`, `admin`, `company`) e partials reutilizáveis.
- **Core** inclui `Auth`, `Session`, `Csrf`, `Validator`, `Lang`, `Mailer`, `View`, `Database`, `Router` e `Middleware`.

Mais diagramas: [`docs/diagrams.md`](docs/diagrams.md).

---

## Estrutura de pastas

```text
projeto/
├── .env.example
├── .htaccess
├── README.md
├── config/
│   └── config.php
├── database/
│   ├── database.sql          # schema + seeders
│   ├── install.php           # instalação/reset
│   ├── generate_images.php   # gerador dos SVGs
│   └── scan_lang.php         # verificação de chaves de tradução
├── docs/
│   ├── MANUAL.md             # manual de instruções (EN)
│   ├── MANUAL-PT.md          # manual de instruções (PT)
│   ├── REPORT.md             # relatório técnico + registo de prompts (EN)
│   ├── RELATORIO-PT.md       # relatório técnico + registo de prompts (PT)
│   ├── TRIVEX-Presentation.pptx
│   ├── TRIVEX-Apresentacao-PT.pptx
│   └── diagrams.md           # ERD, permissões, chat, compra
├── lang/
│   ├── pt.php
│   └── en.php
├── public/
│   ├── index.php
│   ├── router.php
│   └── assets/
│       ├── css/              # app, account, admin, company, auth
│       ├── img/              # produtos, categorias, logo, uploads
│       └── js/               # app, product, chat, admin
├── src/
│   ├── bootstrap.php
│   ├── routes.php
│   ├── Controllers/
│   ├── Core/
│   ├── Models/
│   ├── Support/
│   └── Views/
├── storage/
│   └── mail/                 # e-mails em modo log
└── .gitignore
```

---

## Configuração de e-mail

O driver é escolhido em `MAIL_DRIVER`:

| Driver | Comportamento |
|---|---|
| `log` (padrão) | Renderiza o HTML, grava em `email_log` e em `storage/mail/` |
| `smtp` | Envia por SMTP para `MAIL_HOST:MAIL_PORT` |
| `mail` | Usa a função nativa `mail()` do PHP |

Em todos os casos, o resultado é registado em `email_log` (quando aplicável) e pode ser consultado em **Admin → Mailbox**. Isto permite testar verificação de conta, confirmação de encomenda, aprovação de empresa e notificações sem depender de um serviço externo.

---

## Base de dados

O schema está em `database/database.sql` e inclui 17 tabelas:

`roles`, `companies`, `users`, `categories`, `products`, `product_variants`, `product_color_images`, `addresses`, `orders`, `order_items`, `reviews`, `pages`, `chats`, `chat_messages`, `pending_modifications`, `notifications` e `email_log`.

O seed cria:

- 4 perfis (`admin`, `moderator`, `company`, `user`)
- 8 utilizadores
- 3 empresas (1 pendente)
- 5 categorias
- 19 produtos com 44 variantes
- 5 encomendas de demonstração
- Páginas CMS, reviews, chats e notificações de exemplo

Para restaurar o estado limpo:

```bash
php database/install.php
```

> O comando elimina a base atual. Guarda dados importantes antes de executar.

### Estados principais

- **Encomenda**: `placed` → `shipped` → `received`; alternativos `lost`, `not_received`.
- **Pagamento**: `pending`, `paid`, `refunded` (conforme o método simulado).
- **Empresa**: `pending`, `approved`, `rejected`.
- **Produto**: `active`, `pending`, `rejected`.
- **Utilizador**: `pending`, `active`, `suspended`.
- **Chat**: `open`, `in_progress`, `concluded`, `archived`.
- **Moderação**: `pending`, `approved`, `rejected`.

---

## Segurança

- **PDO + prepared statements** em todas as consultas com input.
- **CSRF** em formulários POST através de `csrf_field()` / `Csrf::verify()`.
- **Passwords** com `password_hash()` e `password_verify()`.
- **Sessões** com `HttpOnly`, `SameSite=Lax` e regeneração de ID no login.
- **Middlewares** de autenticação, perfil, empresa aprovada e throttling.
- **Escaping** de output com `e()` e helpers de view.
- **Uploads** validados por tamanho, `getimagesize()` e extensão permitida; nomes aleatórios.
- **Cabeçalhos** `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` e `X-XSS-Protection`.
- **Preço e stock** são recalculados no servidor; nunca confiar apenas no formulário.
- **Confirmação de encomenda** acessível apenas por sessão ou proprietário.
- **Homepage protegida** contra desativação acidental no backoffice.

---

## Testes rápidos

### Verificação de sintaxe

```bash
php -l public/index.php
php -l src/routes.php
php database/scan_lang.php
```

> O `scan_lang.php` pode assinalar falsos positivos em chaves construídas dinamicamente (por exemplo, `order.status.{estado}`).

### Smoke test manual

1. Abre `/` e alterna **PT/EN** e **tema claro/escuro**.
2. Pesquisa “barra” e aplica filtros de preço, marca, tamanho e stock.
3. Abre um produto, escolhe variante, adiciona ao carrinho e finaliza checkout como convidado.
4. Confirma a encomenda em `/checkout/sucesso/{número}` e o e-mail em **Admin → Mailbox**.
5. Inicia sessão como cliente e consulta `/conta/encomendas`.
6. Abre um chat como cliente; entra como admin em **Admin → Chats**, responde e conclui.
7. Entra como moderador, altera stock; confirma que fica pendente em **Admin → Aprovações**.
8. Como admin, aprova a alteração e verifica o novo stock.
9. Como empresa, submete um produto; como admin, aprova em **Admin → Propostas**.
10. Desativa uma página CMS e confirma o 404; tenta desativar a homepage e confirma que é bloqueado.

### Verificação de assets

Todos os assets são locais. O logótipo está em `public/assets/img/logo.svg`; os produtos e categorias em `public/assets/img/products/` e `public/assets/img/categories/`.

---

## Diagramas Mermaid

Os diagramas completos estão em **[`docs/diagrams.md`](docs/diagrams.md)**:

- **ERD** com as 17 tabelas e relações principais.
- **Matriz de permissões** visual e tabela detalhada.
- **Fluxo de estados do chat** (`Aberto` → `Em progresso` → `Concluído` → `Arquivado`).
- **Fluxo de compra e histórico**, incluindo convidado, cliente autenticado, e-mail, stock e notificações.
- **Fluxo de aprovação de moderadores** e **arquitetura de runtime**.
- **Fluxo de imagens do produto**, incluindo imagem principal, galeria e fotos por cor.

---

## Documentação adicional

- [`docs/MANUAL.md`](docs/MANUAL.md) — manual de instruções para instalação, operação e manutenção (EN).
- [`docs/MANUAL-PT.md`](docs/MANUAL-PT.md) — manual de instruções para instalação, operação e manutenção (PT).
- [`docs/REPORT.md`](docs/REPORT.md) — relatório técnico, funcional e registo de desenvolvimento do projeto (EN).
- [`docs/RELATORIO-PT.md`](docs/RELATORIO-PT.md) — relatório técnico, funcional e registo de desenvolvimento do projeto (PT).
- [`docs/TRIVEX-Presentation.pptx`](docs/TRIVEX-Presentation.pptx) — apresentação do projeto, objetivos e mecânicas principais (EN).
- [`docs/TRIVEX-Apresentacao-PT.pptx`](docs/TRIVEX-Apresentacao-PT.pptx) — apresentação do projeto, objetivos e mecânicas principais (PT).
- [`docs/diagrams.md`](docs/diagrams.md) — diagramas Mermaid de arquitetura, dados e fluxos.

---

## Notas

- Projeto académico/demonstração, sem integração real de pagamentos, transportadoras ou faturação.
- Os pagamentos são simulados e não recolhem dados de cartão reais.
- As imagens e o logótipo são SVGs originais criados para o projeto.
- O e-mail padrão é `log` para permitir uma demonstração totalmente offline.
- O backoffice assume um único papel por utilizador; a empresa é sempre aprovada manualmente por um administrador.

---

**TRIVEX** — swim · bike · run
