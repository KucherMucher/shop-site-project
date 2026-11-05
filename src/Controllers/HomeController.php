<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Review;

final class HomeController extends Controller
{
    public function index(): string
    {
        $categories = Category::allActive();
        $featured = Product::featured(4);
        $newest = Product::newest(8);
        $promotions = Product::promotions();

        $stats = [
            'products' => Product::countActive(),
            'athletes' => 12400 + (int) Database::scalar("SELECT COUNT(*) FROM orders"),
            'rating' => Review::average() ?: 4.8,
        ];

        $homePage = \App\Models\Page::findBySlug('home');

        return $this->render('home/index', [
            'title' => t('nav.home') . ' · ' . config('app.name'),
            'categories' => $categories,
            'featured' => $featured,
            'newest' => $newest,
            'promotions' => $promotions,
            'stats' => $stats,
            'homePage' => $homePage,
        ]);
    }

    public function newsletter(): never
    {
        $email = trim((string) request_input('email', ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', t('home.newsletter_invalid'));
        } else {
            Session::flash('success', t('home.newsletter_success'));
        }
        redirect('/');
    }
}
