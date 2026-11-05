<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Session;
use App\Models\Address;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Core\Database;

final class AccountController extends Controller
{
    public function index(): string
    {
        $user = Auth::user();
        $userId = (int) $user['id'];
        $stats = Order::userStats($userId);
        $stats['reviews'] = (int) Database::scalar('SELECT COUNT(*) FROM reviews WHERE user_id = ?', [$userId]);
        $recentOrders = Order::latestForUser($userId, 3);
        $reviews = Database::fetchAll(
            'SELECT r.*, p.name_pt, p.name_en, p.slug, p.image FROM reviews r
             JOIN products p ON p.id = r.product_id WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 4',
            [$userId]
        );

        return $this->render('account/index', [
            'title' => t('account.title') . ' · ' . config('app.name'),
            'user' => $user,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'reviews' => $reviews,
            'activeNav' => 'overview',
        ], 'layouts/account');
    }

    public function orders(): string
    {
        $user = Auth::user();
        return $this->render('account/orders', [
            'title' => t('account.orders_title') . ' · ' . config('app.name'),
            'user' => $user,
            'orders' => Order::forUser((int) $user['id']),
            'activeNav' => 'orders',
        ], 'layouts/account');
    }

    public function orderShow(string $id): string
    {
        $user = Auth::user();
        $order = Order::findForUser((int) $id, (int) $user['id']);
        if (!$order) {
            abort(404);
        }
        return $this->render('account/order-show', [
            'title' => $order['order_number'] . ' · ' . config('app.name'),
            'user' => $user,
            'order' => $order,
            'items' => Order::items((int) $order['id']),
            'activeNav' => 'orders',
        ], 'layouts/account');
    }

    public function profile(): string
    {
        return $this->render('account/profile', [
            'title' => t('account.profile_title') . ' · ' . config('app.name'),
            'user' => Auth::user(),
            'activeNav' => 'profile',
        ], 'layouts/account');
    }

    public function updateProfile(): never
    {
        $user = Auth::user();
        $name = trim((string) request_input('name', ''));
        $phone = trim((string) request_input('phone', ''));
        $current = (string) request_input('current_password', '');
        $new = (string) request_input('new_password', '');

        $errors = [];
        if ($name === '') {
            $errors['name'] = t('company.required_fields');
        }
        if ($new !== '') {
            if (!password_verify($current, $user['password_hash'])) {
                $errors['current_password'] = t('auth.invalid_credentials');
            } elseif (mb_strlen($new) < 8) {
                $errors['new_password'] = t('auth.password_hint');
            } elseif ($new !== (string) request_input('new_password_confirmation', '')) {
                $errors['new_password_confirmation'] = t('auth.password_mismatch');
            }
        }
        if ($errors) {
            Session::errors($errors);
            Session::old($_POST);
            redirect('/conta/perfil');
        }

        $data = ['name' => $name, 'phone' => $phone, 'locale' => locale()];
        if ($new !== '') {
            $data['password_hash'] = password_hash($new, PASSWORD_BCRYPT);
        }
        User::update((int) $user['id'], $data);
        Auth::refresh();
        Session::flash('success', t('account.profile_saved'));
        redirect('/conta/perfil');
    }

    public function addresses(): string
    {
        return $this->render('account/addresses', [
            'title' => t('account.addresses_title') . ' · ' . config('app.name'),
            'user' => Auth::user(),
            'addresses' => Address::forUser((int) Auth::id()),
            'activeNav' => 'addresses',
        ], 'layouts/account');
    }

    public function storeAddress(): never
    {
        $userId = (int) Auth::id();
        $data = [
            'label' => trim((string) request_input('label', 'Casa')) ?: 'Casa',
            'name' => trim((string) request_input('name', '')),
            'phone' => trim((string) request_input('phone', '')),
            'address' => trim((string) request_input('address', '')),
            'city' => trim((string) request_input('city', '')),
            'postal_code' => trim((string) request_input('postal_code', '')),
            'country' => trim((string) request_input('country', 'Portugal')),
            'is_default_shipping' => isset($_POST['is_default_shipping']) ? 1 : 0,
            'is_default_billing' => isset($_POST['is_default_billing']) ? 1 : 0,
        ];
        if ($data['name'] === '' || $data['address'] === '' || $data['city'] === '' || $data['postal_code'] === '') {
            Session::flash('error', t('company.required_fields'));
            Session::old($_POST);
            redirect('/conta/moradas');
        }
        if ($data['is_default_shipping']) {
            Database::query('UPDATE addresses SET is_default_shipping = 0 WHERE user_id = ?', [$userId]);
        }
        if ($data['is_default_billing']) {
            Database::query('UPDATE addresses SET is_default_billing = 0 WHERE user_id = ?', [$userId]);
        }
        Address::create($userId, $data);
        Session::flash('success', t('account.address_saved'));
        redirect('/conta/moradas');
    }

    public function deleteAddress(string $id): never
    {
        Address::delete((int) $id, (int) Auth::id());
        Session::flash('success', t('account.address_deleted'));
        redirect('/conta/moradas');
    }

    public function defaultAddress(string $id): never
    {
        Address::setDefault((int) $id, (int) Auth::id(), (string) request_input('type', 'shipping'));
        Session::flash('success', t('account.saved'));
        redirect('/conta/moradas');
    }
}
