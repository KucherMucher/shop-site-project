<?php

declare(strict_types=1);

use App\Controllers\AccountController;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\CompanyController;
use App\Controllers\HomeController;
use App\Controllers\LocaleController;
use App\Controllers\PageController;
use App\Controllers\ReviewController;
use App\Controllers\ShopController;
use App\Controllers\SupportController;
use App\Core\Router;

/** @var Router $router */
$router = new Router();

/* ------------------------------- Storefront ------------------------------- */
$router->get('/', [HomeController::class, 'index'], [], 'home');
$router->post('/newsletter', [HomeController::class, 'newsletter'], ['throttle:newsletter,5,60']);

$router->get('/loja', [ShopController::class, 'index'], [], 'shop');
$router->get('/categoria/{slug}', [ShopController::class, 'category'], [], 'category');
$router->get('/pesquisa', [ShopController::class, 'search'], [], 'search');
$router->get('/produto/{slug}', [ShopController::class, 'product'], [], 'product');
$router->get('/promocoes', [ShopController::class, 'promotions'], [], 'promotions');
$router->get('/pagina/{slug}', [PageController::class, 'show'], [], 'page');
$router->get('/api/pesquisa', [ShopController::class, 'apiSearch']);
$router->get('/idioma/{locale}', [LocaleController::class, 'switch'], [], 'locale');

/* ---------------------------------- Cart ---------------------------------- */
$router->get('/carrinho', [CartController::class, 'index'], [], 'cart');
$router->post('/carrinho/adicionar', [CartController::class, 'add']);
$router->post('/carrinho/atualizar', [CartController::class, 'update']);
$router->post('/carrinho/remover', [CartController::class, 'remove']);
$router->post('/carrinho/limpar', [CartController::class, 'clear']);

/* -------------------------------- Checkout -------------------------------- */
$router->get('/checkout', [CheckoutController::class, 'index']);
$router->post('/checkout', [CheckoutController::class, 'place'], ['throttle:checkout,12,60']);
$router->get('/checkout/sucesso/{number}', [CheckoutController::class, 'success']);

/* ---------------------------------- Auth ---------------------------------- */
$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest', 'throttle:login,8,60']);
$router->get('/registo', [AuthController::class, 'showRegister'], ['guest']);
$router->post('/registo', [AuthController::class, 'register'], ['guest', 'throttle:register,6,300']);
$router->get('/verificar-email', [AuthController::class, 'showVerify']);
$router->post('/verificar-email/reenviar', [AuthController::class, 'resendVerification'], ['throttle:resend,4,300']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth']);
$router->get('/empresa/pendente', [CompanyController::class, 'pending']);

/* --------------------------------- Account -------------------------------- */
$router->get('/conta', [AccountController::class, 'index'], ['auth']);
$router->get('/conta/encomendas', [AccountController::class, 'orders'], ['auth']);
$router->get('/conta/encomendas/{id}', [AccountController::class, 'orderShow'], ['auth']);
$router->get('/conta/perfil', [AccountController::class, 'profile'], ['auth']);
$router->post('/conta/perfil', [AccountController::class, 'updateProfile'], ['auth']);
$router->get('/conta/moradas', [AccountController::class, 'addresses'], ['auth']);
$router->post('/conta/moradas', [AccountController::class, 'storeAddress'], ['auth']);
$router->post('/conta/moradas/{id}/eliminar', [AccountController::class, 'deleteAddress'], ['auth']);
$router->post('/conta/moradas/{id}/predefinir', [AccountController::class, 'defaultAddress'], ['auth']);

/* --------------------------------- Reviews -------------------------------- */
$router->post('/avaliacao', [ReviewController::class, 'store'], ['auth']);

/* ------------------------------- Support chat ------------------------------ */
$router->get('/suporte', [SupportController::class, 'index']);
$router->post('/suporte/chat', [SupportController::class, 'start'], ['auth']);
$router->get('/suporte/chat/{id}', [SupportController::class, 'show'], ['auth']);
$router->get('/suporte/chat/{id}/mensagens', [SupportController::class, 'messages'], ['auth']);
$router->post('/suporte/chat/{id}/mensagens', [SupportController::class, 'postMessage'], ['auth']);

/* --------------------------------- Company -------------------------------- */
$router->get('/empresa/painel', [CompanyController::class, 'dashboard'], ['role:company', 'company']);
$router->get('/empresa/produtos', [CompanyController::class, 'products'], ['role:company', 'company']);
$router->get('/empresa/produtos/novo', [CompanyController::class, 'createProduct'], ['role:company', 'company']);
$router->post('/empresa/produtos', [CompanyController::class, 'storeProduct'], ['role:company', 'company']);
$router->get('/empresa/produtos/{id}/editar', [CompanyController::class, 'editProduct'], ['role:company', 'company']);
$router->post('/empresa/produtos/{id}', [CompanyController::class, 'updateProduct'], ['role:company', 'company']);

/* -------------------------------- Backoffice ------------------------------- */
$staff = ['role:admin,moderator'];
$admin = ['role:admin'];

$router->get('/admin', [AdminController::class, 'dashboard'], $staff);
$router->get('/admin/utilizadores', [AdminController::class, 'users'], $staff);
$router->post('/admin/utilizadores/{id}/estado', [AdminController::class, 'updateUser'], $staff);
$router->get('/admin/stock', [AdminController::class, 'stock'], $staff);
$router->post('/admin/stock/{id}', [AdminController::class, 'updateStock'], $staff);
$router->get('/admin/encomendas', [AdminController::class, 'orders'], $staff);
$router->get('/admin/encomendas/{id}', [AdminController::class, 'orderShow'], $staff);
$router->post('/admin/encomendas/{id}/estado', [AdminController::class, 'updateOrder'], $staff);
$router->get('/admin/empresas', [AdminController::class, 'companies'], $staff);
$router->post('/admin/empresas/{id}/estado', [AdminController::class, 'companyStatus'], $staff);
$router->get('/admin/propostas', [AdminController::class, 'proposals'], $staff);
$router->post('/admin/propostas/{id}/estado', [AdminController::class, 'proposalStatus'], $staff);
$router->get('/admin/paginas', [AdminController::class, 'pages'], $staff);
$router->post('/admin/paginas/{id}/alternar', [AdminController::class, 'togglePage'], $staff);
$router->get('/admin/moderadores', [AdminController::class, 'moderators'], $admin);
$router->post('/admin/moderadores', [AdminController::class, 'createModerator'], $admin);
$router->post('/admin/moderadores/{id}/alternar', [AdminController::class, 'toggleModerator'], $admin);
$router->get('/admin/aprovacoes', [AdminController::class, 'approvals'], $admin);
$router->post('/admin/aprovacoes/{id}/aprovar', [AdminController::class, 'approveModification'], $admin);
$router->post('/admin/aprovacoes/{id}/rejeitar', [AdminController::class, 'rejectModification'], $admin);
$router->get('/admin/notificacoes', [AdminController::class, 'notifications'], $staff);
$router->get('/admin/mailbox', [AdminController::class, 'mailbox'], $admin);
$router->get('/admin/mailbox/{id}', [AdminController::class, 'mailShow'], $admin);

/* Chats */
$router->get('/admin/chats', [AdminController::class, 'chats'], $staff);
$router->get('/admin/chats/arquivo', [AdminController::class, 'chatArchive'], $staff);
$router->get('/admin/chats/{id}', [AdminController::class, 'chatShow'], $staff);
$router->get('/admin/chats/{id}/mensagens', [AdminController::class, 'chatMessages'], $staff);
$router->post('/admin/chats/{id}/mensagens', [AdminController::class, 'chatPost'], $staff);
$router->post('/admin/chats/{id}/heartbeat', [AdminController::class, 'chatHeartbeat'], $staff);
$router->post('/admin/chats/{id}/entrar', [AdminController::class, 'chatJoin'], $staff);
$router->post('/admin/chats/{id}/sair', [AdminController::class, 'chatLeave'], $staff);
$router->post('/admin/chats/{id}/concluir', [AdminController::class, 'chatConclude'], $staff);
