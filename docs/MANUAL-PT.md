# TRIVEX — Manual de Instruções

**Projeto:** TRIVEX — Loja de Triatlo  
**Versão:** 1.0  
**Data:** Novembro de 2026  
**Público:** developers, operadores, administradores e utilizadores de demonstração

---

## 1. O que é o TRIVEX?

O TRIVEX é uma plataforma de comércio eletrónico de triatlo, autónoma, construída com **PHP 8**, **MySQL** e **JavaScript puro**. Inclui:

- uma loja pública;
- uma área de cliente;
- um portal para empresas parceiras;
- um backoffice administrativo;
- um chat de suporte;
- localização PT/EN e temas claro/escuro.

O projeto **não** exige Composer, npm, CDNs, frameworks de frontend ou imagens externas em runtime. Todos os assets visuais são SVGs locais, exceto as imagens carregadas pelas empresas.

---

## 2. Requisitos

| Componente | Requisito |
|---|---|
| PHP | 8.1+ (recomendado 8.3) |
| Extensões PHP | `pdo_mysql`, `mbstring`, `openssl` |
| Base de dados | MySQL 8.0+ ou MariaDB 10.6+ |
| Servidor web | Laragon/Apache com `mod_rewrite`, ou o servidor embutido do PHP |
| Navegador | Navegador moderno com JavaScript ativo |

Não é necessária a extensão `gd`. As imagens carregadas são validadas com `getimagesize()`.

---

## 3. Instalação

### 3.1 Colocar o projeto

Caminho recomendado no Laragon:

```text
C:\laragon\www\projeto
```

O `.htaccess` da raiz reescreve os pedidos para `public/index.php`. Em alternativa, aponta o virtual host diretamente para `public/`.

### 3.2 Configurar o ambiente

Copia `.env.example` para `.env` e ajusta:

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
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=
MAIL_FROM=hello@trivex.test
MAIL_FROM_NAME="TRIVEX"

CURRENCY=EUR
```

Notas:

- Deixa `APP_URL` vazio para deteção automática do URL base.
- Mantém `MAIL_DRIVER=log` para demonstrações offline.
- Nunca coloques credenciais reais no repositório.

### 3.3 Criar e popular a base de dados

Inicia o MySQL e executa:

```bash
php database/install.php
```

> **Atenção:** este comando apaga e recria a base de dados `trivex`. Não o executes sobre dados de produção.

Resumo esperado do seed:

```text
roles        4
users        8
products     19
variants     44
orders       5
```

A base de dados de demonstração atual pode conter dados de teste adicionais.

### 3.4 Iniciar a aplicação

Com Laragon/Apache:

```text
http://projeto.test
```

Com o servidor embutido do PHP:

```bash
php -S 127.0.0.1:8090 -t public public/router.php
```

Abre:

```text
http://127.0.0.1:8090
```

> O argumento `public/router.php` é necessário para rotas amigáveis no servidor embutido.

### 3.5 Verificar a instalação

```bash
php -l public/index.php
php -l src/routes.php
php database/scan_lang.php
```

Depois abre a página inicial e confirma que o CSS, o JavaScript e os SVGs dos produtos carregam.

---

## 4. Contas de demonstração

| Perfil | Email | Palavra-passe | Notas |
|---|---|---|---|
| Administrador | `admin@trivex.pt` | `Admin123!` | Acesso total, aprovações, moderadores, caixa de correio |
| Moderador | `moderador@trivex.pt` | `Mod12345!` | Backoffice operacional; alterações exigem aprovação |
| Cliente | `cliente@trivex.pt` | `Cliente123!` | Conta verificada, encomendas e chat |
| Empresa | `empresa@trivex.pt` | `Empresa123!` | Empresa aprovada com acesso ao painel |
| Empresa pendente | `tiago@oceanonorte.pt` | `Empresa123!` | Não inicia sessão até aprovação do admin |

Fluxo de demonstração sugerido:

1. Entra como **admin**.
2. Aprova a *Oceano Norte Sports* em **Admin → Empresas**.
3. Entra como `tiago@oceanonorte.pt`.
4. Submete ou edita um produto no portal da empresa.

---

## 5. Utilizar a loja

### 5.1 Idioma e tema

- Usa **PT / EN** no cabeçalho para mudar o idioma.
- Usa o controlo sol/lua para alternar entre tema claro e escuro.
- A escolha fica guardada em sessão/local storage e persiste entre páginas.

### 5.2 Catálogo e pesquisa

Rotas:

| Página | Rota |
|---|---|
| Página inicial | `/` |
| Catálogo | `/loja` |
| Categoria | `/categoria/{slug}` |
| Pesquisa | `/pesquisa?q=...` |
| Promoções | `/promocoes` |
| Página CMS | `/pagina/{slug}` |
| API de pesquisa instantânea | `/api/pesquisa` |

Os filtros do catálogo incluem pesquisa por texto, categoria, intervalo de preço, marca, tamanho, disponibilidade e promoção. A ordenação e a paginação são feitas no servidor.

### 5.3 Página de produto

Rota: `/produto/{slug}`

A página de produto suporta:

- imagem principal e galeria de miniaturas;
- seleção de cor e tamanho;
- preço, SKU e stock em tempo real;
- limites de quantidade com base no stock;
- tabela de especificações;
- avaliações;
- produtos relacionados;
- galerias específicas por cor, quando configuradas pela empresa.

Quando uma cor é selecionada, o `product.js` reconstrói a imagem principal e as miniaturas a partir do array `images` da variante.

### 5.4 Carrinho e checkout

Rotas:

| Ação | Rota |
|---|---|
| Ver carrinho | `/carrinho` |
| Adicionar item | `POST /carrinho/adicionar` |
| Atualizar quantidade | `POST /carrinho/atualizar` |
| Remover item | `POST /carrinho/remover` |
| Limpar carrinho | `POST /carrinho/limpar` |
| Checkout | `/checkout` |
| Sucesso | `/checkout/sucesso/{number}` |

O checkout funciona para convidados e clientes autenticados. O servidor:

1. valida os dados submetidos;
2. volta a validar o stock;
3. cria a encomenda e os itens dentro de uma transação;
4. bloqueia a linha da variante com `FOR UPDATE`;
5. decrementa o stock;
6. simula o pagamento;
7. envia/regista o email de confirmação;
8. limpa o carrinho.

Os métodos de pagamento são simulados:

- **Cartão** → `paid`
- **MB WAY** → `paid`
- **Multibanco** → `pending`, com referência gerada

### 5.5 Conta de cliente

Rotas:

| Página | Rota |
|---|---|
| Dashboard | `/conta` |
| Encomendas | `/conta/encomendas` |
| Detalhe da encomenda | `/conta/encomendas/{id}` |
| Perfil | `/conta/perfil` |
| Moradas | `/conta/moradas` |
| Suporte | `/suporte` |

Os clientes podem gerir moradas, atualizar o perfil, consultar o histórico de encomendas e submeter uma avaliação por produto.

---

## 6. Portal da empresa

### 6.1 Acesso

A conta de empresa tem de estar:

1. registada;
2. aprovada por um administrador;
3. autenticada com a conta do proprietário da empresa.

Rotas:

| Página | Rota |
|---|---|
| Dashboard | `/empresa/painel` |
| Produtos | `/empresa/produtos` |
| Novo produto | `/empresa/produtos/novo` |
| Editar produto | `/empresa/produtos/{id}/editar` |

Se a empresa não estiver aprovada, o middleware redireciona para `/empresa/pendente`.

### 6.2 Dashboard

O dashboard mostra:

- total de produtos;
- produtos ativos e pendentes;
- stock total;
- unidades vendidas;
- receita;
- produtos recentes e encomendas relacionadas.

### 6.3 Lista de produtos

A lista de produtos mostra a imagem principal, o nome, a marca, o ID do produto, o stock, o preço, o estado e a data de criação.

Ações disponíveis:

- **Editar** — abre o formulário de edição.
- **Ver na loja** — visível quando o produto está ativo.

Estados:

| Estado | Significado |
|---|---|
| `active` | Publicado na loja |
| `pending` | À espera de revisão do admin |
| `rejected` | Rejeitado, com nota opcional |

### 6.4 Criar um produto

Abre **Os meus produtos → Submeter novo produto** ou `/empresa/produtos/novo`.

Informação obrigatória:

- nomes em português e inglês;
- categoria;
- preço base;
- marca;
- descrições;
- pelo menos uma variante;
- imagem principal.

Ao submeter, o produto é criado com o estado `pending`.

### 6.5 Editar um produto

Abre **Os meus produtos → Editar** ou `/empresa/produtos/{id}/editar`.

O formulário de edição permite atualizar:

- nomes, descrições, marca e categoria;
- preço base e preço promocional;
- especificações;
- variantes e stock;
- imagem principal;
- imagens de galeria;
- fotos por cor.

Comportamento:

- editar um produto `active` mantém o estado `active`;
- editar um produto `rejected` devolve-o a `pending` e limpa a nota de rejeição;
- as variantes existentes são atualizadas no próprio registo;
- as variantes removidas são desativadas (`active = 0`), em vez de apagadas, para preservar referências no histórico de encomendas.

### 6.6 Variantes

Cada linha de variante contém:

- tamanho;
- cor;
- stock;
- `variant_id[]` oculto para variantes existentes.

Usa **Adicionar variante** para acrescentar uma linha. Usa o controlo de remoção para desativar uma variante existente ou remover uma linha nova.

### 6.7 Especificações técnicas

As especificações são pares chave/valor:

- nome;
- valor.

Usa **Adicionar especificação técnica** para acrescentar uma linha. São guardadas como JSON em `products.specs`.

### 6.8 Imagem principal

A imagem principal é guardada em `products.image`.

- Clica na área de upload para escolher uma imagem.
- A imagem escolhida é pré-visualizada de imediato.
- O ícone de upload muda quando existe pré-visualização.
- As imagens existentes podem ser mantidas ou substituídas.

Validação:

- tipos de imagem permitidos;
- tamanho máximo;
- verificação real com `getimagesize()`;
- nome de ficheiro aleatório.

### 6.9 Galeria

As imagens adicionais de galeria são guardadas como array JSON em `products.gallery`.

- Seleciona uma ou mais imagens.
- As novas imagens são pré-visualizadas antes de guardar.
- As imagens existentes podem ser removidas individualmente.
- As novas imagens podem ser removidas antes de submeter.

A imagem principal e a galeria são combinadas na loja.

### 6.10 Fotos por cor

As fotos por cor são guardadas em `product_color_images`, associadas à string exata da cor usada nas variantes.

Fluxo:

1. Adiciona ou edita as cores das variantes.
2. O formulário cria automaticamente um cartão de fotos por cada cor única.
3. Carrega uma ou mais imagens por cor.
4. Guarda o produto.
5. Na loja, ao selecionar essa cor, a imagem principal e as miniaturas mudam para as fotos dessa cor.

Importante:

- A string da cor é a chave. `Azul Oceano` e `azul oceano` são tratadas como a mesma cor no formulário, mas mantém a grafia consistente.
- Se removeres todas as fotos de uma cor, a imagem principal/galeria por omissão volta a ser usada.

### 6.11 Regras de estado do produto

| Ação | Resultado |
|---|---|
| A empresa cria um produto | `pending` |
| O admin aprova | `active` |
| O admin rejeita | `rejected` + nota |
| A empresa edita um produto ativo | mantém `active` |
| A empresa edita um produto rejeitado | passa a `pending` |

---

## 7. Backoffice

### 7.1 Dashboard do admin

Rota: `/admin`

Mostra métricas de vendas, encomendas, utilizadores, alertas de stock e chats.

### 7.2 Utilizadores

Rota: `/admin/utilizadores`

- pesquisar utilizadores;
- consultar estatísticas de compra;
- alterar o estado do utilizador.

O admin aplica alterações de imediato. As alterações do moderador entram na fila de aprovação.

### 7.3 Stock

Rota: `/admin/stock`

- pesquisar variantes;
- filtrar stock baixo/esgotado;
- atualizar stock.

### 7.4 Encomendas

Rotas:

| Página | Rota |
|---|---|
| Lista | `/admin/encomendas` |
| Detalhe | `/admin/encomendas/{id}` |
| Atualizar estado | `POST /admin/encomendas/{id}/estado` |

Estados de encomenda:

`placed`, `shipped`, `received`, `lost`, `not_received`.

### 7.5 Empresas

Rota: `/admin/empresas`

- aprovar ou rejeitar empresas;
- ativar o proprietário quando aprovada;
- adicionar nota de revisão;
- notificar o proprietário da empresa.

### 7.6 Propostas

Rota: `/admin/propostas`

- rever produtos submetidos por empresas;
- aprovar → o produto passa a `active`;
- rejeitar → o produto passa a `rejected` com nota;
- a empresa é notificada.

### 7.7 Páginas

Rota: `/admin/paginas`

- ativar/desativar páginas CMS;
- a página inicial está protegida e não pode ser desativada.

### 7.8 Moderadores

Rota: `/admin/moderadores`

Apenas admin. Criar, listar, ativar ou suspender contas de moderador.

### 7.9 Aprovações

Rota: `/admin/aprovacoes`

Fila apenas para admin, com alterações submetidas por moderadores.

Ações:

- aprovar → aplica a alteração e marca como aprovada;
- rejeitar → marca como rejeitada e adiciona nota;
- o moderador que pediu é notificado.

### 7.10 Notificações

Rota: `/admin/notificacoes`

Feed de eventos do sistema para a equipa.

### 7.11 Caixa de correio

Rota: `/admin/mailbox`

Apenas admin. Mostra os emails registados em `email_log`.

### 7.12 Chats

Rotas:

| Página | Rota |
|---|---|
| Fila ativa | `/admin/chats` |
| Arquivo | `/admin/chats/arquivo` |
| Conversa | `/admin/chats/{id}` |

Ações:

- entrar numa conversa;
- enviar mensagens;
- manter heartbeat enquanto atribuído;
- sair;
- concluir.

---

## 8. Fluxo do moderador

Os moderadores acedem a áreas operacionais, mas não podem aplicar alterações sensíveis diretamente.

Quando um moderador altera:

- stock de variantes;
- estado de encomendas;
- estado de utilizadores;
- estado de empresas;
- estado de produtos;
- ativação de páginas CMS;

a alteração é escrita em `pending_modifications` e os admins são notificados.

O admin aprova ou rejeita em **Admin → Aprovações**.

Exceção: as ações do chat de suporte são aplicadas de imediato.

---

## 9. Chat de suporte

Rota do cliente: `/suporte`

Estados:

| Estado | Significado |
|---|---|
| `open` | À espera da equipa |
| `in_progress` | Atribuído a um moderador/admin |
| `concluded` | Resolvido |
| `archived` | Arquivado após 10 minutos |

Regras:

1. O cliente abre uma conversa → `open`.
2. A equipa entra → `in_progress`.
3. O heartbeat da equipa mantém a atribuição.
4. Ao sair, a conversa volta a `open`.
5. Sem heartbeat durante 2 minutos → sweep devolve a `open`.
6. Concluir → `concluded`.
7. Após 10 minutos → `archived`.

O cliente vê apenas as suas conversas. A equipa vê a fila global.

---

## 10. Configuração de email

O driver é escolhido com `MAIL_DRIVER`.

### 10.1 Driver log (por omissão)

```dotenv
MAIL_DRIVER=log
```

Os emails são:

- renderizados como HTML;
- guardados em `email_log`;
- escritos em `storage/mail/`;
- visíveis em **Admin → Caixa de correio**.

É o modo mais seguro para demonstrações offline.

### 10.2 Mailpit

```dotenv
MAIL_DRIVER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
```

Abre o Mailpit em `http://127.0.0.1:8025`.

### 10.3 Gmail SMTP

1. Ativa a verificação em dois passos na conta Google.
2. Cria uma **app password**.
3. Define:

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

Para a porta 465 usa:

```dotenv
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
```

Notas:

- Remove espaços da app password antes de colar.
- `MAIL_FROM` tem de ser a conta autenticada ou um alias verificado.
- Reinicia o PHP depois de alterar o `.env`.
- Confirma em **Admin → Caixa de correio** se o estado é `sent` ou `failed`.

### 10.4 Resolução de problemas de email

| Sintoma | Verificação |
|---|---|
| `SMTP username and password are not configured` | Preenche `MAIL_USERNAME` e `MAIL_PASSWORD` |
| Ligação recusada | Verifica host, porta e firewall |
| Erro TLS/certificado | Verifica a configuração OpenSSL/CA do PHP |
| Erro de autenticação Gmail | Usa uma app password, não a palavra-passe normal |
| Mensagem enviada mas não aparece na inbox | Verifica spam, `MAIL_FROM` e regras de alias Gmail |
| `email_log.status = failed` | Lê `storage/mail.log` e o erro na caixa de correio |

---

## 11. Administração da base de dados

### 11.1 Tabelas

O schema tem **17 tabelas**:

`roles`, `companies`, `users`, `categories`, `products`, `product_variants`, `product_color_images`, `addresses`, `orders`, `order_items`, `reviews`, `pages`, `chats`, `chat_messages`, `pending_modifications`, `notifications`, `email_log`.

### 11.2 Backup

```bash
mysqldump -u root trivex > trivex-backup.sql
```

### 11.3 Restauro

```bash
mysql -u root trivex < trivex-backup.sql
```

### 11.4 Reset

```bash
php database/install.php
```

É destrutivo.

---

## 12. Manutenção

### 12.1 Uploads

As imagens carregadas dos produtos ficam em:

```text
public/assets/img/uploads/products/
```

Mantém esta pasta com permissões de escrita para o PHP. Não apagues ficheiros ainda referenciados por `products.image`, `products.gallery` ou `product_color_images.image`.

### 12.2 Logs

| Log | Localização |
|---|---|
| Erros de email | `storage/mail.log` |
| Emails registados | `storage/mail/` |
| Saída do servidor PHP | terminal que corre `php -S` |

### 12.3 Traduções

Ficheiros de tradução:

```text
lang/pt.php
lang/en.php
```

Executa o scanner:

```bash
php database/scan_lang.php
```

Algumas chaves dinâmicas podem aparecer como falsos positivos.

### 12.4 Assets estáticos

Todo o CSS e JavaScript é local:

```text
public/assets/css/
public/assets/js/
public/assets/img/
```

Não há etapa de build. Depois de alterar CSS/JS, faz uma atualização forçada no navegador (Ctrl+F5).

---

## 13. Resolução de problemas

| Problema | Causa provável | Solução |
|---|---|---|
| 404 em rotas amigáveis | Servidor embutido sem router | Usa `php -S 127.0.0.1:8090 -t public public/router.php` |
| Erro de ligação à base de dados | MySQL parado ou `.env` errado | Inicia o MySQL e confirma os valores `DB_*` |
| Alterações de CSS/JS não aparecem | Cache do navegador | Atualização forçada (`Ctrl+F5`) |
| Upload rejeitado | Tipo/tamanho inválido | Usa JPEG/PNG/WebP dentro do limite |
| Empresa redirecionada para página pendente | Empresa não aprovada | Aprova em **Admin → Empresas** |
| Alteração do moderador não aplicada | Fluxo de aprovação | Aprova em **Admin → Aprovações** |
| Emails não chegam ao Gmail | Driver ainda em `log` ou app password ausente | Configura SMTP e reinicia o PHP |
| Fotos por cor não mudam | Strings de cor diferentes | Usa a mesma grafia nas variantes e nas fotos |
| Sessão perdida após reinício | Armazenamento de sessão do PHP reiniciado | Volta a iniciar sessão |

---

## 14. Checklist de segurança

- Usa palavras-passe fortes e contas únicas.
- Mantém o `.env` fora do controlo de versões.
- Usa HTTPS em produção.
- Mantém `APP_DEBUG=false` em produção.
- Restringe permissões da base de dados.
- Faz backups regulares da base de dados e dos uploads.
- Revê periodicamente as contas de admin/moderador.
- Não expões `storage/` nem dumps da base de dados.
- Valida uploads e mantém PHP/MySQL atualizados.
- Roda as app passwords do Gmail se forem comprometidas.

---

## 15. Checklist de testes

### Sintaxe

```bash
php -l public/index.php
php -l src/routes.php
node --check public/assets/js/company.js
node --check public/assets/js/product.js
```

### Smoke test funcional

1. Abre `/` e alterna PT/EN e claro/escuro.
2. Pesquisa um produto e aplica filtros.
3. Abre um produto, muda cor e tamanho, adiciona ao carrinho.
4. Conclui o checkout como convidado.
5. Confirma a encomenda e o email em **Admin → Caixa de correio**.
6. Entra como cliente e consulta `/conta/encomendas`.
7. Abre um chat de suporte como cliente.
8. Entra com uma conta de equipa e responde/conclui o chat.
9. Entra como moderador, altera stock e confirma que fica pendente.
10. Entra como admin e aprova a alteração.
11. Entra como empresa e cria um produto com variantes, especificações, imagem principal, galeria e fotos por cor.
12. Edita o produto e confirma que as variantes mantêm os IDs e o stock é atualizado no próprio registo.
13. Aprova o produto como admin e confirma a troca de cor na loja.

### Verificação responsiva

Verifica aproximadamente:

- 1440px
- 1024px
- 768px
- 390px

Confirma que não há overflow horizontal do documento, botões cortados ou tabelas ilegíveis.

---

## 16. Mapa de ficheiros

| Caminho | Função |
|---|---|
| `public/index.php` | Controlador frontal e cabeçalhos de segurança |
| `public/router.php` | Router para o servidor embutido do PHP |
| `src/bootstrap.php` | Ambiente, autoloader, config, sessão, locale |
| `src/routes.php` | Todas as rotas da aplicação |
| `src/Core/` | Router, middleware, auth, sessão, CSRF, base de dados, mailer, view |
| `src/Controllers/` | Orquestração dos pedidos |
| `src/Models/` | Acesso a dados com PDO |
| `src/Support/` | ModificationService, MailService, auxiliares, ícones |
| `src/Views/` | Templates PHP e layouts |
| `lang/` | Dicionários PT/EN |
| `public/assets/css/` | Estilos app, account, admin, company e auth |
| `public/assets/js/` | Scripts app, product, company, admin e chat |
| `database/database.sql` | Schema e dados de seed |
| `docs/diagrams.md` | Diagramas Mermaid de arquitetura e negócio |
| `docs/REPORT.md` | Relatório do projeto (EN) |
| `docs/RELATORIO-PT.md` | Relatório do projeto (PT) |
| `docs/MANUAL.md` | Manual de instruções (EN) |
| `docs/MANUAL-PT.md` | Este manual |

---

## 17. FAQ

**Posso correr o projeto sem Composer ou npm?**  
Sim. Não há etapa de build nem dependência de pacotes externos em runtime.

**Posso usar o Gmail para enviar emails reais?**  
Sim, com uma app password do Google e `MAIL_DRIVER=smtp`. Ver secção 10.3.

**Porque é que uma alteração do moderador fica “pendente”?**  
É o fluxo de aprovação. Um admin tem de aprovar em **Admin → Aprovações**.

**Porque é que uma empresa não consegue entrar?**  
A empresa tem de ser aprovada por um admin primeiro.

**Porque é que um produto desapareceu da loja?**  
Pode estar `pending` ou `rejected`. Verifica **Admin → Propostas** ou a lista de produtos da empresa.

**Onde ficam as imagens carregadas?**  
Em `public/assets/img/uploads/products/`.

**Como faço reset da demonstração?**  
Executa `php database/install.php`. Isto apaga a base de dados atual.

---

**TRIVEX** — swim · bike · run
