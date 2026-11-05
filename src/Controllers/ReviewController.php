<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\HttpException;
use App\Core\Session;
use App\Models\Product;
use App\Models\Review;

final class ReviewController extends Controller
{
    public function store(): never
    {
        $productId = (int) request_input('product_id', 0);
        $rating = (int) request_input('rating', 0);
        $title = trim((string) request_input('title', ''));
        $body = trim((string) request_input('body', ''));
        $product = Product::find($productId, true);

        if (!$product) {
            throw new HttpException(404);
        }

        if ($rating < 1 || $rating > 5 || $body === '') {
            Session::flash('error', t('review.invalid'));
            redirect('/produto/' . $product['slug'] . '#reviews');
        }

        $existing = Review::findForUser($productId, (int) Auth::id());
        if ($existing) {
            Review::update((int) $existing['id'], (int) Auth::id(), $rating, mb_substr($title, 0, 150), $body);
            Session::flash('success', t('review.updated'));
        } else {
            Review::create($productId, (int) Auth::id(), $rating, mb_substr($title, 0, 150), $body);
            Session::flash('success', t('review.success'));
        }

        redirect('/produto/' . $product['slug'] . '#reviews');
    }
}
