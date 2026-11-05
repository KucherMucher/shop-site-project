<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Session;
use App\Core\View;
use App\Models\Chat;
use App\Models\Company;
use App\Models\EmailLog;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Page;
use App\Models\PendingModification;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Support\MailService;
use App\Support\ModificationService;

final class AdminController extends Controller
{
    public function dashboard(): string
    {
        Chat::sweep();
        $monthly = $this->monthlySeries(6);
        $topProducts = Database::fetchAll(
            "SELECT p.id, p.name_pt, p.name_en, p.image, p.slug,
                SUM(oi.quantity) AS units, SUM(oi.line_total) AS revenue
             FROM order_items oi JOIN products p ON p.id = oi.product_id
             JOIN orders o ON o.id = oi.order_id
             WHERE o.status <> 'not_received'
             GROUP BY p.id, p.name_pt, p.name_en, p.image, p.slug
             ORDER BY units DESC LIMIT 5"
        );
        $stockAlerts = Database::fetchAll(
            'SELECT v.*, p.name_pt, p.name_en, p.slug, p.image
             FROM product_variants v JOIN products p ON p.id = v.product_id
             WHERE v.active = 1 AND v.stock <= 2 ORDER BY v.stock ASC, p.name_pt LIMIT 6'
        );

        return $this->renderAdmin('admin/dashboard', [
            'title' => t('admin.dashboard') . ' · ' . config('app.name'),
            'revenue' => Order::revenue(),
            'ordersTotal' => Order::total(),
            'ordersMonth' => Order::thisMonth(),
            'customers' => User::totalCustomers(),
            'customersNew' => User::newThisMonth(),
            'productsActive' => Product::countActive(),
            'reviewsCount' => Review::count(),
            'reviewsAvg' => Review::average(),
            'recentOrders' => Order::recent(6),
            'recentReviews' => Review::latest(4),
            'topProducts' => $topProducts,
            'stockAlerts' => $stockAlerts,
            'monthly' => $monthly,
            'chatCounts' => Chat::statusCounts(),
            'pendingList' => array_slice(PendingModification::pending(), 0, 4),
        ], 'dashboard');
    }

    /* ---------------------------------------------------------------------
     | Users
     |--------------------------------------------------------------------- */
    public function users(): string
    {
        return $this->renderAdmin('admin/users', [
            'title' => t('admin.users') . ' · ' . config('app.name'),
            'users' => User::allWithStats(
                trim((string) ($_GET['q'] ?? '')),
                (string) ($_GET['role'] ?? ''),
                (string) ($_GET['status'] ?? '')
            ),
            'filters' => ['q' => $_GET['q'] ?? '', 'role' => $_GET['role'] ?? '', 'status' => $_GET['status'] ?? ''],
        ], 'users');
    }

    public function updateUser(string $id): never
    {
        $target = User::find((int) $id);
        if (!$target || $target['role_slug'] === 'admin') {
            Session::flash('error', t('admin.cannot_change_admin'));
            redirect('/admin/utilizadores');
        }
        $status = (string) request_input('status', 'active');
        if (!in_array($status, ['active', 'suspended'], true)) {
            $status = 'active';
        }

        $result = ModificationService::submit(
            'user_status',
            (int) $target['id'],
            'update',
            ['status' => $status],
            t('admin.user_status_summary', ['name' => $target['name'], 'status' => $status === 'active' ? t('admin.activate') : t('admin.suspend')])
        );
        Session::flash($result['pending'] ? 'warning' : 'success', $result['message']);
        redirect('/admin/utilizadores');
    }

    /* ---------------------------------------------------------------------
     | Stock
     |--------------------------------------------------------------------- */
    public function stock(): string
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $state = (string) ($_GET['state'] ?? '');
        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = '(p.name_pt LIKE :q1 OR p.name_en LIKE :q2 OR v.sku LIKE :q3)';
            $like = '%' . $q . '%';
            $params['q1'] = $like;
            $params['q2'] = $like;
            $params['q3'] = $like;
        }
        if ($state === 'low') {
            $where[] = 'v.stock > 0 AND v.stock <= 5';
        } elseif ($state === 'out') {
            $where[] = 'v.stock = 0';
        }
        $rows = Database::fetchAll(
            'SELECT v.*, p.name_pt, p.name_en, p.slug AS product_slug, p.image, p.brand
             FROM product_variants v JOIN products p ON p.id = v.product_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY v.stock ASC, p.name_pt, v.id',
            $params
        );

        return $this->renderAdmin('admin/stock', [
            'title' => t('admin.stock') . ' · ' . config('app.name'),
            'variants' => $rows,
            'summary' => Product::stockSummary(),
            'filters' => ['q' => $q, 'state' => $state],
        ], 'stock');
    }

    public function updateStock(string $id): never
    {
        $variant = Product::variant((int) $id);
        if (!$variant) {
            abort(404);
        }
        $stock = max(0, (int) request_input('stock', 0));
        $summary = t('admin.stock_summary', [
            'product' => $variant['name_pt'],
            'size' => $variant['size'],
            'stock' => $stock,
        ]);
        $result = ModificationService::submit('variant_stock', (int) $variant['id'], 'update', ['stock' => $stock], $summary);
        Session::flash($result['pending'] ? 'warning' : 'success', $result['message']);
        redirect('/admin/stock?' . http_build_query(array_filter(['q' => $_GET['q'] ?? '', 'state' => $_GET['state'] ?? ''])));
    }

    /* ---------------------------------------------------------------------
     | Orders
     |--------------------------------------------------------------------- */
    public function orders(): string
    {
        $result = Order::all([
            'status' => (string) ($_GET['status'] ?? ''),
            'q' => trim((string) ($_GET['q'] ?? '')),
        ], $this->pageNumber(), 20);

        return $this->renderAdmin('admin/orders', [
            'title' => t('admin.orders') . ' · ' . config('app.name'),
            'orders' => $result['items'],
            'result' => $result,
            'counts' => Order::statusCounts(),
            'filters' => ['status' => $_GET['status'] ?? '', 'q' => $_GET['q'] ?? ''],
        ], 'orders');
    }

    public function orderShow(string $id): string
    {
        $order = Order::find((int) $id);
        if (!$order) {
            abort(404);
        }
        return $this->renderAdmin('admin/order-show', [
            'title' => $order['order_number'] . ' · ' . config('app.name'),
            'order' => $order,
            'items' => Order::items((int) $order['id']),
        ], 'orders');
    }

    public function updateOrder(string $id): never
    {
        $order = Order::find((int) $id);
        if (!$order) {
            abort(404);
        }
        $status = (string) request_input('status', 'placed');
        if (!in_array($status, Order::STATUSES, true)) {
            abort(422);
        }
        $tracking = trim((string) request_input('tracking_code', ''));
        $summary = t('admin.order_summary', [
            'number' => $order['order_number'],
            'status' => t('order.status.' . $status),
        ]);
        $result = ModificationService::submit('order_status', (int) $order['id'], 'update', [
            'status' => $status,
            'tracking_code' => $tracking ?: null,
        ], $summary);
        Session::flash($result['pending'] ? 'warning' : 'success', $result['message']);
        redirect('/admin/encomendas/' . $order['id']);
    }

    /* ---------------------------------------------------------------------
     | Companies / proposals
     |--------------------------------------------------------------------- */
    public function companies(): string
    {
        return $this->renderAdmin('admin/companies', [
            'title' => t('admin.companies') . ' · ' . config('app.name'),
            'companies' => Company::all(),
        ], 'companies');
    }

    public function companyStatus(string $id): never
    {
        $company = Company::find((int) $id);
        if (!$company) {
            abort(404);
        }
        $status = (string) request_input('status', 'approved');
        if (!in_array($status, ['approved', 'rejected'], true)) {
            abort(422);
        }
        $note = trim((string) request_input('note', ''));
        $summary = t('admin.company_summary', ['company' => $company['name'], 'status' => $status === 'approved' ? t('admin.approve') : t('admin.reject')]);
        $result = ModificationService::submit('company_status', (int) $company['id'], 'update', ['status' => $status, 'note' => $note], $summary);

        if (!$result['pending'] && $status === 'approved' && !empty($company['owner_email'])) {
            MailService::sendCompanyApproved($company, $company['owner_email']);
        }
        Session::flash($result['pending'] ? 'warning' : 'success', $result['message']);
        redirect('/admin/empresas');
    }

    public function proposals(): string
    {
        return $this->renderAdmin('admin/proposals', [
            'title' => t('admin.proposals') . ' · ' . config('app.name'),
            'proposals' => Company::proposals(),
        ], 'proposals');
    }

    public function proposalStatus(string $id): never
    {
        $product = Product::find((int) $id);
        if (!$product) {
            abort(404);
        }
        $status = (string) request_input('status', 'active');
        if (!in_array($status, ['active', 'rejected'], true)) {
            abort(422);
        }
        $note = trim((string) request_input('note', ''));
        $summary = t('admin.proposal_summary', [
            'product' => $product['name_pt'],
            'status' => $status === 'active' ? t('admin.approve') : t('admin.reject'),
        ]);
        $result = ModificationService::submit('product_status', (int) $product['id'], 'update', ['status' => $status, 'note' => $note], $summary);
        Session::flash($result['pending'] ? 'warning' : 'success', $result['message']);
        redirect('/admin/propostas');
    }

    /* ---------------------------------------------------------------------
     | Moderators (admin only)
     |--------------------------------------------------------------------- */
    public function moderators(): string
    {
        return $this->renderAdmin('admin/moderators', [
            'title' => t('admin.moderators') . ' · ' . config('app.name'),
            'moderators' => User::moderators(),
        ], 'moderators');
    }

    public function createModerator(): never
    {
        $name = trim((string) request_input('name', ''));
        $email = strtolower(trim((string) request_input('email', '')));
        $password = (string) request_input('password', '');

        $errors = [];
        if ($name === '' || $password === '') {
            $errors['form'] = t('company.required_fields');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = t('auth.email_required');
        }
        if (mb_strlen($password) < 8) {
            $errors['password'] = t('auth.password_hint');
        }
        if (User::emailExists($email)) {
            $errors['email'] = t('admin.email_taken');
        }
        if ($errors) {
            Session::errors($errors);
            Session::old($_POST);
            redirect('/admin/moderadores');
        }

        User::createModerator($name, $email, $password);
        Session::flash('success', t('admin.moderator_created'));
        redirect('/admin/moderadores');
    }

    public function toggleModerator(string $id): never
    {
        $target = User::find((int) $id);
        if (!$target || $target['role_slug'] !== 'moderator') {
            abort(404);
        }
        if ((int) $target['id'] === (int) Auth::id()) {
            abort(403);
        }
        $newStatus = $target['status'] === 'active' ? 'suspended' : 'active';
        User::setStatus((int) $target['id'], $newStatus);
        Session::flash('success', $newStatus === 'active' ? t('admin.user_updated') : t('admin.moderator_revoked'));
        redirect('/admin/moderadores');
    }

    /* ---------------------------------------------------------------------
     | Pages
     |--------------------------------------------------------------------- */
    public function pages(): string
    {
        return $this->renderAdmin('admin/pages', [
            'title' => t('admin.pages') . ' · ' . config('app.name'),
            'pages' => Page::all(),
        ], 'pages');
    }

    public function togglePage(string $id): never
    {
        $page = Page::find((int) $id);
        if (!$page) {
            abort(404);
        }
        if ($page['slug'] === 'home') {
            Session::flash('error', t('admin.home_locked'));
            redirect('/admin/paginas');
        }
        $active = !(bool) $page['active'];
        $summary = t('admin.page_summary', [
            'page' => $page['title_pt'],
            'status' => $active ? t('admin.page_active') : t('admin.page_inactive'),
        ]);
        $result = ModificationService::submit('page_active', (int) $page['id'], 'update', ['active' => $active], $summary);
        Session::flash($result['pending'] ? 'warning' : 'success', $result['message']);
        redirect('/admin/paginas');
    }

    /* ---------------------------------------------------------------------
     | Approvals (admin only)
     |--------------------------------------------------------------------- */
    public function approvals(): string
    {
        return $this->renderAdmin('admin/approvals', [
            'title' => t('admin.approvals') . ' · ' . config('app.name'),
            'pending' => PendingModification::pending(),
            'history' => array_slice(array_filter(PendingModification::all(), fn ($m) => $m['status'] !== 'pending'), 0, 20),
        ], 'approvals');
    }

    public function approveModification(string $id): never
    {
        $note = trim((string) request_input('note', ''));
        try {
            ModificationService::approve((int) $id, (int) Auth::id(), $note ?: null);
            Session::flash('success', t('admin.change_approved'));
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
        }
        redirect('/admin/aprovacoes');
    }

    public function rejectModification(string $id): never
    {
        $note = trim((string) request_input('note', ''));
        try {
            ModificationService::reject((int) $id, (int) Auth::id(), $note ?: null);
            Session::flash('success', t('admin.change_rejected'));
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
        }
        redirect('/admin/aprovacoes');
    }

    /* ---------------------------------------------------------------------
     | Chats
     |--------------------------------------------------------------------- */
    public function chats(): string
    {
        Chat::sweep();
        return $this->renderAdmin('admin/chats/index', [
            'title' => t('admin.chats') . ' · ' . config('app.name'),
            'chats' => Chat::queue('active'),
            'counts' => Chat::statusCounts(),
            'view' => 'active',
        ], 'chats');
    }

    public function chatArchive(): string
    {
        Chat::sweep();
        return $this->renderAdmin('admin/chats/index', [
            'title' => t('admin.archive') . ' · ' . config('app.name'),
            'chats' => Chat::queue('archive'),
            'counts' => Chat::statusCounts(),
            'view' => 'archive',
        ], 'chats');
    }

    public function chatShow(string $id): string
    {
        Chat::sweep();
        $chat = Chat::find((int) $id);
        if (!$chat) {
            abort(404);
        }

        if ($chat['status'] === 'open') {
            Chat::join((int) $chat['id'], (int) Auth::id());
            $chat = Chat::find((int) $chat['id']);
        } elseif ($chat['status'] === 'in_progress' && (int) $chat['moderator_id'] === (int) Auth::id()) {
            Chat::heartbeat((int) $chat['id'], (int) Auth::id());
        }

        return $this->renderAdmin('admin/chats/show', [
            'title' => $chat['subject'] . ' · ' . config('app.name'),
            'chat' => $chat,
            'messages' => Chat::messages((int) $chat['id']),
            'isAssigned' => (int) ($chat['moderator_id'] ?? 0) === (int) Auth::id(),
        ], 'chats');
    }

    public function chatMessages(string $id): never
    {
        Chat::sweep();
        $chat = Chat::find((int) $id);
        if (!$chat) {
            json_response(['error' => 'not_found'], 404);
        }
        if ($chat['status'] === 'in_progress' && (int) $chat['moderator_id'] === (int) Auth::id()) {
            Chat::heartbeat((int) $chat['id'], (int) Auth::id());
        }
        $after = (int) ($_GET['after'] ?? 0);
        $messages = Chat::messages((int) $chat['id'], $after);

        json_response([
            'ok' => true,
            'html' => View::raw('admin/chats/_messages', ['messages' => $messages, 'adminId' => (int) Auth::id()]),
            'last_id' => $messages ? (int) end($messages)['id'] : $after,
            'status' => $chat['status'],
            'status_label' => chat_status_label($chat['status']),
            'moderator' => $chat['moderator_name'] ?? null,
            'is_assigned' => (int) ($chat['moderator_id'] ?? 0) === (int) Auth::id(),
        ]);
    }

    public function chatPost(string $id): never
    {
        $chat = Chat::find((int) $id);
        if (!$chat) {
            json_response(['error' => 'not_found'], 404);
        }
        if ($chat['status'] !== 'in_progress' || (int) $chat['moderator_id'] !== (int) Auth::id()) {
            json_response(['error' => t('admin.chat_taken')], 422);
        }
        $body = trim((string) request_input('message', ''));
        if ($body === '') {
            json_response(['error' => t('company.required_fields')], 422);
        }
        Chat::postModeratorMessage((int) $chat['id'], (int) Auth::id(), mb_substr($body, 0, 2000));
        $messages = Chat::messages((int) $chat['id'], (int) ($_POST['after'] ?? 0));
        json_response([
            'ok' => true,
            'html' => View::raw('admin/chats/_messages', ['messages' => $messages, 'adminId' => (int) Auth::id()]),
            'last_id' => $messages ? (int) end($messages)['id'] : 0,
        ]);
    }

    public function chatHeartbeat(string $id): never
    {
        Chat::heartbeat((int) $id, (int) Auth::id());
        json_response(['ok' => true]);
    }

    public function chatJoin(string $id): never
    {
        $joined = Chat::join((int) $id, (int) Auth::id());
        Session::flash($joined ? 'success' : 'error', $joined ? t('admin.chat_joined') : t('admin.chat_taken'));
        redirect('/admin/chats/' . (int) $id);
    }

    public function chatLeave(string $id): never
    {
        Chat::leave((int) $id, (int) Auth::id());
        Session::flash('success', t('admin.chat_left'));
        redirect('/admin/chats');
    }

    public function chatConclude(string $id): never
    {
        Chat::conclude((int) $id, (int) Auth::id());
        Session::flash('success', t('admin.chat_concluded'));
        redirect('/admin/chats');
    }

    /* ---------------------------------------------------------------------
     | Notifications / mailbox
     |--------------------------------------------------------------------- */
    public function notifications(): string
    {
        $notifications = Notification::forUser((int) Auth::id(), 50);
        Notification::markAllRead((int) Auth::id());
        return $this->renderAdmin('admin/notifications', [
            'title' => t('notify.title') . ' · ' . config('app.name'),
            'notifications' => $notifications,
        ], 'notifications');
    }

    public function mailbox(): string
    {
        return $this->renderAdmin('admin/mailbox', [
            'title' => t('admin.mailbox') . ' · ' . config('app.name'),
            'emails' => EmailLog::latest(40),
        ], 'mailbox');
    }

    public function mailShow(string $id): string
    {
        $email = EmailLog::find((int) $id);
        if (!$email) {
            abort(404);
        }
        return $this->renderAdmin('admin/mail-show', [
            'title' => $email['subject'],
            'email' => $email,
        ], 'mailbox');
    }

    /* ---------------------------------------------------------------------
     | Helpers
     |--------------------------------------------------------------------- */
    private function renderAdmin(string $view, array $data, string $activeNav): string
    {
        Chat::sweep();
        return $this->render($view, array_merge([
            'admin' => Auth::user(),
            'activeNav' => $activeNav,
            'navPendingApprovals' => PendingModification::countPending(),
            'navUnread' => Notification::unreadCount((int) Auth::id()),
            'navChats' => Chat::unreadForStaff(),
            'navCompanies' => Company::pendingCount(),
            'navProposals' => Product::countPending(),
        ], $data), 'layouts/admin');
    }

    private function monthlySeries(int $months): array
    {
        $series = [];
        $map = [];
        foreach (Order::monthlySeries($months) as $row) {
            $map[$row['ym']] = $row;
        }
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime("-$i months"));
            $series[] = [
                'ym' => $ym,
                'label' => locale() === 'en' ? date('M', strtotime($ym . '-01')) : ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'][(int) date('n', strtotime($ym . '-01')) - 1],
                'orders' => (int) ($map[$ym]['orders'] ?? 0),
                'revenue' => (float) ($map[$ym]['revenue'] ?? 0),
            ];
        }
        return $series;
    }
}
