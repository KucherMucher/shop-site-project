<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\HttpException;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Review;

final class ShopController extends Controller
{
    public function index(): string
    {
        return $this->catalog([], t('shop.title'), t('shop.subtitle'));
    }

    public function category(string $slug): string
    {
        $category = Category::findBySlug($slug);
        if (!$category) {
            throw new HttpException(404);
        }
        if (empty($category['active'])) {
            throw new HttpException(404, t('errors.page_inactive'));
        }

        $filters = $this->filters();
        $filters['category'] = $slug;
        $result = Product::search($filters, $this->pageNumber(), 12);

        return $this->render('shop/catalog', [
            'title' => localized($category, 'name') . ' · ' . config('app.name'),
            'meta_description' => localized($category, 'description'),
            'category' => $category,
            'categories' => Category::allActive(),
            'products' => $result['items'],
            'result' => $result,
            'filters' => $filters,
            'brands' => Product::brands(),
            'sizes' => Product::sizes(),
            'heading' => localized($category, 'name'),
            'subtitle' => t('shop.category_subtitle', ['category' => mb_strtolower(localized($category, 'name'))]),
            'baseQuery' => ['category' => $slug],
        ]);
    }

    public function search(): string
    {
        $filters = $this->filters();
        $q = trim((string) ($filters['q'] ?? ''));
        $result = Product::search($filters, $this->pageNumber(), 12);

        return $this->render('shop/search', [
            'title' => t('shop.search_title') . ' · ' . config('app.name'),
            'products' => $result['items'],
            'result' => $result,
            'filters' => $filters,
            'categories' => Category::allActive(),
            'brands' => Product::brands(),
            'sizes' => Product::sizes(),
            'q' => $q,
        ]);
    }

    private function catalog(array $extra, string $heading, string $subtitle): string
    {
        $filters = array_merge($this->filters(), $extra);
        $result = Product::search($filters, $this->pageNumber(), 12);

        return $this->render('shop/catalog', [
            'title' => $heading . ' · ' . config('app.name'),
            'meta_description' => $subtitle,
            'category' => null,
            'categories' => Category::allActive(),
            'products' => $result['items'],
            'result' => $result,
            'filters' => $filters,
            'brands' => Product::brands(),
            'sizes' => Product::sizes(),
            'heading' => $heading,
            'subtitle' => $subtitle,
            'baseQuery' => [],
        ]);
    }

    private function filters(): array
    {
        $brand = $_GET['brand'] ?? [];
        if (!is_array($brand)) {
            $brand = [$brand];
        }
        $brand = array_values(array_filter(array_map('trim', $brand)));

        return [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'brand' => $brand,
            'size' => trim((string) ($_GET['size'] ?? '')),
            'min_price' => trim((string) ($_GET['min_price'] ?? '')),
            'max_price' => trim((string) ($_GET['max_price'] ?? '')),
            'in_stock' => isset($_GET['in_stock']) ? 1 : 0,
            'promo' => isset($_GET['promo']) ? 1 : 0,
            'sort' => (string) ($_GET['sort'] ?? 'featured'),
            'page' => $this->pageNumber(),
        ];
    }

    public function product(string $slug): string
    {
        $product = Product::findBySlug($slug);
        if (!$product) {
            throw new HttpException(404);
        }
        $category = Category::find((int) $product['category_id']);
        if (!$category || !$category['active']) {
            throw new HttpException(404);
        }

        $variants = Product::variants((int) $product['id']);
        $colorImages = Product::colorImages((int) $product['id']);
        $reviews = Product::reviews((int) $product['id']);
        $rating = Product::rating((int) $product['id']);
        $userReview = Auth::check() ? Review::findForUser((int) $product['id'], (int) Auth::id()) : null;
        $related = Product::related((int) $product['category_id'], (int) $product['id'], 4);
        $totalStock = array_sum(array_column($variants, 'stock'));

        return $this->render('shop/product', [
            'title' => localized($product, 'name') . ' · ' . config('app.name'),
            'meta_description' => localized($product, 'short'),
            'product' => $product,
            'category' => $category,
            'variants' => $variants,
            'colorImages' => $colorImages,
            'reviews' => $reviews,
            'rating' => $rating,
            'userReview' => $userReview,
            'related' => $related,
            'totalStock' => $totalStock,
            'breadcrumbs' => [
                ['label' => t('product.breadcrumb_home'), 'url' => '/'],
                ['label' => localized($category, 'name'), 'url' => '/categoria/' . $category['slug']],
                ['label' => localized($product, 'name'), 'url' => null],
            ],
        ]);
    }

    public function promotions(): string
    {
        $page = Page::findBySlug('promocoes');
        if ($page && empty($page['active'])) {
            throw new HttpException(404, t('errors.page_inactive'));
        }
        $products = Product::promotions();
        $maxDiscount = 0;
        $end = null;
        foreach ($products as $product) {
            $maxDiscount = max($maxDiscount, discount_percent($product));
            if (!empty($product['promo_ends_at'])) {
                $end = $end === null || strtotime($product['promo_ends_at']) < $end ? strtotime($product['promo_ends_at']) : $end;
            }
        }
        if ($end === null) {
            $end = strtotime('+12 days');
        }

        return $this->render('shop/promotions', [
            'title' => t('nav.promotions') . ' · ' . config('app.name'),
            'products' => $products,
            'maxDiscount' => $maxDiscount,
            'promoEnd' => $end,
            'page' => $page,
        ]);
    }

    public function apiSearch(): never
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        if (mb_strlen($q) < 2) {
            json_response(['items' => []]);
        }
        $result = Product::search(['q' => $q], 1, 6);
        $items = [];
        foreach ($result['items'] as $product) {
            $items[] = [
                'name' => localized($product, 'name'),
                'brand' => $product['brand'],
                'price' => money(product_price($product)),
                'old_price' => discount_percent($product) > 0 ? money($product['base_price']) : null,
                'image' => asset('img/' . $product['image']),
                'url' => url('/produto/' . $product['slug']),
                'category' => localized($product, 'category_name'),
            ];
        }
        json_response(['items' => $items, 'total' => $result['total'], 'q' => $q]);
    }
}
