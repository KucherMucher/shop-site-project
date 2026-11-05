<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Session;

final class Cart
{
    private const SHIPPING_FLAT = 4.90;
    private const FREE_SHIPPING_FROM = 60.00;

    public static function raw(): array
    {
        return Session::get('cart', []);
    }

    public static function add(int $variantId, int $qty = 1): array
    {
        $variant = Product::variant($variantId);
        if (!$variant || $variant['product_status'] !== 'active' || !$variant['active']) {
            return ['ok' => false, 'message' => t('cart.invalid_product')];
        }
        $qty = max(1, min($qty, 10));
        $cart = self::raw();
        $current = (int) ($cart[$variantId] ?? 0);
        $newQty = min($current + $qty, (int) $variant['stock'], 10);
        if ((int) $variant['stock'] <= 0) {
            return ['ok' => false, 'message' => t('cart.out_of_stock')];
        }
        if ($current >= (int) $variant['stock']) {
            return ['ok' => false, 'message' => t('cart.stock_limit', ['count' => $variant['stock']])];
        }
        $cart[$variantId] = max(1, $newQty);
        Session::set('cart', $cart);
        return ['ok' => true, 'message' => t('cart.added'), 'count' => self::count()];
    }

    public static function update(int $variantId, int $qty): array
    {
        $cart = self::raw();
        if (!isset($cart[$variantId])) {
            return ['ok' => false, 'message' => t('cart.not_found')];
        }
        if ($qty <= 0) {
            unset($cart[$variantId]);
            Session::set('cart', $cart);
            return ['ok' => true, 'removed' => true, 'count' => self::count()];
        }
        $variant = Product::variant($variantId);
        if (!$variant) {
            return ['ok' => false, 'message' => t('cart.invalid_product')];
        }
        $qty = min($qty, (int) $variant['stock'], 10);
        $cart[$variantId] = max(1, $qty);
        Session::set('cart', $cart);
        return ['ok' => true, 'count' => self::count()];
    }

    public static function remove(int $variantId): void
    {
        $cart = self::raw();
        unset($cart[$variantId]);
        Session::set('cart', $cart);
    }

    public static function clear(): void
    {
        Session::forget('cart');
    }

    public static function count(): int
    {
        return array_sum(array_map('intval', self::raw()));
    }

    public static function mergeSessionCart(int $userId): void
    {
        // Cart is kept in the PHP session, which survives authentication.
    }

    public static function items(): array
    {
        $items = [];
        foreach (self::raw() as $variantId => $qty) {
            $variant = Product::variant((int) $variantId);
            if (!$variant) {
                continue;
            }
            $unit = (float) ($variant['sale_price'] ?? 0) > 0
                ? (float) $variant['sale_price']
                : (float) $variant['base_price'];
            $unit += (float) ($variant['price_delta'] ?? 0);
            $qty = max(1, (int) $qty);
            $items[] = [
                'variant_id' => (int) $variantId,
                'variant' => $variant,
                'quantity' => $qty,
                'unit_price' => round($unit, 2),
                'line_total' => round($unit * $qty, 2),
                'stock' => (int) $variant['stock'],
            ];
        }
        return $items;
    }

    public static function subtotal(): float
    {
        return round(array_sum(array_column(self::items(), 'line_total')), 2);
    }

    public static function shipping(): float
    {
        if (self::count() === 0) {
            return 0.0;
        }
        return self::subtotal() >= self::FREE_SHIPPING_FROM ? 0.0 : self::SHIPPING_FLAT;
    }

    public static function total(): float
    {
        return round(self::subtotal() + self::shipping(), 2);
    }

    public static function freeShippingThreshold(): float
    {
        return self::FREE_SHIPPING_FROM;
    }

    public static function problems(): array
    {
        $problems = [];
        foreach (self::items() as $item) {
            if ($item['quantity'] > $item['stock']) {
                $problems[] = t('cart.insufficient_stock', [
                    'name' => localized($item['variant'], 'name'),
                    'stock' => $item['stock'],
                ]);
            }
        }
        return $problems;
    }
}
