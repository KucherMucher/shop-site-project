<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Models\Cart;

final class CartController extends Controller
{
    public function index(): string
    {
        return $this->render('cart/index', [
            'title' => t('cart.title') . ' · ' . config('app.name'),
            'items' => Cart::items(),
            'subtotal' => Cart::subtotal(),
            'shipping' => Cart::shipping(),
            'total' => Cart::total(),
            'freeFrom' => Cart::freeShippingThreshold(),
        ]);
    }

    public function add(): never
    {
        $variantId = (int) request_input('variant_id', 0);
        $qty = max(1, (int) request_input('quantity', 1));
        $result = Cart::add($variantId, $qty);
        $this->respond($result, '/carrinho');
    }

    public function update(): never
    {
        $quantities = $_POST['quantities'] ?? [];
        if (is_array($quantities)) {
            foreach ($quantities as $variantId => $qty) {
                Cart::update((int) $variantId, (int) $qty);
            }
        } else {
            Cart::update((int) request_input('variant_id', 0), (int) request_input('quantity', 1));
        }
        Session::flash('success', t('cart.updated'));
        redirect('/carrinho');
    }

    public function remove(): never
    {
        Cart::remove((int) request_input('variant_id', 0));
        Session::flash('success', t('cart.removed'));
        redirect('/carrinho');
    }

    public function clear(): never
    {
        Cart::clear();
        Session::flash('success', t('cart.removed'));
        redirect('/carrinho');
    }

    private function respond(array $result, string $fallback = '/'): never
    {
        if ($this->wantsJson()) {
            json_response($result, $result['ok'] ? 200 : 422);
        }
        Session::flash($result['ok'] ? 'success' : 'error', $result['message']);
        if (!empty($result['ok']) && str_contains($_SERVER['HTTP_REFERER'] ?? '', '/produto/')) {
            back($fallback);
        }
        back($fallback);
    }
}
