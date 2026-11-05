# TRIVEX — Relatório do Projeto

**Projeto:** TRIVEX — Loja de Triatlo  
**Tipo de documento:** Relatório técnico, funcional e registo de desenvolvimento  
**Versão:** 1.1  
**Data:** Novembro de 2026  
**Última atualização:** 5 de novembro de 2026  
**Stack:** PHP 8.3, MySQL 8, JavaScript puro, CSS  
**Registo de desenvolvimento:** construção assistida por IA e orientada por prompts; ver Apêndice E.

---

## Resumo executivo

O TRIVEX é uma plataforma completa de demonstração de comércio eletrónico para triatlo, construída de raiz sem Composer, npm, frameworks de frontend, CDNs ou bibliotecas externas em runtime. Combina uma loja pública, contas de cliente, um portal para empresas parceiras, um backoffice administrativo e um chat de suporte numa única aplicação PHP MVC.

O TRIVEX foi desenvolvido num fluxo iterativo orientado por prompts, com modelos de codificação da OpenCode. O prompt inicial, os prompts seguintes, os modelos utilizados, as datas e o instantâneo de dados estão documentados nos Apêndices E e F.

O projeto assenta em quatro ideias:

1. **Uma loja realista** com catálogo, pesquisa, variantes de produto, stock, promoções, carrinho e checkout.
2. **Um modelo operacional multiperfil** para clientes, empresas, moderadores e administradores.
3. **Publicação controlada de produtos**, em que as empresas submetem e editam produtos e os administradores aprovam.
4. **Uma base de código sustentável e autónoma**, com assets locais, traduções nativas PT/EN e temas claro/escuro.

O ciclo de desenvolvimento mais recente concluiu o fluxo de produto da empresa: produtos editáveis, repetidores funcionais de variantes e especificações, pré-visualização da imagem principal, galerias com múltiplas imagens, fotos por cor e troca de galeria na loja. Também foi adicionado suporte a SMTP autenticado com STARTTLS/SSL e documentação da configuração de entrega via Gmail.

---

## Arquitetura em resumo

| Camada | Tecnologia | Responsabilidade |
|---|---|---|
| Apresentação | Vistas PHP, variáveis CSS, JavaScript puro | Loja, área de conta, portal da empresa, backoffice, PT/EN e temas claro/escuro |
| Aplicação | Router próprio, middleware, controladores | Encaminhamento, autenticação, verificação de perfis, validação e limitação de pedidos |
| Domínio | Modelos e serviços | Produtos, variantes, encomendas, stock, aprovações, notificações e email |
| Infraestrutura | MySQL 8 + PDO, serviço de email SMTP/log, sistema de ficheiros | Persistência, entrega de email, carregamento de ficheiros e logs |
| Ambiente de execução | Laragon/Apache ou servidor embutido do PHP | Desenvolvimento local e demonstração |

Fluxo do pedido: `Navegador → public/index.php → bootstrap → router → middleware → controlador → modelo/serviço → vista ou JSON`.

---

## 1. Introdução

### 1.1 Contexto

O projeto foi construído como um sistema full-stack de comércio eletrónico para uma loja de triatlo. O domínio cobre três disciplinas — natação, ciclismo e corrida — e inclui equipamento, vestuário e nutrição.

A aplicação é intencionalmente autónoma, para poder correr num ambiente Laragon local, ser demonstrada offline e ser inspecionada sem uma etapa de instalação de dependências.

### 1.2 Motivação

Muitos projetos académicos e demonstrações de comércio eletrónico ficam-se pelo catálogo e pelo carrinho. O TRIVEX estende o modelo com:

- um fluxo real de onboarding de empresas e aprovação de produtos;
- alterações de moderadores que exigem aprovação de administradores;
- um chat de suporte com atribuição e regras de arquivo;
- um fluxo completo de email com caixa de correio no backoffice;
- conteúdo bilingue e troca de tema;
- comportamento responsivo em desktop, tablet e mobile.

### 1.3 Âmbito deste relatório

Este relatório documenta a arquitetura, o modelo de dados, as mecânicas principais, o trabalho de implementação recente, os resultados de validação e as limitações atuais. Complementa:

- `README.md` — instalação e visão geral das funcionalidades;
- `docs/MANUAL-PT.md` — manual de instruções operacional;
- `docs/diagrams.md` — diagramas Mermaid;
- `docs/TRIVEX-Apresentacao-PT.pptx` — apresentação do projeto;
- o prompt inicial, o registo de prompts, os modelos de IA e a cronologia de desenvolvimento (Apêndices E e F).

### 1.4 Desenvolvimento assistido por IA e metodologia de prompts

O TRIVEX foi construído num fluxo iterativo assistido por IA. O autor escreveu requisitos e relatos de erros em linguagem natural; um agente de codificação OpenCode inspecionou o ambiente de trabalho, editou ficheiros, executou comandos e validou o resultado. Cada prompt foi seguido de implementação e verificação, e o projeto foi retomado várias vezes à medida que o contexto era compactado.

| Modelo | Fornecedor | Papel no projeto | Utilização observada |
|---|---|---|---|
| DeepSeek V4.1 Flash Free | OpenCode | Modelo principal de codificação | 1.395 mensagens do assistente; construção inicial, correções, fluxo de produto e documentação |
| GLM-5.3 Flash Free | OpenCode | Alternativa temporária | 12 mensagens do assistente; duas janelas curtas de troca de modelo em 22 out 2026 e 5 nov 2026 |

O registo de prompts é reproduzido no Apêndice E. Foi reconstruído a partir do registo de sessão da OpenCode em 5 de novembro de 2026. As horas são locais. Os prompts "Resume" são mantidos no registo por fazerem parte do histórico de desenvolvimento, embora não introduzam novos requisitos.

---

## 2. Objetivos

### 2.1 Objetivo geral

Construir uma plataforma de comércio eletrónico de triatlo coerente, segura e demonstrável, com fluxos realistas de loja, empresa e backoffice.

### 2.2 Objetivos específicos

1. Implementar uma loja completa com catálogo, pesquisa, filtros, variantes e promoções.
2. Suportar checkout de convidados e de clientes autenticados com controlo transacional de stock.
3. Disponibilizar gestão de conta, histórico de encomendas, moradas e avaliações.
4. Disponibilizar um portal de empresa para submissão e edição de produtos.
5. Implementar perfis de administrador e moderador com poderes de aprovação diferentes.
6. Adicionar um chat de suporte com fila, atribuição e estados de ciclo de vida.
7. Suportar conteúdo PT/EN e temas claro/escuro.
8. Manter o sistema autónomo e fácil de correr localmente.
9. Validar a implementação com verificações de sintaxe, funcionais, responsivas e de entrega.

---

## 3. Âmbito

### 3.1 Incluído

- Loja pública e páginas CMS.
- Catálogo, pesquisa e filtros.
- Variantes de produto, stock e imagens específicas por cor.
- Carrinho e checkout simulado.
- Contas de cliente, moradas, encomendas e avaliações.
- Registo de empresas, aprovação e gestão de produtos.
- Backoffice de admin e moderador.
- Fila de aprovação de alterações de moderadores.
- Chat de suporte.
- Registo de emails e envio por SMTP.
- Localização PT/EN.
- Temas claro/escuro.
- Layouts responsivos.

### 3.2 Excluído

- Integração com gateway de pagamento real.
- Integração com transportadoras reais.
- Faturação fiscal.
- Chat em tempo real com WebSockets (é usado polling).
- Aplicações móveis nativas.
- Envio de Gmail via OAuth.
- Conjunto de testes de navegador automatizado.

---

## 4. Requisitos

### 4.1 Requisitos funcionais

| ID | Requisito |
|---|---|
| RF-01 | Visitantes podem navegar no catálogo, categorias, pesquisa e páginas CMS |
| RF-02 | Visitantes podem adicionar variantes ao carrinho e finalizar como convidados |
| RF-03 | Clientes podem registar-se, verificar email, iniciar sessão e gerir a conta |
| RF-04 | Clientes podem ver o histórico de encomendas e submeter avaliações |
| RF-05 | Empresas podem submeter produtos com variantes, especificações e imagens |
| RF-06 | Empresas podem editar produtos existentes, incluindo media e fotos por cor |
| RF-07 | Admins podem aprovar/rejeitar empresas e produtos |
| RF-08 | Moderadores podem propor alterações operacionais que exigem aprovação de admin |
| RF-09 | A equipa pode gerir chats de suporte |
| RF-10 | O sistema regista ou envia emails transacionais |
| RF-11 | A interface suporta PT/EN e temas claro/escuro |
| RF-12 | A interface mantém-se utilizável em mobile e tablet |

### 4.2 Requisitos não funcionais

| ID | Requisito |
|---|---|
| RNF-01 | Compatibilidade com PHP 8.1+ |
| RNF-02 | Compatibilidade com MySQL 8 / MariaDB 10.6+ |
| RNF-03 | Sem dependência de Composer/npm em runtime |
| RNF-04 | Layout responsivo |
| RNF-05 | Segurança: CSRF, prepared statements, hashing de palavras-passe, throttling |
| RNF-06 | Estrutura MVC sustentável |
| RNF-07 | Apenas assets locais |
| RNF-08 | Ficheiros de tradução PT/EN claros |
| RNF-09 | Tratamento seguro de imagens carregadas |
| RNF-10 | Comportamento previsível de encomendas e stock em concorrência |

---

## 5. Arquitetura

### 5.1 Arquitetura de alto nível

O TRIVEX usa uma arquitetura MVC própria:

```text
Navegador
   │
   ▼
public/index.php
   │
   ▼
src/bootstrap.php
   ├── .env / config
   ├── autoloader
   ├── sessão
   └── locale
   │
   ▼
Router + Middleware
   │
   ▼
Controllers
   ├── Models (PDO)
   ├── Services (ModificationService, MailService)
   └── Views / Layouts / Partials
   │
   ▼
MySQL + storage/mail + SMTP
```

### 5.2 Ciclo de vida do pedido

1. O servidor web encaminha o pedido para `public/index.php`.
2. O `src/bootstrap.php` carrega a configuração, inicia a sessão e define o locale.
3. O `src/routes.php` registra as rotas.
4. O router compara o URI e o método.
5. O middleware verifica autenticação, perfil, aprovação da empresa ou throttling.
6. O controller valida os dados e chama models/services.
7. O controller renderiza uma view dentro de um layout.
8. A resposta é devolvida como HTML ou JSON.

### 5.3 Camadas

| Camada | Responsabilidade |
|---|---|
| `public/` | Controlador frontal, router e assets públicos |
| `src/Core/` | Router, middleware, auth, sessão, CSRF, base de dados, mailer, view |
| `src/Controllers/` | Tratamento e validação de pedidos |
| `src/Models/` | Queries PDO e acesso a entidades |
| `src/Support/` | Serviços transversais e auxiliares |
| `src/Views/` | Templates, layouts e partials |
| `lang/` | Dicionários PT/EN |
| `database/` | Schema, seeders e scripts de instalação |
| `docs/` | Manual, relatório, diagramas e apresentação |

### 5.4 Decisões de design

- **Sem framework:** mantém o projeto transparente e sem dependências.
- **Prepared statements PDO:** acesso consistente à base de dados e proteção contra SQL injection.
- **Views renderizadas no servidor:** implementação simples e sem build de frontend.
- **JavaScript progressivo:** os formulários funcionam sem estado complexo; o JS melhora pré-visualizações, repetidores, galerias e polling do chat.
- **Fila de aprovação na base de dados:** alterações de moderadores são auditáveis e reversíveis.
- **Itens de encomenda com snapshot:** nome, variante e preço unitário são guardados no momento da compra.
- **Desativar variantes em vez de apagar:** preserva referências no histórico de encomendas.
- **Tabela de imagens por cor:** permite várias fotos por cor sem alterar o schema principal do produto.

---

## 6. Modelo de dados

### 6.1 Visão geral

O schema contém **17 tabelas**:

| Grupo | Tabelas |
|---|---|
| Identidade e acesso | `roles`, `companies`, `users` |
| Catálogo | `categories`, `products`, `product_variants`, `product_color_images` |
| Comércio | `addresses`, `orders`, `order_items` |
| Envolvimento | `reviews`, `chats`, `chat_messages`, `notifications` |
| Conteúdo e operações | `pages`, `pending_modifications`, `email_log` |

### 6.2 Relações principais

- Um perfil tem muitos utilizadores.
- Uma empresa tem muitos utilizadores e produtos.
- Uma categoria tem muitos produtos.
- Um produto tem muitas variantes.
- Um produto tem muitas imagens por cor.
- Um utilizador tem muitas moradas, encomendas, avaliações, chats e notificações.
- Uma encomenda tem muitos itens.
- Um chat tem muitas mensagens.
- Uma modificação pendente referencia os utilizadores que pedem e revêem.

### 6.3 Modelo de media do produto

O media do produto usa três locais de armazenamento:

| Local | Função |
|---|---|
| `products.image` | Imagem principal |
| `products.gallery` | Array JSON de imagens adicionais |
| `product_color_images` | Imagens agrupadas pela cor exata |

Colunas de `product_color_images`:

| Coluna | Tipo | Função |
|---|---|---|
| `id` | INT UNSIGNED | Chave primária |
| `product_id` | INT UNSIGNED | FK do produto, delete em cascata |
| `color` | VARCHAR(50) | Chave exata da cor da variante |
| `image` | VARCHAR(255) | Caminho da imagem |
| `sort_order` | INT | Ordem de apresentação |
| `created_at` | DATETIME | Data de criação |

Este design mantém a linha principal do produto estável e permite um número arbitrário de fotos por cor.

### 6.4 Snapshots de encomenda

`order_items` guarda:

- `product_name`;
- `variant_label`;
- `unit_price`;
- `quantity`;
- `line_total`.

Estes valores são snapshots históricos. Se o produto mudar mais tarde, a encomenda mantém-se correta.

### 6.5 Restrições importantes

- `orders.user_id` é anulável para checkout de convidados.
- `product_variants.sku` é único.
- `products.slug` é único.
- `product_color_images` faz delete em cascata com o produto.
- `email_log` registra o driver e o estado.

---

## 7. Mecânicas principais

### 7.1 Autenticação e perfis

Perfis:

- `admin`
- `moderator`
- `company`
- `user`

A autenticação usa:

- `password_hash()` / `password_verify()`;
- regeneração de sessão no login;
- tokens de verificação de email;
- cookies `HttpOnly` e `SameSite=Lax`.

Middleware:

| Middleware | Efeito |
|---|---|
| `auth` | Exige sessão ativa |
| `guest` | Bloqueia utilizadores autenticados em login/registo |
| `role:...` | Exige um dos perfis listados |
| `company` | Exige empresa aprovada |
| `throttle:key,limit,window` | Limita tentativas repetidas |

### 7.2 Loja e descoberta

A loja usa filtragem e paginação no servidor. As queries de produto incluem agregação de stock e lógica de promoção.

Funcionalidades principais:

- navegação por categorias;
- pesquisa por texto;
- filtros de preço, marca, tamanho, disponibilidade e promoção;
- endpoint JSON de pesquisa instantânea;
- produtos relacionados;
- avaliações e rating;
- páginas CMS.

### 7.3 Carrinho e checkout

O estado do carrinho é guardado em sessão. O checkout cria a encomenda dentro de uma transação.

Sequência crítica em `Order::create()`:

1. Gerar o número da encomenda.
2. Inserir a encomenda.
3. Para cada item, selecionar a variante com `FOR UPDATE`.
4. Validar o stock.
5. Decrementar o stock.
6. Inserir o snapshot do item.
7. Commit.

Se algum passo falhar, a transação faz rollback. Isto evita overselling e encomendas parciais.

Pagamento simulado:

| Método | Estado |
|---|---|
| Cartão | `paid` |
| MB WAY | `paid` |
| Multibanco | `pending` |

O email de confirmação é enviado depois do commit. Uma falha de email não remove a encomenda.

### 7.4 Fluxo de produto da empresa

O portal da empresa suporta o ciclo de vida completo do produto:

1. A empresa inicia sessão.
2. A empresa cria um produto.
3. O produto começa em `pending`.
4. O admin aprova ou rejeita.
5. A empresa pode editar o produto.
6. Produtos ativos mantêm-se ativos após edição.
7. Produtos rejeitados voltam a `pending` após edição.

Funcionalidades do formulário:

- nomes e descrições PT/EN;
- categoria e marca;
- preço base e preço promocional;
- especificações técnicas;
- variantes (tamanho, cor, stock);
- imagem principal;
- imagens de galeria;
- fotos por cor;
- remoção de imagens existentes.

Sincronização de variantes:

- variantes existentes são atualizadas no próprio registo através de `variant_id[]` oculto;
- novas variantes são inseridas;
- variantes que já não existem são desativadas (`active = 0`);
- o histórico de encomendas mantém as referências das variantes.

### 7.5 Media e troca de cor

Imagem principal:

- guardada em `products.image`;
- pré-visualizada de imediato após seleção;
- validada e guardada com nome aleatório.

Galeria:

- guardada como array JSON em `products.gallery`;
- é possível selecionar vários ficheiros;
- novos ficheiros são pré-visualizados;
- ficheiros existentes podem ser removidos.

Fotos por cor:

- guardadas em `product_color_images`;
- agrupadas pela string exata da cor;
- carregadas em cartões gerados a partir das cores das variantes;
- expostas à loja como `colorImages`.

Fluxo na loja:

1. `ShopController::product()` carrega `Product::colorImages()`.
2. A view do produto adiciona um array `images` a cada variante.
3. `product.js` escuta mudanças de cor.
4. `paintGallery()` reconstrói a imagem principal e as miniaturas a partir das imagens da variante selecionada.
5. A galeria por omissão mantém-se como alternativa.

### 7.6 Moderação e aprovação

Alterações de admin são aplicadas de imediato. Alterações de moderador são colocadas em fila.

`ModificationService::submit()`:

- se o ator é admin → `apply()`;
- caso contrário → cria uma linha em `pending_modifications` e notifica os admins.

Tipos de entidade suportados:

- `variant_stock`
- `order_status`
- `page_active`
- `user_status`
- `company_status`
- `product_status`

Aprovação:

- admin aprova → aplica + marca como aprovada + notifica o moderador;
- admin rejeita → marca como rejeitada + notifica o moderador.

O chat de suporte está excluído deste fluxo.

### 7.7 Chat de suporte

Estados:

```text
open → in_progress → concluded → archived
```

Regras:

- apenas um moderador pode entrar num chat aberto;
- o heartbeat mantém a atribuição;
- sem heartbeat durante 2 minutos → volta a `open`;
- a conclusão regista `concluded_at`;
- 10 minutos após a conclusão → `archived`;
- o arquivo continua disponível no backoffice.

É usado polling em vez de WebSockets para manter a stack sem dependências.

### 7.8 Fluxo de email

`Mailer::send()` suporta três drivers:

| Driver | Comportamento |
|---|---|
| `log` | Escreve HTML em `storage/mail/` e em `email_log` |
| `smtp` | Envia por SMTP autenticado |
| `mail` | Usa `mail()` do PHP |

Implementação SMTP:

- AUTH LOGIN;
- STARTTLS na porta 587;
- SSL implícito na porta 465;
- validação de códigos de resposta;
- corpos HTML em base64;
- dot-stuffing;
- verificação de certificado TLS.

O resultado é registado em `email_log` com estado `sent`, `failed` ou `logged`.

### 7.9 Internacionalização e temas

- `lang/pt.php` e `lang/en.php` contêm as chaves de tradução.
- O conteúdo de produto usa colunas `_pt` e `_en`.
- O locale é guardado em sessão/cookie.
- O tema é guardado em `localStorage` e aplicado antes da pintura.
- As cores claro/escuro são controladas por variáveis CSS.
- A tipografia é escalada centralmente com `--text-scale`.

### 7.10 Segurança

Controlos implementados:

- prepared statements PDO;
- tokens CSRF em formulários POST;
- hashing de palavras-passe;
- regeneração de sessão;
- middleware de perfil e empresa;
- throttling em login, registo, checkout, newsletter e reenvio;
- escape de saída;
- validação de upload por tamanho, extensão e `getimagesize()`;
- nomes de ficheiro aleatórios;
- recálculo de preço e stock no servidor;
- headers de segurança;
- página inicial protegida;
- confirmação de encomenda restrita por sessão ou proprietário.

---

## 8. Destaques de implementação

### 8.1 Edição de produtos da empresa

Adicionado:

- rota `/empresa/produtos/{id}/editar`;
- rota de atualização `POST /empresa/produtos/{id}`;
- lógica partilhada `persistProduct()`;
- sincronização de variantes;
- uploads de galeria;
- uploads de imagens por cor;
- botões de edição na lista de produtos.

### 8.2 Formulário de produto

O formulário foi reescrito para suportar criação e edição:

- pré-visualização da imagem principal;
- pré-visualizações da galeria;
- remoção de galeria existente;
- cartões de fotos por cor;
- remoção de imagens por cor existentes;
- IDs de variante ocultos;
- erros de validação e valores anteriores do formulário.

### 8.3 JavaScript

`public/assets/js/company.js` trata de:

- repetidores de variantes/especificações;
- pré-visualização da imagem principal;
- pré-visualizações e remoção da galeria;
- cartões de cor gerados a partir das cores das variantes;
- pré-visualizações e remoção de ficheiros por cor.

`public/assets/js/product.js` trata de:

- preço, stock e SKU da variante;
- disponibilidade de tamanho/cor;
- limites de quantidade;
- reconstrução da galeria ao mudar de cor;
- seleção de miniaturas e zoom.

### 8.4 Correções responsivas

- A navegação da empresa passa a quebrar linha em mobile em vez de cortar ações.
- Os filtros de produto quebram linha em mobile.
- As tabelas da empresa têm estilos base e scroll horizontal em tablet.
- O formulário da empresa passa a uma coluna abaixo de 900px.
- Os cartões de cor passam a uma coluna abaixo de 640px.
- As linhas de tabela em mobile usam etiquetas em estilo cartão.

### 8.5 SMTP

O `src/Core/Mailer.php` suporta agora SMTP autenticado. A configuração foi adicionada para:

- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_ENCRYPTION`

O README documenta a configuração de uma palavra-passe de aplicação do Gmail.

### 8.6 Cronologia de desenvolvimento e fluxo orientado por prompts

| Data | Prompts | Fase | Resultado principal |
|---|---|---|---|
| 22 out 2026 | 1–6 | Construção inicial | Plataforma MVC completa, schema, dados iniciais, loja, contas, portal da empresa, backoffice, chat e diagramas |
| 29 out 2026 | 7–11 | Correções e tipografia | Nove correções de UI/PHP, escala global de texto e novas verificações responsivas |
| 30 out – 5 nov 2026 | 12–18 | Fluxo de produto e email | Edição de produtos da empresa, repetidores de variantes/especificações, imagem principal, galeria, fotos por cor e suporte SMTP |
| 5 nov 2026 | 19–20 | Documentação | Manual, relatório, diagramas, PowerPoint e versões em português |
| 5 nov 2026 | 21–22 | Atualização do relatório | Registo de prompts, modelos de IA, instantâneo de dados, datas e resumo da arquitetura |

O ciclo de implementação seguiu um padrão consistente: inspecionar o estado atual, fazer a alteração coerente mais pequena, validar sintaxe e comportamento HTTP, verificar layouts responsivos e atualizar a documentação. O agente usou comandos de shell, PHP/MySQL, verificações no navegador/CDP e `python-pptx` para a apresentação.

---

## 9. Testes e validação

### 9.1 Verificações de sintaxe

| Verificação | Resultado |
|---|---|
| `php -l` nos ficheiros PHP alterados | Passou |
| `node --check public/assets/js/company.js` | Passou |
| `node --check public/assets/js/product.js` | Passou |

### 9.2 Verificações da aplicação

| Verificação | Resultado |
|---|---|
| Estado HTTP da página inicial | 200 |
| Ligação à base de dados | Passou |
| Formulário de produto da empresa | Carrega e submete |
| Lista de produtos da empresa | Carrega e filtra |
| Página de produto da loja | Carrega |
| Troca de idioma PT/EN | Passou |
| Troca claro/escuro | Passou |

### 9.3 Verificações funcionais do produto

| Verificação | Resultado |
|---|---|
| Repetidor de variantes | Passou |
| Repetidor de especificações | Passou |
| Pré-visualização da imagem principal | Passou |
| Pré-visualização múltipla da galeria | Passou |
| Pré-visualização de fotos por cor | Passou |
| Remoção de galeria existente | Passou |
| Remoção de imagem por cor existente | Passou |
| Editar produto ativo mantém `active` | Passou |
| Editar produto rejeitado passa a `pending` | Passou |
| ID da variante preservado na edição | Passou |
| Stock atualizado no próprio registo | Passou |
| Troca de galeria por cor na loja | Passou |

### 9.4 Verificações responsivas

Resoluções testadas:

- 1440px
- 1024px
- 768px
- 390px

Páginas testadas:

- formulário de produto da empresa;
- lista de produtos da empresa;
- página de produto da loja.

Resultado:

- sem overflow horizontal do documento;
- sem navegação ou filtros cortados a 390px;
- colunas do formulário:
  - 1440px → duas colunas com aside sticky;
  - 1024px → duas colunas;
  - 768px → uma coluna, aside estático;
  - 390px → uma coluna;
- cartões de cor:
  - duas colunas em desktop;
  - uma coluna em mobile.

### 9.5 Verificações de email

| Verificação | Resultado |
|---|---|
| Ligação TLS a `smtp.gmail.com:587` | Passou |
| Validação de certificado | Passou |
| Upgrade STARTTLS | Passou |
| Entrega autenticada no Gmail | Não verificada — credenciais não configuradas |
| Registo de estado em `email_log` | Implementado |

O `.env` atual usa `MAIL_DRIVER=log`, para manter as demonstrações offline e estáveis.

---

## 10. Resultados e estado atual

### 10.1 Concluído

- Loja e catálogo completos.
- Carrinho e checkout simulado.
- Área de cliente.
- Onboarding e aprovação de empresas.
- Criação de produtos pela empresa.
- Edição de produtos pela empresa.
- Imagem principal, galeria e fotos por cor.
- Troca de cor na loja.
- Backoffice de admin e moderador.
- Fila de aprovação.
- Chat de suporte.
- Registo de emails e suporte SMTP.
- PT/EN e temas claro/escuro.
- Correções responsivas.
- Documentação e apresentação.

### 10.2 Limitações atuais

- Pagamentos simulados.
- Envio e tracking simulados.
- Entrega no Gmail não verificada até configurar uma palavra-passe de aplicação.
- Sem conjunto de testes de navegador automatizado.
- O chat usa polling, não WebSockets.
- Sem CDN de imagens ou otimização avançada.
- Sem integração Gmail via OAuth.

### 10.3 Próximos passos recomendados

1. Configurar uma palavra-passe de aplicação do Gmail e fazer um teste real de entrega.
2. Adicionar testes end-to-end automatizados para checkout e edição de produtos.
3. Adicionar testes de integração para `ModificationService`.
4. Adicionar redimensionamento/compressão de imagens nos uploads.
5. Adicionar um checklist de implementação em produção.
6. Considerar OAuth 2.0 para Gmail em produção.
7. Adicionar logging estruturado e monitorização.
8. Adicionar migrações de base de dados em vez de scripts de reinstalação total.

---

## 11. Conclusão

O TRIVEX demonstra uma arquitetura de comércio eletrónico completa e autónoma, com fluxos operacionais realistas. O projeto vai além de um catálogo simples ao combinar gestão de produtos por empresas, aprovação de administradores, controlo de alterações de moderadores, chat de suporte e checkout transacional.

O trabalho recente no fluxo de produto fecha uma lacuna importante: as empresas passam a gerir toda a apresentação do produto, incluindo variantes, especificações, imagem principal, galeria e fotos específicas por cor, enquanto a loja reflete essas escolhas de forma dinâmica.

O trabalho restante é sobretudo operacional: configurar credenciais reais de email, adicionar testes automatizados e preparar o endurecimento para produção. A aplicação principal está funcional, documentada e pronta para demonstração.

O TRIVEX documenta também o seu próprio processo de desenvolvimento: o prompt inicial, os prompts seguintes, os modelos utilizados, as datas e o instantâneo de dados estão registados nos Apêndices E e F.

---

## Apêndice A — Contas de demonstração

| Perfil | Email | Palavra-passe |
|---|---|---|
| Administrador | `admin@trivex.pt` | `Admin123!` |
| Moderador | `moderador@trivex.pt` | `Mod12345!` |
| Cliente | `cliente@trivex.pt` | `Cliente123!` |
| Empresa | `empresa@trivex.pt` | `Empresa123!` |
| Empresa pendente | `tiago@oceanonorte.pt` | `Empresa123!` |

---

## Apêndice B — Rotas principais

### Loja

`/`, `/loja`, `/categoria/{slug}`, `/pesquisa`, `/produto/{slug}`, `/promocoes`, `/pagina/{slug}`, `/api/pesquisa`

### Carrinho e checkout

`/carrinho`, `/carrinho/adicionar`, `/carrinho/atualizar`, `/carrinho/remover`, `/carrinho/limpar`, `/checkout`, `/checkout/sucesso/{number}`

### Autenticação e conta

`/login`, `/registo`, `/verificar-email`, `/logout`, `/conta`, `/conta/encomendas`, `/conta/perfil`, `/conta/moradas`

### Empresa

`/empresa/painel`, `/empresa/produtos`, `/empresa/produtos/novo`, `/empresa/produtos/{id}/editar`

### Backoffice

`/admin`, `/admin/utilizadores`, `/admin/stock`, `/admin/encomendas`, `/admin/empresas`, `/admin/propostas`, `/admin/paginas`, `/admin/moderadores`, `/admin/aprovacoes`, `/admin/notificacoes`, `/admin/mailbox`, `/admin/chats`

---

## Apêndice C — Tabelas da base de dados

`roles`, `companies`, `users`, `categories`, `products`, `product_variants`, `product_color_images`, `addresses`, `orders`, `order_items`, `reviews`, `pages`, `chats`, `chat_messages`, `pending_modifications`, `notifications`, `email_log`.

---

## Apêndice D — Comandos de validação

```bash
php -l public/index.php
php -l src/routes.php
php -l src/Controllers/CompanyController.php
php -l src/Core/Mailer.php
php -l src/Models/Product.php
node --check public/assets/js/company.js
node --check public/assets/js/product.js
php database/scan_lang.php
```

---

## Apêndice E — Registo de prompts

### E.1 Prompt inicial (verbatim)

O prompt inicial foi escrito em português. É reproduzido abaixo sem tradução.

```text
Você é um **Engenheiro de Software Sénior Profissional** responsável por arquitetar, estruturar e desenvolver um website completo de e-commerce de **Triatlo**, inspirado na experiência de navegação e funcionalidade da Decathlon.

---

### 1. Contexto do Ambiente e Tecnologia

* **Ambiente de Desenvolvimento:** O projeto será alojado e executado no **Laragon** na sua máquina local. Utilize os serviços do Laragon (Apache/Nginx, PHP, MySQL, Mailtrap/Sendmail local) para rodar e testar a aplicação.
* **Stack Tecnológica:**
* **Frontend:** HTML5, CSS3 (com suporte nativo a variáveis CSS para temas) e JavaScript nativo (vanilla JS).
* **Backend:** PHP (orientado a objetos, estruturado com padrão MVC ou arquitetura modular limpa).
* **Base de Dados:** MySQL (com `PDO` e prepared statements contra SQL Injection).
* **Envio de E-mails:** PHP `mail()` ou PHPMailer via servidor SMTP do Laragon.
* **Internacionalização (i18n):** Sistema multi-idioma nativo para suporte completo em **Português (PT)** e **Inglês (EN)** (com ficheiros de dicionário/tradução e alternância sem recarregar a sessão).



---

### 2. Design, Identidade Visual e Temas

* **Temática Principal:** Triatlo (Natação, Ciclismo, Corrida).
* **Paleta de Cores:** Azul como cor dominante (azul escuro/oceano, azul elétrico/desportivo, branco e cinza claro para contraste).
* **Suporte a Temas (Dark/Light Mode):**
* Alternância dinâmica entre **Modo Claro (Light)** e **Modo Escuro (Dark)**.
* Persistência da preferência do utilizador via `localStorage` e suporte ao tema do sistema operacional (`prefers-color-scheme`).


* **Página Especial de Promoções:** Uma página dedicada exclusivamente a promoções temporárias com temática de **Halloween**, com personalização estética específica (detalhes temáticos, banners e badges festivos adaptados ao universo do desporto), mantendo suporte ao Dark/Light mode e idioma selecionado.

---

### 3. Hierarquia de Utilizadores e Permissões

1. **Guest (Visitante Não Autenticado):**
* Pode navegar, pesquisar e filtrar produtos.
* Adicionar produtos ao carrinho (armazenado via `Cookies` / `LocalStorage` / `PHP Sessions`).
* Não pode deixar avaliações/reviews de produtos.
* Não guarda informações de compras nem tem acesso a histórico.


2. **User (Cliente Registado):**
* Registo de conta com envio de link único de verificação de e-mail.
* Acesso à conta ativado apenas após clicar no link recebido por e-mail.
* Pode deixar avaliações/reviews e pontuações (1 a 5 estrelas) nos produtos.
* Acesso a uma área pessoal com **histórico completo de compras** (detalhes de encomendas passadas, itens comprados, valores e estado dos pedidos).
* Pode guardar e reutilizar dados de faturação e envio para compras futuras.


3. **Empresa (Parceiro Comercial):**
* Criação de conta submetida para aprovação manual (não é ativada por e-mail automático).
* Validação de conta feita por um Moderador/Administrador através de contacto externo (e-mail).
* Possui painel para submeter propostas de novos produtos (com fotos, preço, stock, tamanhos, descrição e especificações).
* A submissão não publica o produto diretamente: envia para aprovação do Backoffice.


4. **Moderador:**
* Acesso limitado ao Backoffice.
* Pode visualizar stocks, listas de utilizadores, empresas, processos ativos, páginas e suporte por chat.
* Quaisquer alterações efetuadas por um Moderador (em produtos, utilizadores, conteúdos) ficam em estado **Pendente** e geram uma notificação de confirmação no painel do Administrador (com exceção do chat em tempo real).


5. **Administrador:**
* Acesso total e irrestrito ao Backoffice.
* Aprovação de alterações propostas por Moderadores.
* Gestão de contas de Moderadores e Empresas.



---

### 4. Funcionalidades do Frontoffice (Loja Pública)

1. **Cabeçalho Global:**
* Seletor de Idioma (**PT / EN**).
* Toggle/Alternador de Tema (**Light / Dark Mode**).
* Barra de navegação, barra de pesquisa, ícone de carrinho e acesso à conta/perfil.


2. **Página Principal (Homepage):**
* Banner em destaque, categorias principais (Natação, Ciclismo, Corrida, Nutrição, Equipamento), produtos populares e novidades.


3. **Páginas de Categorias e Pesquisa:**
* Lista de produtos por modalidade.
* Barra de pesquisa em tempo real com filtros por preço, tamanho, marca e disponibilidade.


4. **Página de Detalhe de Produto:**
* Galeria de imagens do produto.
* Seleção de tamanho, cor, quantidade e visualização do stock em tempo real.
* Descrição detalhada, tabela de especificações técnicas e secção de avaliações de utilizadores registados.


5. **Página de Promoções de Halloween:**
* Layout e estilização temática com elementos visuais de Halloween mantendo o fundo azul/escuro ajustável da loja.
* Lista de artigos em saldo.


6. **Carrinho de Compras:**
* Gestão dinâmica de itens, quantidade, cálculo automático de totais.


7. **Simulação de Checkout:**
* Formulário para recolha de morada e dados de envio.
* Métodos de pagamento simulados (Cartão de Crédito, MB WAY, Referência Multibanco).
* **Sem verificação real de dados bancários/cartão** (por ser um ambiente de simulação).
* Ao finalizar, regista a encomenda no **histórico de compras do utilizador**, altera o estado na base de dados e envia automaticamente um e-mail de confirmação/envio simulado para o e-mail do comprador.


8. **Área do Cliente (Perfil e Histórico de Compras):**
* Painel onde o utilizador registado pode consultar os dados de perfil e consultar o seu **histórico detalhado de compras**, acompanhando o estado atual de cada encomenda (Pedido, Enviado, Recebido, etc.).



---

### 5. Funcionalidades do Backoffice (Painel de Gestão)

1. **Gestão de Utilizadores:**
* Tabela com lista completa de utilizadores registados.
* Colunas com estatísticas: número de reviews criadas e quantidade total de produtos comprados (integrado com o histórico de compras).


2. **Gestão de Stock:**
* Tabela de controlo de inventário de produtos e respetivas variações (tamanhos/cores).


3. **Processos Ativos (Encomendas):**
* Tabela de processos organizados por data de criação.
* Estado do processo: `Pedido`, `Enviado`, `Recebido`, `Perdido`, `Não Recebido`.


4. **Gestão de Moderadores:**
* Listagem, criação e revogação de acessos a moderadores.


5. **Gestão de Empresas:**
* Lista de empresas registadas.
* Aprovação manual de contas de empresas.
* Análise de propostas de novos produtos submetidos pelas empresas para inserção no catálogo.


6. **Gestão de Páginas:**
* Ativação e desativação de páginas do site (Categorias, Promoções, etc.).
* A Página Principal **não pode** ser desativada.


7. **Sistema de Gestão de Chats de Suporte:**
* Fila centralizada de chats iniciados pelos utilizadores.


8. **Confirmação de Alterações (Exclusivo Admin):**
* Lista de edições feitas por Moderadores aguardando revisão e aprovação final do Administrador antes de serem refletidas na base de dados pública.



---

### 6. Regras do Sistema de Chat de Suporte

* **Abertura:** Quando um utilizador inicia um pedido de suporte, um novo chat é criado no Backoffice com o estado **"Aberto"**.
* **Atendimento:** Quando um Moderador entra no chat, o estado muda automaticamente de **"Aberto"** para **"Em progresso"**.
* **Abandono:** Se o Moderador sair da conversa sem a concluir, o estado regressa a **"Aberto"** para que outro elemento da equipa possa atender.
* **Conclusão:** Ao terminar o atendimento, o Moderador clica no botão **"Concluir"**, alterando o estado para **"Concluído"**.
* **Arquivamento:** Após 10 minutos no estado "Concluído", uma rotina/cron automática transfere o chat para o **"Arquivo de Chats"**.

---

### 7. Entregáveis de Arquitetura e Diagramas

Antes ou juntamente com a geração do código, crie e inclua no projeto os seguintes diagramas (utilizando formato Mermaid.js ou texto estruturado):

1. **Diagrama de Entidade-Relação (ERD / Base de Dados MySQL):**
* Tabelas: `users`, `roles`, `companies`, `products`, `product_variants`, `categories`, `orders` (histórico de compras), `order_items`, `reviews`, `chats`, `chat_messages`, `pages`, `pending_modifications`.


2. **Diagrama de Hierarquia de Contas e Permissões:**
* Mapeamento dos papéis (`Guest`, `User`, `Empresa`, `Moderador`, `Administrador`) e matriz de acessos às rotas do sistema.


3. **Diagrama do Fluxo do Chat e Estados:**
* Transição de estados (`Aberto` -> `Em progresso` -> `Aberto` / `Concluído` -> `Arquivado`).


4. **Diagrama de Fluxo de Compras, Histórico e Processos Ativos:**
* Percurso desde a adição ao carrinho, simulação de checkout, geração do registo no histórico de compras do cliente, envio do e-mail simulado e acompanhamento do estado no Backoffice.



---

### 8. Orientações Finais para o Desenvolvimento

* Organize o código em pastas claras (ex: `/config`, `/public`, `/src/controllers`, `/src/models`, `/src/views`, `/lang/pt.php`, `/lang/en.php`, `/assets/css`, `/assets/js`).
* Implemente um ficheiro CSS modular utilizando variáveis para cores primárias, secundárias e superfícies (`--bg-color`, `--text-color`, `--primary-blue`, etc.) para garantir a transição suave entre Light e Dark Mode.
* Forneça o ficheiro SQL (`database.sql`) com o esquema completo da base de dados e dados iniciais (seeders) para categorias, utilizador admin, moderador de teste, compras de exemplo no histórico e produtos de triatlo.
* Certifique-se de que o design seja responsivo e limpo. Sinta-se à vontade para adicionar melhorias no Frontoffice/Backoffice que elevem a qualidade profissional do projeto.
```

### E.2 Registo prompt a prompt

O registo abaixo cobre os 22 prompts do utilizador no registo de sessão, incluindo os prompts de continuação que dizem apenas "Resume".

| # | Data | Prompt | O que fez | Resultado |
|---|---|---|---|---|
| 1 | 2026-10-22 10:38 | Especificação completa do TRIVEX (ver E.1) | Construiu a plataforma de raiz: arquitetura, schema, dados iniciais, loja, contas, portal da empresa, backoffice, chat, i18n, temas e diagramas. | Base da aplicação e da documentação. |
| 2 | 2026-10-22 11:24 | `Resume` | Continuou a construção inicial; sem novos requisitos. | Implementação inicial avançou. |
| 3 | 2026-10-22 11:35 | `Resume` | Continuou a construção inicial. | Implementação inicial avançou. |
| 4 | 2026-10-22 11:35 | `Resume` | Continuou a construção inicial. | Implementação inicial avançou. |
| 5 | 2026-10-22 12:01 | `Resume` | Continuou a construção inicial. | Implementação inicial avançou. |
| 6 | 2026-10-22 14:25 | `Resume` | Continuou a construção inicial e a validação. | Implementação inicial concluída. |
| 7 | 2026-10-29 14:34 | Lista de erros e pedido de tipografia | Corrigiu nove erros de UI/PHP e introduziu uma escala global de tipografia. | Checkout, detalhe de encomenda, rodapé, chat, indicador de stock, painéis de conta e alinhamento da administração corrigidos; `--text-scale: 1.2`. |
| 8 | 2026-10-29 15:17 | `resume` | Continuou a verificação de erros e tipografia. | Correções validadas. |
| 9 | 2026-10-29 15:44 | `resume` | Continuou a mesma tarefa. | Correções validadas. |
| 10 | 2026-10-29 15:44 | `resume` | Continuou a mesma tarefa. | Correções validadas. |
| 11 | 2026-10-30 16:24 | `resume` | Continuou a mesma tarefa. | Correções validadas. |
| 12 | 2026-10-30 17:05 | Entrega Gmail, fluxo de produto da empresa, multimédia e fotos por cor | Reformulou o caminho SMTP/email, reparou os repetidores de variantes/especificações, adicionou pré-visualizações de imagem, carregamento de galeria, edição de produtos e fotos por cor. | Fluxo de produto da empresa concluído; `product_color_images` adicionada; suporte SMTP preparado, mas a entrega em Gmail continua por verificar enquanto faltarem credenciais. |
| 13 | 2026-11-05 09:09 | `resume` | Continuou o fluxo de produto e o trabalho de email. | Fluxo de produto e multimédia concluído. |
| 14 | 2026-11-05 09:31 | `resume` | Continuou a mesma tarefa. | Validação e documentação. |
| 15 | 2026-11-05 09:32 | `resume` | Continuou a mesma tarefa. | Validação e documentação. |
| 16 | 2026-11-05 09:32 | `resume` | Continuou a mesma tarefa. | Validação e documentação. |
| 17 | 2026-11-05 09:33 | `resume` | Continuou a mesma tarefa. | Validação e documentação. |
| 18 | 2026-11-05 09:33 | `resume` | Continuou a mesma tarefa. | Validação e documentação. |
| 19 | 2026-11-05 09:44 | Pedido de manual, relatório, diagramas e PowerPoint | Criou `docs/MANUAL.md`, `docs/REPORT.md`, atualizou `docs/diagrams.md` e gerou `docs/TRIVEX-Presentation.pptx`. | Conjunto de documentação e apresentação de 18 diapositivos. |
| 20 | 2026-11-05 10:15 | Pedido de versões em português | Criou `docs/MANUAL-PT.md`, `docs/RELATORIO-PT.md` e `docs/TRIVEX-Apresentacao-PT.pptx`; atualizou as ligações do README. | Conjunto documental completo em português. |
| 21 | 2026-11-05 10:35 | `resume` | Continuou a tarefa de documentação em português. | Conjunto em português concluído. |
| 22 | 2026-11-05 10:46 | Pedido de atualização do relatório (este documento) | Adicionou o prompt inicial, o registo de prompts, os modelos de IA, as datas, o resumo da arquitetura e o instantâneo de dados aos dois relatórios. | Relatórios atualizados para a versão 1.1. |

### E.3 Prompts de seguimento substantivos (verbatim)

#### Prompt 7 — 2026-10-29

```text
There are some bugs on the website:
. On the after paying page, theres text that goes outside of the box (see anex1)
. When looking at the encomenda details from client, the line goes through the text, blocking the text. (see anex2)
. footer has a button where the text goes outside the button. (see anex 3)
. php error when sending starting support chat from client + the sent message is shown as supporter message, not the client; chat has two "aberto" texts. (see anex4, anex7)
. "in stock" text is on the products panels is bugged (see anex 5)
. on the user account page, the "encomendas" panel and "avaliações" panel are not alined (see anex6)
. in the backoffice, "aberto" tetx is not aligned with other elements on the panel (see anex8)
. company informations are not aligned and divided correctly (see anex9)

Correct the bugs.

After correcting the bugs, make the text/font bigger. Its too small. Test the bigger text/font on every page to see if everything aligns right.
```

#### Prompt 12 — 2026-10-30

```text
Bug: emails are not sent into the inbox of gmail. Fix it.

On company account: buttons for adding more variations and teachnical definitions dont work. When putting in an image the icon of image input doesnt change (but the image was nputed). Make it so the company user could see the image that he putted there. Also add a way to add more pictures to a product (1 for main picture and other for gallery).

Regarding the companies products - make it so they can edit the product (info, material, photos, etc.)

In products, when changing color make it so the photos also change (the company will need to add photos for each color / variation)
```

#### Prompt 19 — 2026-11-05

```text
ok. For now make an instruction manual about this project in .md extension. Also make make a report document and update diagram.md if it is necessary. Also make me a powerPoint about this project explaining core mechanics and objectives.
```

#### Prompt 20 — 2026-11-05

```text
can you also make portuguese versions of these?
```

#### Prompt 22 — 2026-11-05

```text
When i said project report (from portuguese "relatorio") of a project. It should include my inicial prompt, my other promtpts, how the site was made (i think you got that part but you can edit it aswell if needed), what each prompt did (prompt logs, at the end), resumed architecture, ai models used, datas, etc. Make thos for both inglish and portuguese report documents
```

---

## Apêndice F — Instantâneo de dados e ambiente

| Item | Valor |
|---|---|
| Caminho do projeto | `C:\laragon\www\projeto` |
| Ambiente | Laragon em Windows |
| PHP | 8.3.30 |
| MySQL | 8.4.3 |
| Base de dados | `trivex` |
| Tabelas | 17 |
| Produtos | 20 (última verificação) |
| Variantes de produto | 45 (última verificação) |
| Linhas em `product_color_images` | 0 (última verificação) |
| Encomendas | 6 (base de dados atual; 5 nos dados iniciais originais) |
| Utilizadores | 12 (base de dados atual; 8 nos dados iniciais originais) |
| Perfis | 4 |
| Método de envio de email | `log` no `.env` atual; código SMTP pronto |
| Entrega Gmail | Não verificada; requer uma palavra-passe de aplicação válida e configuração SMTP |
| Resoluções testadas | 1440, 1024, 768 e 390 px |
| Data da documentação | 5 de novembro de 2026 |

Os dados são de demonstração (dados iniciais). A base de dados foi inspecionada pela última vez em 5 de novembro de 2026; as contagens podem mudar quando forem criados produtos ou encomendas.

---

**TRIVEX** — swim · bike · run
