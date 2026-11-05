<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Models\Category;
use App\Models\Company;
use App\Models\Notification;
use App\Models\Product;

final class CompanyController extends Controller
{
    public function pending(): string
    {
        $company = Auth::check() ? Company::findForUser((int) Auth::id()) : null;
        return $this->render('company/pending', [
            'title' => t('auth.company_pending_title') . ' · ' . config('app.name'),
            'company' => $company,
        ], 'layouts/auth');
    }

    public function dashboard(): string
    {
        $company = Company::findForUser((int) Auth::id());
        if (!$company) {
            abort(403);
        }
        $companyId = (int) $company['id'];

        $stats = [
            'products' => (int) Database::scalar("SELECT COUNT(*) FROM products WHERE company_id = ? AND status = 'active'", [$companyId]),
            'pending' => (int) Database::scalar("SELECT COUNT(*) FROM products WHERE company_id = ? AND status = 'pending'", [$companyId]),
            'units' => (int) Database::scalar(
                'SELECT COALESCE(SUM(oi.quantity),0) FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE p.company_id = ?',
                [$companyId]
            ),
            'revenue' => (float) Database::scalar(
                'SELECT COALESCE(SUM(oi.line_total),0) FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE p.company_id = ?',
                [$companyId]
            ),
        ];

        $products = Database::fetchAll(
            'SELECT p.*, (SELECT COALESCE(SUM(v.stock),0) FROM product_variants v WHERE v.product_id = p.id) AS total_stock
             FROM products p WHERE p.company_id = ? ORDER BY p.created_at DESC LIMIT 8',
            [$companyId]
        );

        $orders = Database::fetchAll(
            "SELECT DISTINCT o.*, u.name AS user_name FROM orders o
             JOIN order_items oi ON oi.order_id = o.id
             JOIN products p ON p.id = oi.product_id
             LEFT JOIN users u ON u.id = o.user_id
             WHERE p.company_id = ? ORDER BY o.created_at DESC LIMIT 6",
            [$companyId]
        );

        return $this->render('company/dashboard', [
            'title' => t('company.panel_title') . ' · ' . config('app.name'),
            'company' => $company,
            'stats' => $stats,
            'products' => array_map([Product::class, 'decode'], $products),
            'orders' => $orders,
            'companyNav' => 'dashboard',
        ], 'layouts/company');
    }

    public function products(): string
    {
        $company = Company::findForUser((int) Auth::id());
        if (!$company) {
            abort(403);
        }
        $products = Database::fetchAll(
            "SELECT p.*, (SELECT COALESCE(SUM(v.stock),0) FROM product_variants v WHERE v.product_id = p.id) AS total_stock,
                (SELECT COUNT(*) FROM product_variants v2 WHERE v2.product_id = p.id) AS variants_count
             FROM products p WHERE p.company_id = ? ORDER BY p.created_at DESC",
            [(int) $company['id']]
        );
        return $this->render('company/products', [
            'title' => t('company.products') . ' · ' . config('app.name'),
            'company' => $company,
            'products' => array_map([Product::class, 'decode'], $products),
            'companyNav' => 'products',
        ], 'layouts/company');
    }

    public function createProduct(): string
    {
        $company = Company::findForUser((int) Auth::id());
        if (!$company) {
            abort(403);
        }
        return $this->render('company/product-form', [
            'title' => t('company.new_product') . ' · ' . config('app.name'),
            'company' => $company,
            'categories' => Category::allActive(),
            'companyNav' => 'new',
        ], 'layouts/company');
    }

    public function editProduct(string $id): string
    {
        $company = Company::findForUser((int) Auth::id());
        if (!$company) {
            abort(403);
        }
        $product = Product::find((int) $id);
        if (!$product || (int) $product['company_id'] !== (int) $company['id']) {
            abort(404);
        }

        return $this->render('company/product-form', [
            'title' => t('company.edit_product') . ' · ' . config('app.name'),
            'company' => $company,
            'categories' => Category::allActive(),
            'product' => $product,
            'variants' => Product::variants((int) $product['id'], false),
            'colorImages' => Product::colorImages((int) $product['id']),
            'companyNav' => 'products',
        ], 'layouts/company');
    }

    public function storeProduct(): never
    {
        $company = Company::findForUser((int) Auth::id());
        if (!$company) {
            abort(403);
        }

        $this->persistProduct($company, null);
        Session::flash('success', t('company.product_created'));
        redirect('/empresa/produtos');
    }

    public function updateProduct(string $id): never
    {
        $company = Company::findForUser((int) Auth::id());
        if (!$company) {
            abort(403);
        }
        $product = Product::find((int) $id);
        if (!$product || (int) $product['company_id'] !== (int) $company['id']) {
            abort(404);
        }

        $this->persistProduct($company, $product);
        Session::flash('success', t('company.product_updated'));
        redirect('/empresa/produtos');
    }

    private function persistProduct(array $company, ?array $product): int
    {
        $isEdit = $product !== null;
        $productId = $isEdit ? (int) $product['id'] : 0;
        $backUrl = $isEdit ? '/empresa/produtos/' . $productId . '/editar' : '/empresa/produtos/novo';

        $namePt = trim((string) request_input('name_pt', ''));
        $nameEn = trim((string) request_input('name_en', $namePt));
        $categoryId = (int) request_input('category_id', 0);
        $price = (float) str_replace(',', '.', (string) request_input('base_price', '0'));
        $salePrice = (float) str_replace(',', '.', (string) request_input('sale_price', '0'));
        $brand = trim((string) request_input('brand', $company['name']));
        $sizes = array_values((array) request_input('variant_size', []));
        $colors = array_values((array) request_input('variant_color', []));
        $stocks = array_values((array) request_input('variant_stock', []));
        $variantIds = array_values((array) request_input('variant_id', []));
        $category = Category::find($categoryId);

        $hasSize = false;
        foreach ($sizes as $size) {
            if (trim((string) $size) !== '') {
                $hasSize = true;
                break;
            }
        }

        $errors = [];
        if ($namePt === '' || !$category || $price <= 0) {
            $errors['form'] = t('company.required_fields');
        }
        if (!$hasSize) {
            $errors['variants'] = t('company.required_fields');
        }
        if ($errors) {
            Session::errors($errors);
            Session::old($_POST);
            redirect($backUrl);
        }

        if ($isEdit) {
            $slug = (string) $product['slug'];
        } else {
            $slugBase = slugify($namePt) ?: 'produto-empresa';
            $slug = $slugBase;
            $i = 0;
            while (Database::scalar('SELECT COUNT(*) FROM products WHERE slug = ?', [$slug]) > 0) {
                $slug = $slugBase . '-' . (++$i);
            }
        }

        $mainUpload = $this->handleUpload($_FILES['image'] ?? [], $errors, 'image');
        $imagePath = $mainUpload ?: ($isEdit ? (string) $product['image'] : 'products/placeholder.svg');

        $existingGallery = [];
        if ($isEdit) {
            $allowedGallery = array_values(array_filter((array) ($product['gallery'] ?? []), 'is_string'));
            foreach ((array) request_input('existing_gallery', []) as $image) {
                if (is_string($image) && in_array($image, $allowedGallery, true)) {
                    $existingGallery[] = $image;
                }
            }
        }
        $newGallery = $this->uploadMany($_FILES['gallery_images'] ?? [], $errors, 'gallery');
        $gallery = array_values(array_unique(array_merge($existingGallery, $newGallery)));
        if (!$gallery) {
            $gallery = [$imagePath];
        }

        $specs = [];
        $specLabelsPt = (array) request_input('spec_label_pt', []);
        $specLabelsEn = (array) request_input('spec_label_en', []);
        $specValues = (array) request_input('spec_value', []);
        foreach ($specLabelsPt as $index => $labelPt) {
            $labelPt = trim((string) $labelPt);
            $value = trim((string) ($specValues[$index] ?? ''));
            if ($labelPt === '' || $value === '') {
                continue;
            }
            $specs[] = [
                'label_pt' => $labelPt,
                'label_en' => trim((string) ($specLabelsEn[$index] ?? $labelPt)),
                'value' => $value,
            ];
        }

        $currentColorImages = $isEdit ? Product::colorImages($productId) : [];
        $colorImageMap = $currentColorImages;
        $colorRows = request_input('color_image_color', null);
        if (is_array($colorRows)) {
            $colorImageMap = [];
            $existingColorInput = (array) request_input('existing_color_image', []);
            foreach ($colorRows as $key => $colorValue) {
                $color = trim((string) $colorValue);
                if ($color === '') {
                    continue;
                }
                $allowedColorImages = $currentColorImages[$color] ?? [];
                $existing = array_values(array_intersect(
                    (array) ($existingColorInput[$key] ?? []),
                    $allowedColorImages
                ));
                $new = $this->uploadMany(
                    $this->filesForKey($_FILES['color_image_file'] ?? [], $key),
                    $errors,
                    'color_images'
                );
                $images = array_values(array_unique(array_merge($existing, $new)));
                if ($images) {
                    $colorImageMap[$color] = $images;
                }
            }
        }

        if ($errors) {
            Session::errors($errors);
            Session::old($_POST);
            redirect($backUrl);
        }

        $descriptionPt = trim((string) request_input('description_pt', ''));
        $descriptionEn = trim((string) request_input('description_en', $descriptionPt));

        $data = [
            'category_id' => $categoryId,
            'name_pt' => $namePt,
            'name_en' => $nameEn,
            'short_pt' => trim((string) request_input('short_pt', '')) ?: mb_substr($descriptionPt, 0, 160),
            'short_en' => trim((string) request_input('short_en', '')) ?: mb_substr($descriptionEn, 0, 160),
            'description_pt' => $descriptionPt,
            'description_en' => $descriptionEn,
            'brand' => $brand !== '' ? $brand : (string) $company['name'],
            'specs' => json_encode($specs, JSON_UNESCAPED_UNICODE),
            'base_price' => $price,
            'sale_price' => $salePrice > 0 && $salePrice < $price ? $salePrice : null,
            'image' => $imagePath,
            'gallery' => json_encode($gallery, JSON_UNESCAPED_UNICODE),
        ];

        if ($isEdit) {
            if (($product['status'] ?? '') === 'rejected') {
                $data['status'] = 'pending';
                $data['rejection_note'] = null;
            }
        } else {
            $data += [
                'company_id' => $company['id'],
                'slug' => $slug,
                'featured' => 0,
                'is_new' => 1,
                'status' => 'pending',
                'submitted_by' => Auth::id(),
            ];
        }

        return Database::transaction(function () use (
            $isEdit,
            $productId,
            $data,
            $sizes,
            $colors,
            $stocks,
            $variantIds,
            $slug,
            $colorImageMap,
            $company,
            $namePt
        ): int {
            if ($isEdit) {
                Product::update($productId, $data);
                $savedId = $productId;
            } else {
                $savedId = Product::create($data);
            }

            $this->syncVariants($savedId, $sizes, $colors, $stocks, $variantIds, $slug, $isEdit);
            $this->syncColorImages($savedId, $colorImageMap);

            if (!$isEdit) {
                Notification::notifyAdmins(
                    'proposal',
                    t('notify.proposal_title'),
                    t('notify.proposal_message', ['company' => $company['name'], 'product' => $namePt]),
                    '/admin/propostas'
                );
            }

            return $savedId;
        });
    }

    /**
     * Update the submitted rows in place so order history keeps its variant
     * references, then deactivate variants the company removed.
     */
    private function syncVariants(
        int $productId,
        array $sizes,
        array $colors,
        array $stocks,
        array $variantIds,
        string $slug,
        bool $isEdit
    ): void {
        $existing = $isEdit ? Product::variants($productId, false) : [];
        $existingById = [];
        foreach ($existing as $variant) {
            $existingById[(int) $variant['id']] = $variant;
        }

        $kept = [];
        $count = 0;
        foreach ($sizes as $index => $size) {
            $size = trim((string) $size);
            if ($size === '') {
                continue;
            }
            $count++;
            $color = trim((string) ($colors[$index] ?? '')) ?: 'Padrão';
            $stock = max(0, (int) ($stocks[$index] ?? 0));
            $variantId = (int) ($variantIds[$index] ?? 0);

            if ($variantId > 0 && isset($existingById[$variantId])) {
                Database::update('product_variants', [
                    'size' => $size,
                    'color' => $color,
                    'stock' => $stock,
                    'active' => 1,
                ], 'id = :id AND product_id = :product_id', [
                    'id' => $variantId,
                    'product_id' => $productId,
                ]);
                $kept[] = $variantId;
                continue;
            }

            Database::insert('product_variants', [
                'product_id' => $productId,
                'sku' => $this->makeSku($slug, $productId, $count),
                'size' => $size,
                'color' => $color,
                'color_hex' => '#0B6BFF',
                'stock' => $stock,
                'active' => 1,
            ]);
        }

        foreach ($existingById as $variantId => $variant) {
            if (!in_array($variantId, $kept, true)) {
                Database::update('product_variants', ['active' => 0], 'id = :id', ['id' => $variantId]);
            }
        }
    }

    private function makeSku(string $slug, int $productId, int $count): string
    {
        $prefix = strtoupper(substr((string) preg_replace('/[^a-z0-9]/', '', strtolower($slug)), 0, 6)) ?: 'PROD';
        $base = 'TRV-' . $prefix . '-' . $productId . '-' . $count;
        $sku = $base;
        $suffix = 0;
        while ((int) Database::scalar('SELECT COUNT(*) FROM product_variants WHERE sku = ?', [$sku]) > 0) {
            $sku = $base . '-' . (++$suffix);
        }
        return $sku;
    }

    private function syncColorImages(int $productId, array $map): void
    {
        Database::query('DELETE FROM product_color_images WHERE product_id = ?', [$productId]);
        $order = 0;
        foreach ($map as $color => $images) {
            foreach ((array) $images as $image) {
                if (!is_string($image) || $image === '') {
                    continue;
                }
                Database::insert('product_color_images', [
                    'product_id' => $productId,
                    'color' => (string) $color,
                    'image' => $image,
                    'sort_order' => $order++,
                ]);
            }
        }
    }

    private function handleUpload(array $file, array &$errors, string $errorKey = 'image'): ?string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK) {
            $errors[$errorKey] = t('company.image_hint');
            return null;
        }
        if (($file['size'] ?? 0) > 4 * 1024 * 1024) {
            $errors[$errorKey] = t('company.image_hint');
            return null;
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        $info = $tmp !== '' ? @getimagesize($tmp) : false;
        $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        if (!$info || !isset($allowed[$info[2]])) {
            $errors[$errorKey] = t('company.image_hint');
            return null;
        }

        $dir = BASE_PATH . '/public/assets/img/uploads/products';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $filename = date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$info[2]];
        if (!move_uploaded_file($tmp, $dir . '/' . $filename)) {
            $errors[$errorKey] = t('company.image_hint');
            return null;
        }
        return 'uploads/products/' . $filename;
    }

    /**
     * @return list<string>
     */
    private function uploadMany(array $files, array &$errors, string $errorKey): array
    {
        $paths = [];
        foreach ($this->normalizeFiles($files) as $file) {
            $path = $this->handleUpload($file, $errors, $errorKey);
            if ($path !== null) {
                $paths[] = $path;
            }
        }
        return $paths;
    }

    /**
     * @return list<array{name:string,type:string,tmp_name:string,error:int,size:int}>
     */
    private function normalizeFiles(array $files): array
    {
        if (!isset($files['name'])) {
            return [];
        }
        if (!is_array($files['name'])) {
            return [$files];
        }

        $out = [];
        foreach ($files['name'] as $key => $name) {
            if (is_array($name)) {
                continue;
            }
            $out[] = [
                'name' => (string) $name,
                'type' => (string) ($files['type'][$key] ?? ''),
                'tmp_name' => (string) ($files['tmp_name'][$key] ?? ''),
                'error' => (int) ($files['error'][$key] ?? UPLOAD_ERR_NO_FILE),
                'size' => (int) ($files['size'][$key] ?? 0),
            ];
        }
        return $out;
    }

    /**
     * Slice a nested $_FILES structure for one colour row, keeping the shape
     * expected by normalizeFiles().
     */
    private function filesForKey(array $files, string|int $key): array
    {
        if (!isset($files['name']) || !is_array($files['name']) || !array_key_exists($key, $files['name'])) {
            return [];
        }

        return [
            'name' => $files['name'][$key],
            'type' => $files['type'][$key] ?? '',
            'tmp_name' => $files['tmp_name'][$key] ?? '',
            'error' => $files['error'][$key] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$key] ?? 0,
        ];
    }
}
