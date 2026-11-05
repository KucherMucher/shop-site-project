# TRIVEX — Diagramas

Documento de apoio à arquitetura e às regras de negócio. Os diagramas usam **Mermaid**, suportado nativamente pelo GitHub, GitLab, VS Code e pela maioria dos editores Markdown.

---

## 1. ERD — Modelo de dados

O schema usa 17 tabelas e chaves estrangeiras com integridade referencial. `orders.user_id` é opcional para permitir checkout de convidados. `order_items` guarda uma cópia do nome e do preço no momento da compra, para preservar o histórico mesmo que o produto mude. `product_color_images` guarda as fotografias específicas de cada cor, mantendo `products.image` como imagem principal e `products.gallery` como galeria JSON.

```mermaid
erDiagram
    ROLES ||--o{ USERS : "define"
    COMPANIES |o--o{ USERS : "emprega"
    COMPANIES |o--o{ PRODUCTS : "propõe"
    CATEGORIES ||--o{ PRODUCTS : "classifica"
    PRODUCTS ||--o{ PRODUCT_VARIANTS : "tem"
    PRODUCTS ||--o{ PRODUCT_COLOR_IMAGES : "tem fotos por cor"
    USERS ||--o{ ADDRESSES : "guarda"
    USERS |o--o{ ORDERS : "faz"
    ORDERS ||--o{ ORDER_ITEMS : "contém"
    PRODUCTS ||--o{ ORDER_ITEMS : "referencia"
    PRODUCT_VARIANTS ||--o{ ORDER_ITEMS : "variante"
    USERS ||--o{ REVIEWS : "escreve"
    PRODUCTS ||--o{ REVIEWS : "recebe"
    USERS ||--o{ CHATS : "abre"
    CHATS ||--o{ CHAT_MESSAGES : "contém"
    USERS |o--o{ CHAT_MESSAGES : "envia"
    USERS ||--o{ NOTIFICATIONS : "recebe"
    USERS ||--o{ PENDING_MODIFICATIONS : "solicita"

    ROLES {
        int id PK
        string slug
        string name
        string description
    }

    COMPANIES {
        int id PK
        string name
        string slug
        string vat
        string email
        string phone
        string city
        string postal_code
        string country
        enum status
        string review_note
        int approved_by FK
        datetime approved_at
    }

    USERS {
        int id PK
        int role_id FK
        int company_id FK
        string name
        string email
        string password_hash
        string phone
        string locale
        enum status
        datetime email_verified_at
        string verification_token
        datetime verification_expires_at
        datetime last_login_at
    }

    CATEGORIES {
        int id PK
        string slug
        string name_pt
        string name_en
        string description_pt
        string description_en
        string icon
        string image
        int position
        int active
    }

    PRODUCTS {
        int id PK
        int category_id FK
        int company_id FK
        string slug
        string name_pt
        string name_en
        string short_pt
        string short_en
        text description_pt
        text description_en
        string brand
        json specs
        decimal base_price
        decimal sale_price
        datetime promo_ends_at
        string image
        json gallery
        int featured
        int is_new
        enum status
        string rejection_note
        int submitted_by FK
        int approved_by FK
        datetime approved_at
    }

    PRODUCT_VARIANTS {
        int id PK
        int product_id FK
        string sku
        string size
        string color
        string color_hex
        int stock
        decimal price_delta
        int active
    }

    PRODUCT_COLOR_IMAGES {
        int id PK
        int product_id FK
        string color
        string image
        int sort_order
        datetime created_at
    }

    ADDRESSES {
        int id PK
        int user_id FK
        string label
        string name
        string phone
        string address
        string city
        string postal_code
        string country
        int is_default_shipping
        int is_default_billing
    }

    ORDERS {
        int id PK
        int user_id FK
        string order_number
        string email
        enum status
        enum payment_method
        enum payment_status
        string payment_reference
        decimal subtotal
        decimal shipping
        decimal discount
        decimal total
        string shipping_name
        string shipping_phone
        string shipping_address
        string shipping_city
        string shipping_postal_code
        string shipping_country
        string billing_name
        string billing_address
        string billing_city
        string billing_postal_code
        string billing_country
        string tracking_code
        string notes
    }

    ORDER_ITEMS {
        int id PK
        int order_id FK
        int product_id FK
        int variant_id FK
        string product_name
        string variant_label
        decimal unit_price
        int quantity
        decimal line_total
    }

    REVIEWS {
        int id PK
        int product_id FK
        int user_id FK
        int rating
        string title
        text body
        datetime created_at
    }

    PAGES {
        int id PK
        string slug
        string title_pt
        string title_en
        text content_pt
        text content_en
        int is_system
        int active
        int position
        datetime created_at
        datetime updated_at
    }

    CHATS {
        int id PK
        int user_id FK
        int moderator_id FK
        string subject
        enum status
        datetime last_message_at
        datetime last_moderator_activity
        datetime concluded_at
    }

    CHAT_MESSAGES {
        int id PK
        int chat_id FK
        int sender_id FK
        enum sender_type
        text body
        datetime created_at
    }

    PENDING_MODIFICATIONS {
        int id PK
        string entity_type
        int entity_id
        string action
        json payload
        string summary
        int requested_by FK
        enum status
        int reviewed_by FK
        string review_note
        datetime reviewed_at
    }

    NOTIFICATIONS {
        int id PK
        int user_id FK
        string type
        string title
        string message
        string link
        datetime read_at
        datetime created_at
    }

    EMAIL_LOG {
        int id PK
        string to_email
        string subject
        text body
        string driver
        string status
        datetime created_at
    }
```

### Notas do modelo

- `COMPANIES.approved_by` aponta para `USERS`; o respetivo utilizador é normalmente um administrador.
- `PRODUCTS.submitted_by` e `PRODUCTS.approved_by` também referenciam `USERS`; são FKs históricas e não alteram o proprietário da empresa.
- `PENDING_MODIFICATIONS.requested_by` e `reviewed_by` referenciam `USERS`; a tabela funciona como fila de aprovação para alterações de moderadores.
- `CHAT_MESSAGES.sender_type` distingue `user`, `moderator` e `system`; mensagens de sistema têm `sender_id` nulo.
- `ORDERS` pode ter `user_id` nulo (convidado). O acesso à confirmação é validado por sessão ou pelo proprietário autenticado.
- `ORDER_ITEMS.product_name` e `unit_price` são snapshots históricos. Se um produto for removido, a encomenda mantém a informação.
- `PRODUCT_COLOR_IMAGES.color` usa o mesmo texto exato da cor em `PRODUCT_VARIANTS.color`; a loja agrupa as imagens por essa chave.
- `PRODUCTS.image` é a imagem principal; `PRODUCTS.gallery` é um array JSON de imagens adicionais; `PRODUCT_COLOR_IMAGES` contém as fotos específicas de cada cor.
- `EMAIL_LOG` é a fonte da mailbox do backoffice quando o driver é `log`.

### 1.1 Fluxo de imagens do produto

```mermaid
flowchart TD
    A["Empresa abre o formulário do produto"] --> B{"Novo ou edição?"}
    B -->|Novo| C["Carrega imagem principal"]
    B -->|Edição| D["Mantém ou substitui imagem principal"]
    C --> E["Carrega imagens de galeria"]
    D --> E
    E --> F["Define variantes e cores"]
    F --> G["Cartões de fotos por cor"]
    G --> H["products.image"]
    E --> I["products.gallery (JSON)"]
    G --> J["product_color_images"]
    H --> K["Página de produto na loja"]
    I --> K
    J --> K
    K --> L{"Cor selecionada?"}
    L -->|Sim| M["product.js reconstrói imagem principal e thumbnails"]
    L -->|Não| N["Mostra imagem principal e galeria por omissão"]
```

---

## 2. Matriz de permissões

### 2.1 Visão visual

```mermaid
flowchart LR
    subgraph PERFIS["Perfis"]
        G["Visitante"]
        U["Cliente"]
        C["Empresa"]
        M["Moderador"]
        A["Administrador"]
    end

    subgraph LOJA["Loja"]
        B["Catálogo, pesquisa e CMS"]
        K["Carrinho e checkout"]
        H["Conta, moradas e histórico"]
        R["Avaliações"]
        S["Chat de suporte"]
    end

    subgraph EMPRESA["Empresa"]
        EP["Painel de empresa"]
        PP["Propostas de produtos"]
    end

    subgraph BACKOFFICE["Backoffice"]
        BO["Operação: stock, encomendas, empresas, propostas, páginas"]
        MOD["Fila de aprovação de alterações"]
        ADM["Moderadores, mailbox e aprovações"]
        CHAT["Gestão de chats"]
    end

    G --> B
    G --> K
    G --> S

    U --> B
    U --> K
    U --> H
    U --> R
    U --> S

    C --> B
    C --> K
    C --> H
    C --> R
    C --> S
    C --> EP
    C --> PP

    M --> BO
    M --> CHAT
    M -.->|alterações ficam pendentes| MOD

    A --> BO
    A --> CHAT
    A --> MOD
    A --> ADM
```

### 2.2 Tabela detalhada

| Capacidade / área | Visitante | Cliente | Empresa | Moderador | Administrador |
|---|---:|---:|---:|---:|---:|
| Ver homepage, catálogo, categorias, pesquisa, promoções e páginas CMS | Sim | Sim | Sim | Sim | Sim |
| Pesquisa instantânea e sugestões JSON | Sim | Sim | Sim | Sim | Sim |
| Carrinho e checkout | Sim (convidado) | Sim | Sim | Sim | Sim |
| Moradas guardadas, perfil e histórico | Não | Sim | Sim | Sim | Sim |
| Criar/editar avaliação | Não | Sim | Sim | Sim | Sim |
| Abrir chat de suporte | Não | Sim | Sim | Sim | Sim |
| Responder no chat de cliente | Não | Sim | Sim | Sim | Sim |
| Backoffice de chats (entrar, sair, concluir) | Não | Não | Não | Sim | Sim |
| Painel de empresa | Não | Não | Sim, após aprovação | Não | Não |
| Submeter produto/proposta | Não | Não | Sim, após aprovação | Não | Não |
| Ver/editar stock, encomendas, utilizadores, empresas, propostas e páginas | Não | Não | Não | Sim, com aprovação | Sim, imediato |
| Alterar o próprio estado ou o de outro admin | Não | Não | Não | Não | Não aplicável |
| CRUD de moderadores | Não | Não | Não | Não | Sim |
| Fila de aprovações | Não | Não | Não | Não | Sim |
| Mailbox | Não | Não | Não | Não | Sim |
| Notificações de backoffice | Não | Não | Não | Sim | Sim |

### 2.3 Regras de aprovação

| Alteração | Admin | Moderador |
|---|---|---|
| Stock de variante | Aplica de imediato | Fica pendente |
| Estado de encomenda | Aplica de imediato | Fica pendente |
| Estado de utilizador | Aplica de imediato | Fica pendente |
| Estado de empresa | Aplica de imediato e ativa/reverte o proprietário | Fica pendente |
| Estado de produto/proposta | Aplica de imediato e notifica a empresa | Fica pendente |
| Ativação de página CMS | Aplica de imediato; homepage bloqueada | Fica pendente; homepage bloqueada |
| Mensagens/chat | Sem aprovação | Sem aprovação |
| Gestão de moderadores | Exclusivo do admin | Sem acesso |
| Mailbox | Exclusivo do admin | Sem acesso |

### 2.4 Middlewares

| Middleware | Efeito |
|---|---|
| `auth` | Exige sessão ativa; guarda `intended_url` e redireciona para `/login` |
| `guest` | Impede acesso a login/registo quando já existe sessão |
| `role:admin,moderator` | Exige um dos perfis indicados; caso contrário, 403 |
| `company` | Exige perfil empresa e empresa com estado `approved` |
| `throttle:key,limite,janela` | Limita tentativas por sessão (login, registo, checkout, newsletter) |

---

## 3. Fluxo de estados do chat

Estados em base de dados: `open`, `in_progress`, `concluded`, `archived`.
Rótulos na interface: **Aberto**, **Em progresso**, **Concluído**, **Arquivado**.

```mermaid
stateDiagram-v2
    state "Aberto" as Open
    state "Em progresso" as InProgress
    state "Concluído" as Concluded
    state "Arquivado" as Archived

    [*] --> Open : Cliente abre conversa
    Open --> InProgress : Moderador/admin entra
    InProgress --> Open : Moderador sai
    InProgress --> Open : Sem heartbeat durante 2 min
    InProgress --> Concluded : Moderador conclui
    Concluded --> Archived : 10 min após conclusão
    Archived --> [*]

    note right of InProgress
        Só o moderador atribuído
        pode responder e concluir.
        O heartbeat renova a atribuição.
    end note

    note right of Open
        A fila mostra conversas
        abertas e não atribuídas.
    end note
```

### Regras operacionais

1. `Chat::start()` cria a conversa em `open`, guarda a primeira mensagem e notifica a equipa.
2. `join()` faz `UPDATE ... WHERE status = 'open'` com verificação de concorrência; só um moderador consegue atribuir a conversa.
3. Enquanto está `in_progress`, o moderador envia heartbeat; o cliente pode continuar a responder.
4. `leave()` devolve a conversa a `open`, limpa `moderator_id` e regista uma mensagem de sistema.
5. `sweep()` devolve a `open` conversas sem heartbeat durante **2 minutos**.
6. `conclude()` passa a `concluded`, grava `concluded_at` e adiciona mensagem de sistema.
7. `sweep()` arquiva conversas concluídas há **10 minutos**.
8. O arquivo continua consultável em **Admin → Chats → Arquivo**.
9. O cliente só vê as suas conversas; a equipa vê a fila global.

---

## 4. Fluxo de compra e histórico

```mermaid
flowchart TD
    A["Catálogo / detalhe do produto"] --> B["Adicionar variante ao carrinho"]
    B --> C["Carrinho"]
    C --> D["Checkout"]
    D --> E{"Cliente autenticado?"}

    E -->|Não| F["Checkout convidado"]
    E -->|Sim| G["Selecionar/guardar morada"]

    F --> H["Validar email, morada e pagamento"]
    G --> H
    H --> I["Validar stock atual"]
    I --> J{"Stock suficiente?"}

    J -->|Não| J1["Erro de stock e regresso ao checkout"]
    J -->|Sim| K["Transação: criar encomenda + linhas"]

    K --> L["Reduzir stock com FOR UPDATE"]
    L --> M["Gerar referência de pagamento simulada"]
    M --> N["Enviar e-mail de confirmação"]
    N --> O["Limpar carrinho e mostrar sucesso"]

    O --> P{"Compra autenticada?"}
    P -->|Sim| Q["Histórico em /conta/encomendas"]
    P -->|Não| R["Confirmação apenas na sessão"]

    Q --> S["Admin/moderador atualiza estado"]
    S --> T["Notificação para o cliente"]
    T --> U["Cliente vê estado e tracking"]
    U --> V["Cliente pode avaliar o produto"]

    subgraph ESTADOS["Estados da encomenda"]
        S1["placed"]
        S2["shipped"]
        S3["received"]
        S4["lost"]
        S5["not_received"]
    end

```

### Regras de compra

- O checkout está disponível para convidados e autenticados; o `user_id` fica nulo em compras de convidado.
- A criação da encomenda e a redução de stock ocorrem na mesma transação.
- O stock é validado novamente no servidor com `FOR UPDATE`, mesmo que o carrinho já tenha sido validado antes.
- Cartão e MB WAY são marcados como `paid`; Multibanco fica `pending` e recebe uma referência.
- O e-mail de confirmação é registado ou enviado; uma falha de e-mail não deve impedir a encomenda de existir.
- A página de sucesso é acessível por sessão (`last_order_number`) ou pelo proprietário autenticado.
- O histórico de cliente usa `/conta/encomendas` e `/conta/encomendas/{id}`.
- A equipa altera o estado em `/admin/encomendas/{id}/estado`; alterações de moderador passam pela fila de aprovação.
- Cada alteração de estado gera uma notificação para o cliente, quando existe conta associada.

---

## 5. Fluxo de aprovação de alterações

```mermaid
flowchart LR
    M["Moderador"] --> S["ModificationService::submit()"]
    A["Administrador"] --> S

    S --> R{"Perfil do autor"}

    R -->|admin| AP["Aplicar alteração imediatamente"]
    R -->|moderator| P["Criar pending_modification"]
    P --> N["Notificar administradores"]
    N --> Q["Fila em /admin/aprovacoes"]

    Q --> D{"Decisão do admin"}
    D -->|Aprovar| AP2["Aplicar alteração + marcar approved"]
    D -->|Rejeitar| RJ["Marcar rejected + nota"]

    AP2 --> N2["Notificar moderador"]
    RJ --> N3["Notificar moderador"]

    AP --> DONE["Alteração visível no sistema"]
    N2 --> DONE
    N3 --> DONE
```

**Exceção:** o chat não passa por `ModificationService`. As ações de entrar, responder, sair e concluir são executadas diretamente pela equipa.

---

## 6. Arquitetura de runtime

```mermaid
flowchart TB
    Browser["Browser / cliente HTTP"] --> Public["public/index.php"]
    Public --> Bootstrap["src/bootstrap.php"]
    Bootstrap --> Session["Session + Lang + Config"]
    Bootstrap --> Router["Router + Middleware"]
    Router --> Controller["Controllers"]
    Controller --> Model["Models / PDO"]
    Controller --> Service["Services"]
    Service --> Model
    Model --> MySQL[("MySQL trivex")]
    Service --> Mailer["Mailer"]
    Mailer --> MailLog[("email_log + storage/mail")]
    Mailer --> SMTP["SMTP / Mailpit / mail()"]
    Controller --> View["View + Layouts + Partials"]
    View --> Response["HTML / JSON"]
    Response --> Browser
```

### Camadas

| Camada | Responsabilidade |
|---|---|
| `public/index.php` | Controlador frontal, cabeçalhos de segurança, tratamento global de erros |
| `src/bootstrap.php` | Autoloader, `.env`, configuração, sessão, locale, timezone |
| `src/Core/Router.php` | Correspondência de rotas, parâmetros `{id}`, middleware |
| `src/Core/Middleware.php` | Autenticação, perfis, empresa aprovada e throttling |
| `src/Controllers` | Orquestração de pedidos, validação e respostas |
| `src/Models` | Acesso a dados com PDO e prepared statements |
| `src/Support` | Serviços transversais (`ModificationService`, `MailService`, auxiliares) |
| `src/Views` | Templates PHP, layouts e partials |
| `lang` | Dicionários PT/EN |
| `public/assets` | CSS, JavaScript e SVGs locais |

---

## 7. Legenda de estados

| Entidade | Estados |
|---|---|
| Encomenda | `placed`, `shipped`, `received`, `lost`, `not_received` |
| Pagamento | `pending`, `paid`, `refunded` |
| Empresa | `pending`, `approved`, `rejected` |
| Produto | `active`, `pending`, `rejected` |
| Utilizador | `pending`, `active`, `suspended` |
| Chat | `open`, `in_progress`, `concluded`, `archived` |
| Moderação | `pending`, `approved`, `rejected` |

---

## 8. Como visualizar

- **GitHub/GitLab**: o ficheiro é renderizado automaticamente na aba de código.
- **VS Code**: instala a extensão *Markdown Preview Mermaid Support* e abre o preview.
- **Mermaid Live Editor**: cola cada bloco em <https://mermaid.live> para exportar SVG/PNG.

> Os diagramas são documentação. A fonte de verdade continua a ser o código em `src/` e o schema em `database/database.sql`.
