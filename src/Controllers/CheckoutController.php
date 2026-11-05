<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\HttpException;
use App\Core\Session;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Support\MailService;

final class CheckoutController extends Controller
{
    public function index(): string
    {
        if (Cart::count() === 0) {
            Session::flash('warning', t('checkout.empty_cart'));
            redirect('/carrinho');
        }

        $user = Auth::user();
        $addresses = $user ? Address::forUser((int) $user['id']) : [];
        $defaultAddress = $addresses[0] ?? null;

        return $this->render('checkout/index', [
            'title' => t('checkout.title') . ' · ' . config('app.name'),
            'items' => Cart::items(),
            'subtotal' => Cart::subtotal(),
            'shipping' => Cart::shipping(),
            'total' => Cart::total(),
            'user' => $user,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'cartProblems' => Cart::problems(),
        ]);
    }

    public function place(): never
    {
        if (Cart::count() === 0) {
            Session::flash('warning', t('checkout.empty_cart'));
            redirect('/carrinho');
        }

        $input = [
            'email' => strtolower(trim((string) request_input('email', ''))),
            'shipping_name' => trim((string) request_input('shipping_name', '')),
            'shipping_phone' => trim((string) request_input('shipping_phone', '')),
            'shipping_address' => trim((string) request_input('shipping_address', '')),
            'shipping_city' => trim((string) request_input('shipping_city', '')),
            'shipping_postal_code' => trim((string) request_input('shipping_postal_code', '')),
            'shipping_country' => trim((string) request_input('shipping_country', 'Portugal')),
            'payment_method' => (string) request_input('payment_method', 'card'),
            'notes' => trim((string) request_input('notes', '')),
        ];

        $errors = [];
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = t('auth.email_required');
        }
        foreach ([
            'shipping_name' => t('common.name'),
            'shipping_address' => t('common.address'),
            'shipping_city' => t('common.city'),
            'shipping_postal_code' => t('common.postal_code'),
        ] as $field => $label) {
            if ($input[$field] === '') {
                $errors[$field] = t('checkout.required_address');
            }
        }
        if (!in_array($input['payment_method'], ['card', 'mbway', 'multibanco'], true)) {
            $errors['payment_method'] = t('checkout.simulation_notice');
        }
        if ($input['payment_method'] === 'mbway' && $input['shipping_phone'] === '') {
            $errors['shipping_phone'] = t('checkout.mbway_phone');
        }

        if ($errors) {
            Session::errors($errors);
            Session::old($_POST);
            redirect('/checkout');
        }

        $subtotal = Cart::subtotal();
        $shipping = Cart::shipping();
        $total = round($subtotal + $shipping, 2);
        $items = Cart::items();

        $paymentReference = match ($input['payment_method']) {
            'card' => 'CARD-•••• ' . substr(preg_replace('/\D/', '', (string) request_input('card_number', '4242424242424242')) ?: '4242', -4),
            'mbway' => 'MBW-' . random_int(100000, 999999),
            'multibanco' => 'MB-REF ' . implode(' ', str_split((string) random_int(100000000, 999999999), 3)),
        };

        try {
            $created = Order::create([
                'user_id' => Auth::id(),
                'email' => $input['email'],
                'status' => 'placed',
                'payment_method' => $input['payment_method'],
                'payment_status' => $input['payment_method'] === 'multibanco' ? 'pending' : 'paid',
                'payment_reference' => $paymentReference,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,
                'shipping_name' => $input['shipping_name'],
                'shipping_phone' => $input['shipping_phone'],
                'shipping_address' => $input['shipping_address'],
                'shipping_city' => $input['shipping_city'],
                'shipping_postal_code' => $input['shipping_postal_code'],
                'shipping_country' => $input['shipping_country'],
                'billing_name' => trim((string) request_input('billing_name', $input['shipping_name'])),
                'billing_address' => trim((string) request_input('billing_address', $input['shipping_address'])),
                'billing_city' => trim((string) request_input('billing_city', $input['shipping_city'])),
                'billing_postal_code' => trim((string) request_input('billing_postal_code', $input['shipping_postal_code'])),
                'billing_country' => trim((string) request_input('billing_country', $input['shipping_country'])),
                'notes' => $input['notes'] ?: null,
            ], $items);

            if (Auth::check() && isset($_POST['save_address']) && empty($_POST['address_id'])) {
                Address::create((int) Auth::id(), [
                    'label' => trim((string) request_input('address_label', 'Casa')) ?: 'Casa',
                    'name' => $input['shipping_name'],
                    'phone' => $input['shipping_phone'],
                    'address' => $input['shipping_address'],
                    'city' => $input['shipping_city'],
                    'postal_code' => $input['shipping_postal_code'],
                    'country' => $input['shipping_country'],
                    'is_default_shipping' => 1,
                ]);
            }
        } catch (\Throwable $e) {
            Session::errors(['checkout' => $e->getMessage()]);
            Session::old($_POST);
            redirect('/checkout');
        }

        $order = Order::find($created['id']);
        $items = Order::items($created['id']);
        try {
            MailService::sendOrderConfirmation($order, $items);
        } catch (\Throwable $e) {
            error_log('[TRIVEX] Order confirmation mail failed: ' . $e->getMessage());
        }

        Cart::clear();
        Session::set('last_order_number', $created['order_number']);
        Session::set('last_order_id', $created['id']);
        Session::flash('success', t('checkout.success_title'));

        redirect('/checkout/sucesso/' . $created['order_number']);
    }

    public function success(string $number): string
    {
        $order = Order::findByNumber($number);
        if (!$order) {
            throw new HttpException(404);
        }

        $isOwner = Auth::check() && (int) $order['user_id'] === (int) Auth::id();
        $isSession = Session::get('last_order_number') === $number;
        if (!$isOwner && !$isSession) {
            throw new HttpException(403);
        }

        $items = Order::items((int) $order['id']);

        return $this->render('checkout/success', [
            'title' => t('checkout.success_title') . ' · ' . config('app.name'),
            'order' => $order,
            'items' => $items,
        ]);
    }
}
