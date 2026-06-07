<?php

namespace App\Controllers;

use App\Libraries\RazorpayGateway;
use App\Models\CouponsModel;
use App\Models\DashboardTemplateModel;
use App\Models\OrdersModel;
use App\Models\PaymentsModel;
use App\Models\ProductReviewsModel;
use App\Models\ProductsModel;
use App\Models\SettingsModel;
use App\Models\CategoriesModel;
use CodeIgniter\HTTP\ResponseInterface;

class Rest_api extends BaseController
{
    private const ROLE_CUSTOMER = 2;

    public function dashboardTemplate(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $template = $this->getDefaultUserDashboardTemplate();

        try {
            $db = db_connect();
            if ($db->tableExists('dashboard_templates')) {
                $row = (new DashboardTemplateModel())
                    ->where('dt_is_active', 1)
                    ->where('dt_status', 1)
                    ->orderBy('id', 'DESC')
                    ->first();

                if ($row) {
                    $stored = json_decode((string) ($row['dt_json'] ?? ''), true);
                    if (!empty($stored) && is_array($stored)) {
                        $template = array_replace_recursive($template, $stored);
                    }
                }
            } elseif ($db->tableExists('settings') || $db->tableExists('system_settings')) {
                $stored = (new SettingsModel())->getGroupSettings('user_dashboard_template');
                if (!empty($stored) && is_array($stored)) {
                    $template = array_replace_recursive($template, $stored);
                }
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        return $this->response->setJSON([
            'status' => true,
            'template' => $template,
        ]);
    }

    public function dashboardProducts(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $limit = (int) ($this->request->getGet('limit') ?? 8);
        if ($limit <= 0 || $limit > 24) {
            $limit = 8;
        }

        $categoryId = (int) ($this->request->getGet('category_id') ?? 0);
        if ($categoryId < 0) {
            $categoryId = 0;
        }

        $products = [];

        try {
            $db = db_connect();
            if ($db->tableExists('products')) {
                $builder = (new ProductsModel())
                    ->where('pr_status', 1);
                if ($categoryId > 0) {
                    $builder->where('pr_category', (string) $categoryId);
                }

                $rows = $builder
                    ->orderBy('id', 'DESC')
                    ->findAll($limit);

                foreach ($rows as $row) {
                    $name = trim((string) ($row['pr_name'] ?? 'Product'));
                    $description = trim((string) ($row['pr_description'] ?? ''));
                    $image = trim((string) ($row['pr_image'] ?? ''));

                    $products[] = [
                        'id' => (int) ($row['id'] ?? 0),
                        'name' => $name !== '' ? $name : 'Product',
                        'description' => $description,
                        'image_url' => $image !== '' ? base_url('uploads/products/' . $image) : '',
                        'price' => (float) ($row['pr_price'] ?? 0),
                        'stock' => (int) ($row['pr_stock'] ?? 0),
                    ];
                }
            }
        } catch (\Throwable $e) {
            $products = [];
        }

        return $this->response->setJSON([
            'status' => true,
            'products' => $products,
        ]);
    }

    public function dashboardCategories(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $limit = (int) ($this->request->getGet('limit') ?? 10);
        if ($limit <= 0 || $limit > 24) {
            $limit = 10;
        }

        $categories = [];

        try {
            $db = db_connect();
            if ($db->tableExists('categories')) {
                $rows = (new CategoriesModel())
                    ->where('ct_status', 1)
                    ->orderBy('ct_products', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->findAll($limit);

                foreach ($rows as $row) {
                    $name = trim((string) ($row['ct_name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }

                    $categories[] = [
                        'id' => (int) ($row['id'] ?? 0),
                        'name' => $name,
                        'products' => (int) ($row['ct_products'] ?? 0),
                    ];
                }
            }
        } catch (\Throwable $e) {
            $categories = [];
        }

        return $this->response->setJSON([
            'status' => true,
            'categories' => $categories,
        ]);
    }

    public function session(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $loggedIn = session()->get('logged_in') === true;
        $roleId = (int) (session()->get('us_role_id') ?? 0);
        $imageFile = trim((string) (session()->get('us_image') ?? ''));
        if ($imageFile === '') {
            $imageFile = 'default-user.svg';
        }

        return $this->response->setJSON([
            'status' => true,
            'logged_in' => $loggedIn,
            'user' => [
                'id' => (int) (session()->get('user_id') ?? 0),
                'name' => (string) (session()->get('us_name') ?? ''),
                'email' => (string) (session()->get('email') ?? ''),
                'role_id' => $roleId,
                'is_customer' => $roleId === self::ROLE_CUSTOMER,
                'image_url' => base_url('uploads/customers/' . $imageFile),
            ],
        ]);
    }

    public function catalogFilters(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $categories = [];
        $priceMin = 0;
        $priceMax = 0;

        try {
            $db = db_connect();

            if ($db->tableExists('products')) {
                $priceRow = $db->table('products')
                    ->select('MIN(pr_price) as min_price, MAX(pr_price) as max_price')
                    ->where('pr_status', 1)
                    ->get()
                    ->getRowArray();

                $priceMin = (float) ($priceRow['min_price'] ?? 0);
                $priceMax = (float) ($priceRow['max_price'] ?? 0);
            }

            if ($db->tableExists('categories') && $db->tableExists('products')) {
                $counts = $db->table('products')
                    ->select('pr_category, COUNT(*) as total')
                    ->where('pr_status', 1)
                    ->groupBy('pr_category')
                    ->get()
                    ->getResultArray();

                $countMap = [];
                foreach ($counts as $row) {
                    $key = trim((string) ($row['pr_category'] ?? ''));
                    if ($key !== '') {
                        $countMap[$key] = (int) ($row['total'] ?? 0);
                    }
                }

                $rows = (new CategoriesModel())
                    ->select('id, ct_name, ct_status')
                    ->orderBy('ct_name', 'ASC')
                    ->findAll();

                foreach ($rows as $row) {
                    $status = (int) ($row['ct_status'] ?? 0);
                    if ($status !== 1) {
                        continue;
                    }
                    $id = (int) ($row['id'] ?? 0);
                    $name = trim((string) ($row['ct_name'] ?? ''));
                    if ($id <= 0 || $name === '') {
                        continue;
                    }

                    $categories[] = [
                        'id' => $id,
                        'name' => $name,
                        'total' => $countMap[(string) $id] ?? 0,
                    ];
                }
            }
        } catch (\Throwable $e) {
            $categories = [];
            $priceMin = 0;
            $priceMax = 0;
        }

        return $this->response->setJSON([
            'status' => true,
            'filters' => [
                'categories' => $categories,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
            ],
        ]);
    }

    public function catalogProducts(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $limit = (int) ($this->request->getGet('limit') ?? 12);
        if ($limit <= 0 || $limit > 60) {
            $limit = 12;
        }

        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page <= 0) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $minPrice = $this->request->getGet('min_price');
        $maxPrice = $this->request->getGet('max_price');
        $sort = trim((string) ($this->request->getGet('sort') ?? 'newest'));
        $categoryParam = trim((string) ($this->request->getGet('categories') ?? ''));

        $categoryIds = [];
        if ($categoryParam !== '') {
            foreach (explode(',', $categoryParam) as $value) {
                $value = trim($value);
                if ($value !== '' && ctype_digit($value)) {
                    $categoryIds[] = (string) ((int) $value);
                }
            }
        }

        $products = [];
        $total = 0;

        try {
            $db = db_connect();
            if (!$db->tableExists('products')) {
                return $this->response->setJSON([
                    'status' => true,
                    'products' => [],
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit,
                ]);
            }

            $builder = $db->table('products');
            $builder->select('products.id, products.pr_name, products.pr_description, products.pr_image, products.pr_price, products.pr_stock, products.pr_category, products.pr_brand, products.pr_discount');
            $builder->where('products.pr_status', 1);

            if (!empty($categoryIds)) {
                $builder->whereIn('products.pr_category', $categoryIds);
            }

            if ($search !== '') {
                $builder->groupStart()
                    ->like('products.pr_name', $search)
                    ->orLike('products.pr_description', $search)
                    ->groupEnd();
            }

            if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) {
                $builder->where('products.pr_price >=', (float) $minPrice);
            }

            if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) {
                $builder->where('products.pr_price <=', (float) $maxPrice);
            }

            switch ($sort) {
                case 'price_asc':
                    $builder->orderBy('products.pr_price', 'ASC');
                    break;
                case 'price_desc':
                    $builder->orderBy('products.pr_price', 'DESC');
                    break;
                case 'name_asc':
                    $builder->orderBy('products.pr_name', 'ASC');
                    break;
                default:
                    $builder->orderBy('products.id', 'DESC');
                    break;
            }

            $total = (int) $builder->countAllResults(false);
            $rows = $builder->limit($limit, $offset)->get()->getResultArray();

            $categoryNames = [];
            if ($db->tableExists('categories')) {
                $cats = (new CategoriesModel())->select('id, ct_name')->findAll();
                foreach ($cats as $cat) {
                    $id = (int) ($cat['id'] ?? 0);
                    $name = trim((string) ($cat['ct_name'] ?? ''));
                    if ($id > 0 && $name !== '') {
                        $categoryNames[(string) $id] = $name;
                    }
                }
            }

            foreach ($rows as $row) {
                $name = trim((string) ($row['pr_name'] ?? 'Product'));
                $description = trim((string) ($row['pr_description'] ?? ''));
                $image = trim((string) ($row['pr_image'] ?? ''));
                $categoryValue = trim((string) ($row['pr_category'] ?? ''));
                $categoryName = $categoryNames[$categoryValue] ?? $categoryValue;

                $products[] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'name' => $name !== '' ? $name : 'Product',
                    'description' => $description,
                    'image_url' => $image !== '' ? base_url('uploads/products/' . $image) : '',
                    'price' => (float) ($row['pr_price'] ?? 0),
                    'stock' => (int) ($row['pr_stock'] ?? 0),
                    'category_id' => ctype_digit($categoryValue) ? (int) $categoryValue : 0,
                    'category' => $categoryName,
                    'discount' => (float) ($row['pr_discount'] ?? 0),
                ];
            }
        } catch (\Throwable $e) {
            $products = [];
            $total = 0;
        }

        return $this->response->setJSON([
            'status' => true,
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    public function productDetails(int $id = 0): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $id = (int) $id;
        if ($id <= 0) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => false,
                    'message' => 'Invalid product id.',
                ]);
        }

        try {
            $db = db_connect();
            if (!$db->tableExists('products')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Products table does not exist.',
                ]);
            }

            $product = (new ProductsModel())
                ->where('id', $id)
                ->where('pr_status', 1)
                ->first();

            if (!$product) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Product not found.',
                ]);
            }

            $categoryName = '';
            if ($db->tableExists('categories')) {
                $categoryValue = trim((string) ($product['pr_category'] ?? ''));
                if ($categoryValue !== '' && ctype_digit($categoryValue)) {
                    $categoryRow = (new CategoriesModel())->find((int) $categoryValue);
                    $categoryName = trim((string) ($categoryRow['ct_name'] ?? ''));
                }
            }

            $image = trim((string) ($product['pr_image'] ?? ''));
            $name = trim((string) ($product['pr_name'] ?? 'Product'));
            $description = trim((string) ($product['pr_description'] ?? ''));
            $categoryValue = trim((string) ($product['pr_category'] ?? ''));

            return $this->response->setJSON([
                'status' => true,
                'product' => [
                    'id' => (int) ($product['id'] ?? 0),
                    'name' => $name !== '' ? $name : 'Product',
                    'description' => $description,
                    'image_url' => $image !== '' ? base_url('uploads/products/' . $image) : '',
                    'price' => (float) ($product['pr_price'] ?? 0),
                    'stock' => (int) ($product['pr_stock'] ?? 0),
                    'category_id' => ctype_digit($categoryValue) ? (int) $categoryValue : 0,
                    'category' => $categoryName !== '' ? $categoryName : $categoryValue,
                    'discount' => (float) ($product['pr_discount'] ?? 0),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => false,
                    'message' => 'Unable to load product.',
                ]);
        }
    }

    public function relatedProducts(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $productId = (int) ($this->request->getGet('product_id') ?? 0);
        if ($productId <= 0) {
            return $this->response->setJSON([
                'status' => true,
                'products' => [],
            ]);
        }

        $limit = (int) ($this->request->getGet('limit') ?? 4);
        if ($limit <= 0 || $limit > 20) {
            $limit = 4;
        }

        $products = [];

        try {
            $db = db_connect();
            if (!$db->tableExists('products')) {
                return $this->response->setJSON([
                    'status' => true,
                    'products' => [],
                ]);
            }

            $current = (new ProductsModel())
                ->where('id', $productId)
                ->where('pr_status', 1)
                ->first();

            if (!$current) {
                return $this->response->setJSON([
                    'status' => true,
                    'products' => [],
                ]);
            }

            $categoryValue = trim((string) ($current['pr_category'] ?? ''));
            if ($categoryValue === '') {
                return $this->response->setJSON([
                    'status' => true,
                    'products' => [],
                ]);
            }

            $rows = (new ProductsModel())
                ->where('pr_status', 1)
                ->where('pr_category', $categoryValue)
                ->where('id !=', $productId)
                ->orderBy('id', 'DESC')
                ->findAll($limit);

            foreach ($rows as $row) {
                $name = trim((string) ($row['pr_name'] ?? 'Product'));
                $image = trim((string) ($row['pr_image'] ?? ''));

                $products[] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'name' => $name !== '' ? $name : 'Product',
                    'image_url' => $image !== '' ? base_url('uploads/products/' . $image) : '',
                    'price' => (float) ($row['pr_price'] ?? 0),
                ];
            }
        } catch (\Throwable $e) {
            $products = [];
        }

        return $this->response->setJSON([
            'status' => true,
            'products' => $products,
        ]);
    }

    public function checkout(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $paymentMethod = $this->normalizePaymentMethod($payload['payment_method'] ?? 'cod');
        if ($paymentMethod !== 'cod') {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Online payments must use the verified payment flow.',
            ]);
        }
        $paymentSettings = $this->getPaymentGatewaySettings();
        if ($paymentSettings['cod_enabled'] !== '1') {
            return $this->response->setStatusCode(503)->setJSON([
                'status' => false,
                'message' => 'Cash on delivery is not enabled.',
            ]);
        }
        $customer = $payload['customer'] ?? [];
        if (!is_array($customer)) {
            $customer = [];
        }
        $customerName = trim((string) ($customer['name'] ?? ''));
        $customerPhone = trim((string) ($customer['phone'] ?? ''));
        $customerAddress = trim((string) ($customer['address'] ?? ''));
        $customerNote = trim((string) ($customer['note'] ?? ''));
        if ($paymentMethod === 'cod') {
            if ($customerName === '' || $customerPhone === '' || $customerAddress === '') {
                return $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'status' => false,
                        'message' => 'Name, phone, and address are required for cash on delivery.',
                    ]);
            }
        }

        $cart = $this->buildCartSummary($payload);
        if (!$cart['ok']) {
            $response = $this->response->setStatusCode($cart['statusCode'])->setJSON([
                'status' => false,
                'message' => $cart['message'],
            ]);
            if (!empty($cart['extra'])) {
                $response = $this->response->setStatusCode($cart['statusCode'])->setJSON([
                    'status' => false,
                    'message' => $cart['message'],
                ] + $cart['extra']);
            }
            return $response;
        }

        $items = $cart['items'];
        $subtotal = $cart['subtotal'];
        $couponCode = $this->normalizeCouponCode((string) ($payload['coupon_code'] ?? ''));
        $couponData = null;
        $discount = 0.0;
        if ($couponCode !== '') {
            $couponCheck = $this->evaluateCoupon($couponCode, $subtotal);
            if ($couponCheck['ok']) {
                $discount = $couponCheck['discount'];
                $couponData = $couponCheck['coupon'];
                $this->incrementCouponUsage($couponData['id'] ?? 0);
            } else {
                return $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'status' => false,
                        'message' => $couponCheck['message'],
                    ]);
            }
        }

        $orderCode = 'ODR-' . date('ymd') . '-' . strtoupper(substr(sha1(uniqid('order_', true)), 0, 5));
        $userId = (int) (session()->get('user_id') ?? 0);

        $orderInserted = false;
        try {
            $db = db_connect();
            if ($db->tableExists('orders')) {
                $orderData = [
                    'order_code' => $orderCode,
                    'user_id' => $userId > 0 ? $userId : null,
                    'customer_name' => $customerName !== '' ? $customerName : null,
                    'customer_phone' => $customerPhone !== '' ? $customerPhone : null,
                    'customer_address' => $customerAddress !== '' ? $customerAddress : null,
                    'customer_note' => $customerNote !== '' ? $customerNote : null,
                    'payment_method' => $paymentMethod,
                    'status' => 1,
                    'subtotal' => round($subtotal, 2),
                    'discount' => round($discount, 2),
                    'total' => round(max($subtotal - $discount, 0), 2),
                    'coupon_code' => $couponCode !== '' ? $couponCode : null,
                    'items_json' => json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ];
                (new OrdersModel())->insert($orderData);
                $orderInserted = true;
            }
        } catch (\Throwable $e) {
            $orderInserted = false;
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => $orderInserted ? 'Order placed successfully.' : 'Checkout successful.',
            'order_code' => $orderCode,
            'date' => date('Y-m-d'),
            'subtotal' => round($subtotal, 2),
            'shipping' => 0,
            'discount' => round($discount, 2),
            'total' => round(max($subtotal - $discount, 0), 2),
            'items' => $items,
            'coupon' => $couponData,
            'payment_method' => $paymentMethod,
            'customer' => [
                'name' => $customerName,
                'phone' => $customerPhone,
                'address' => $customerAddress,
                'note' => $customerNote,
            ],
        ]);
    }

    public function paymentConfig(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $settings = $this->getPaymentGatewaySettings();
        $enabled = $settings['gateway_enabled'] === '1'
            && $settings['razorpay_key_id'] !== ''
            && $settings['razorpay_key_secret'] !== '';

        return $this->response->setJSON([
            'status' => true,
            'cod_enabled' => $settings['cod_enabled'] === '1',
            'online_enabled' => $enabled,
            'gateway' => $enabled ? 'razorpay' : '',
            'upi_enabled' => $enabled && $settings['upi_enabled'] === '1',
            'google_pay_enabled' => $enabled && $settings['upi_enabled'] === '1',
            'mode' => $settings['razorpay_mode'],
        ]);
    }

    public function createPaymentOrder(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $customer = is_array($payload['customer'] ?? null) ? $payload['customer'] : [];
        $customerName = trim((string) ($customer['name'] ?? ''));
        $customerPhone = trim((string) ($customer['phone'] ?? ''));
        $customerEmail = strtolower(trim((string) ($customer['email'] ?? '')));
        $customerAddress = trim((string) ($customer['address'] ?? ''));
        $customerNote = trim((string) ($customer['note'] ?? ''));

        if ($customerName === '' || $customerPhone === '' || $customerAddress === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Name, phone, and address are required.',
            ]);
        }
        if ($customerEmail !== '' && filter_var($customerEmail, FILTER_VALIDATE_EMAIL) === false) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Please enter a valid email address.',
            ]);
        }

        $settings = $this->getPaymentGatewaySettings();
        if ($settings['gateway_enabled'] !== '1' || $settings['upi_enabled'] !== '1') {
            return $this->response->setStatusCode(503)->setJSON([
                'status' => false,
                'message' => 'Online payments are not enabled.',
            ]);
        }

        $cart = $this->buildCartSummary($payload);
        if (!$cart['ok']) {
            return $this->response->setStatusCode($cart['statusCode'])->setJSON([
                'status' => false,
                'message' => $cart['message'],
            ] + ($cart['extra'] ?? []));
        }

        $couponCode = $this->normalizeCouponCode((string) ($payload['coupon_code'] ?? ''));
        $couponData = null;
        $discount = 0.0;
        if ($couponCode !== '') {
            $couponCheck = $this->evaluateCoupon($couponCode, (float) $cart['subtotal']);
            if (!$couponCheck['ok']) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => false,
                    'message' => $couponCheck['message'],
                ]);
            }
            $discount = (float) $couponCheck['discount'];
            $couponData = $couponCheck['coupon'];
        }

        $total = round(max((float) $cart['subtotal'] - $discount, 0), 2);
        if ($total < 1) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Online payment total must be at least INR 1.00.',
            ]);
        }

        $orderCode = 'ODR-' . date('ymd') . '-' . strtoupper(substr(sha1(uniqid('order_', true)), 0, 5));

        try {
            $gateway = new RazorpayGateway($settings['razorpay_key_id'], $settings['razorpay_key_secret']);
            $gatewayOrder = $gateway->createOrder(
                (int) round($total * 100),
                'INR',
                $orderCode,
                ['local_order_code' => $orderCode]
            );
        } catch (\Throwable $e) {
            log_message('error', 'Razorpay order creation failed: {message}', ['message' => $e->getMessage()]);
            return $this->response->setStatusCode(502)->setJSON([
                'status' => false,
                'message' => 'Unable to start online payment. ' . $e->getMessage(),
            ]);
        }

        $gatewayOrderId = trim((string) ($gatewayOrder['id'] ?? ''));
        if ($gatewayOrderId === '') {
            return $this->response->setStatusCode(502)->setJSON([
                'status' => false,
                'message' => 'Payment gateway did not return an order ID.',
            ]);
        }

        session()->set('razorpay_pending_' . $gatewayOrderId, [
            'expires_at' => time() + 1800,
            'gateway_order_id' => $gatewayOrderId,
            'order_code' => $orderCode,
            'user_id' => (int) (session()->get('user_id') ?? 0),
            'customer' => [
                'name' => $customerName,
                'phone' => $customerPhone,
                'email' => $customerEmail,
                'address' => $customerAddress,
                'note' => $customerNote,
            ],
            'items' => $cart['items'],
            'subtotal' => round((float) $cart['subtotal'], 2),
            'discount' => round($discount, 2),
            'total' => $total,
            'coupon_code' => $couponCode,
            'coupon_id' => (int) ($couponData['id'] ?? 0),
        ]);

        return $this->response->setJSON([
            'status' => true,
            'gateway' => 'razorpay',
            'key_id' => $settings['razorpay_key_id'],
            'order_id' => $gatewayOrderId,
            'amount' => (int) ($gatewayOrder['amount'] ?? round($total * 100)),
            'currency' => 'INR',
            'name' => $settings['checkout_name'],
            'description' => 'Payment for ' . $orderCode,
            'theme_color' => $settings['checkout_theme_color'],
            'prefill' => [
                'name' => $customerName,
                'contact' => $customerPhone,
                'email' => $customerEmail,
            ],
        ]);
    }

    public function verifyPayment(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $paymentId = trim((string) ($payload['razorpay_payment_id'] ?? ''));
        $gatewayOrderId = trim((string) ($payload['razorpay_order_id'] ?? ''));
        $signature = trim((string) ($payload['razorpay_signature'] ?? ''));
        if ($paymentId === '' || $gatewayOrderId === '' || $signature === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Incomplete payment verification data.',
            ]);
        }

        $existingPayment = null;
        try {
            $db = db_connect();
            if ($db->tableExists('payments')) {
                $existingPayment = (new PaymentsModel())->where('pm_gateway_ref', $paymentId)->first();
            }
        } catch (\Throwable $e) {
            $existingPayment = null;
        }
        if ($existingPayment) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Payment already verified.',
                'order_code' => (string) ($existingPayment['pm_order_code'] ?? ''),
                'payment_method' => 'upi',
            ]);
        }

        $pendingKey = 'razorpay_pending_' . $gatewayOrderId;
        $pending = session()->get($pendingKey);
        if (!is_array($pending) || (int) ($pending['expires_at'] ?? 0) < time()) {
            session()->remove($pendingKey);
            return $this->response->setStatusCode(410)->setJSON([
                'status' => false,
                'message' => 'Payment session expired. Please start checkout again.',
            ]);
        }

        $settings = $this->getPaymentGatewaySettings();
        try {
            $gateway = new RazorpayGateway($settings['razorpay_key_id'], $settings['razorpay_key_secret']);
            if (!$gateway->verifyPaymentSignature((string) $pending['gateway_order_id'], $paymentId, $signature)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => 'Payment signature verification failed.',
                ]);
            }
            $gatewayPayment = $gateway->fetchPayment($paymentId);
        } catch (\Throwable $e) {
            log_message('error', 'Razorpay verification failed: {message}', ['message' => $e->getMessage()]);
            return $this->response->setStatusCode(502)->setJSON([
                'status' => false,
                'message' => 'Unable to verify payment status.',
            ]);
        }

        $expectedAmount = (int) round(((float) ($pending['total'] ?? 0)) * 100);
        $paymentValid = (string) ($gatewayPayment['order_id'] ?? '') === (string) $pending['gateway_order_id']
            && (int) ($gatewayPayment['amount'] ?? 0) === $expectedAmount
            && strtoupper((string) ($gatewayPayment['currency'] ?? '')) === 'INR'
            && strtolower((string) ($gatewayPayment['status'] ?? '')) === 'captured';

        if (!$paymentValid) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => false,
                'message' => 'Payment is not captured or does not match this order.',
            ]);
        }

        $orderCode = (string) ($pending['order_code'] ?? '');
        $customer = is_array($pending['customer'] ?? null) ? $pending['customer'] : [];
        $items = is_array($pending['items'] ?? null) ? $pending['items'] : [];
        $method = strtolower((string) ($gatewayPayment['method'] ?? 'upi'));
        $db = db_connect();
        $db->transBegin();

        try {
            if (!$db->tableExists('orders')) {
                throw new \RuntimeException('Orders table does not exist.');
            }

            $orderModel = new OrdersModel();
            $existingOrder = $orderModel->where('order_code', $orderCode)->first();
            if (!$existingOrder) {
                $orderModel->insert([
                    'order_code' => $orderCode,
                    'user_id' => (int) ($pending['user_id'] ?? 0) > 0 ? (int) $pending['user_id'] : null,
                    'customer_name' => trim((string) ($customer['name'] ?? '')) ?: null,
                    'customer_phone' => trim((string) ($customer['phone'] ?? '')) ?: null,
                    'customer_address' => trim((string) ($customer['address'] ?? '')) ?: null,
                    'customer_note' => trim((string) ($customer['note'] ?? '')) ?: null,
                    'payment_method' => $method,
                    'status' => 'processing',
                    'subtotal' => round((float) ($pending['subtotal'] ?? 0), 2),
                    'discount' => round((float) ($pending['discount'] ?? 0), 2),
                    'total' => round((float) ($pending['total'] ?? 0), 2),
                    'coupon_code' => trim((string) ($pending['coupon_code'] ?? '')) ?: null,
                    'items_json' => json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ]);
            }

            if ($db->tableExists('payments')) {
                (new PaymentsModel())->insert([
                    'pm_transaction_code' => 'RZP-' . substr(preg_replace('/[^A-Za-z0-9]/', '', $paymentId), -30),
                    'pm_order_code' => $orderCode,
                    'pm_customer_name' => trim((string) ($customer['name'] ?? 'Customer')),
                    'pm_method' => strtoupper($method),
                    'pm_gateway_ref' => $paymentId,
                    'pm_amount' => number_format((float) ($pending['total'] ?? 0), 2, '.', ''),
                    'pm_paid_on' => date('Y-m-d'),
                    'pm_status' => 2,
                ]);
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Unable to save verified payment.');
            }
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Verified payment persistence failed: {message}', ['message' => $e->getMessage()]);
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Payment was verified but the order could not be saved. Contact support with payment ID ' . $paymentId . '.',
            ]);
        }

        $this->incrementCouponUsage((int) ($pending['coupon_id'] ?? 0));
        session()->remove($pendingKey);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Payment verified and order placed successfully.',
            'order_code' => $orderCode,
            'date' => date('Y-m-d'),
            'subtotal' => round((float) ($pending['subtotal'] ?? 0), 2),
            'shipping' => 0,
            'discount' => round((float) ($pending['discount'] ?? 0), 2),
            'total' => round((float) ($pending['total'] ?? 0), 2),
            'items' => $items,
            'payment_method' => $method,
            'gateway_payment_id' => $paymentId,
            'customer' => $customer,
        ]);
    }

    public function orders(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        if ($userId <= 0) {
            return $this->response->setJSON([
                'status' => true,
                'orders' => [],
            ]);
        }

        $orders = [];
        try {
            $db = db_connect();
            if ($db->tableExists('orders')) {
                $deliveryByOrder = [];
                if ($db->tableExists('deliveries')) {
                    foreach ($db->table('deliveries')->get()->getResultArray() as $delivery) {
                        $deliveryByOrder[(string) ($delivery['dl_order_code'] ?? '')] = $delivery;
                    }
                }

                $paymentByOrder = [];
                if ($db->tableExists('payments')) {
                    foreach ($db->table('payments')->get()->getResultArray() as $payment) {
                        $paymentByOrder[(string) ($payment['pm_order_code'] ?? '')] = $payment;
                    }
                }

                $rows = (new OrdersModel())
                    ->where('user_id', $userId)
                    ->orderBy('id', 'DESC')
                    ->findAll(50);

                foreach ($rows as $row) {
                    $orderCode = (string) ($row['order_code'] ?? '');
                    $itemsJson = (string) ($row['items_json'] ?? '[]');
                    $items = json_decode($itemsJson, true);
                    if (!is_array($items)) {
                        $items = [];
                    }
                    $statusRaw = $row['status'] ?? 'processing';
                    $statusText = $this->orderStatusText($statusRaw);
                    $delivery = $deliveryByOrder[$orderCode] ?? [];
                    $payment = $paymentByOrder[$orderCode] ?? [];
                    $deliveryStatus = (int) ($delivery['dl_status'] ?? 0);
                    $paymentStatus = (int) ($payment['pm_status'] ?? 0);
                    $orders[] = [
                        'id' => $orderCode,
                        'database_id' => (int) ($row['id'] ?? 0),
                        'date' => substr((string) ($row['created_at'] ?? ''), 0, 10),
                        'created_at' => (string) ($row['created_at'] ?? ''),
                        'updated_at' => (string) ($row['updated_at'] ?? ''),
                        'status' => $statusText,
                        'items' => $items,
                        'subtotal' => (float) ($row['subtotal'] ?? 0),
                        'discount' => (float) ($row['discount'] ?? 0),
                        'total' => (float) ($row['total'] ?? 0),
                        'payment_method' => $this->normalizePaymentMethod($row['payment_method'] ?? 'cod'),
                        'coupon_code' => (string) ($row['coupon_code'] ?? ''),
                        'customer' => [
                            'name' => (string) ($row['customer_name'] ?? ''),
                            'phone' => (string) ($row['customer_phone'] ?? ''),
                            'address' => (string) ($row['customer_address'] ?? ''),
                            'note' => (string) ($row['customer_note'] ?? ''),
                        ],
                        'delivery' => [
                            'available' => $delivery !== [],
                            'shipment_code' => (string) ($delivery['dl_shipment_code'] ?? ''),
                            'hub' => (string) ($delivery['dl_hub'] ?? ''),
                            'rider_name' => (string) ($delivery['dl_rider_name'] ?? ''),
                            'eta_date' => (string) ($delivery['dl_eta_date'] ?? ''),
                            'status' => $this->deliveryStatusText($deliveryStatus),
                        ],
                        'payment' => [
                            'available' => $payment !== [],
                            'transaction_code' => (string) ($payment['pm_transaction_code'] ?? ''),
                            'gateway_ref' => (string) ($payment['pm_gateway_ref'] ?? ''),
                            'status' => $this->paymentStatusText($paymentStatus),
                            'paid_on' => (string) ($payment['pm_paid_on'] ?? ''),
                        ],
                    ];
                }
            }
        } catch (\Throwable $e) {
            $orders = [];
        }

        return $this->response->setJSON([
            'status' => true,
            'orders' => $orders,
        ]);
    }

    public function productReviews(int $productId = 0): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $productId = max(0, $productId);
        if ($productId <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Invalid product.',
            ]);
        }

        $reviews = [];
        $summary = [
            'average' => 0,
            'count' => 0,
            'distribution' => ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0],
        ];
        $userId = (int) (session()->get('user_id') ?? 0);
        $loggedIn = session()->get('logged_in') === true && $userId > 0;
        $myReview = null;
        $eligible = false;

        try {
            $db = db_connect();
            if ($db->tableExists('product_reviews')) {
                $rows = $db->table('product_reviews pr')
                    ->select('pr.id, pr.user_id, pr.rating, pr.review_text, pr.is_verified_purchase, pr.created_at, pr.updated_at, u.us_name')
                    ->join('users u', 'u.id = pr.user_id', 'left')
                    ->where('pr.product_id', $productId)
                    ->where('pr.status', 1)
                    ->orderBy('pr.updated_at', 'DESC')
                    ->get()
                    ->getResultArray();

                $ratingTotal = 0;
                foreach ($rows as $row) {
                    $rating = max(1, min(5, (int) ($row['rating'] ?? 0)));
                    $ratingTotal += $rating;
                    $summary['distribution'][(string) $rating]++;
                    $review = [
                        'id' => (int) ($row['id'] ?? 0),
                        'rating' => $rating,
                        'review_text' => trim((string) ($row['review_text'] ?? '')),
                        'reviewer_name' => trim((string) ($row['us_name'] ?? 'Customer')) ?: 'Customer',
                        'verified_purchase' => (int) ($row['is_verified_purchase'] ?? 0) === 1,
                        'created_at' => (string) ($row['created_at'] ?? ''),
                        'updated_at' => (string) ($row['updated_at'] ?? ''),
                    ];
                    $reviews[] = $review;

                    if ($loggedIn && (int) ($row['user_id'] ?? 0) === $userId) {
                        $myReview = $review;
                    }
                }

                $summary['count'] = count($reviews);
                $summary['average'] = $summary['count'] > 0
                    ? round($ratingTotal / $summary['count'], 1)
                    : 0;
            }

            if ($loggedIn) {
                $eligible = $this->hasDeliveredProductPurchase($userId, $productId);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Unable to load product reviews: {message}', ['message' => $e->getMessage()]);
        }

        return $this->response->setJSON([
            'status' => true,
            'summary' => $summary,
            'reviews' => $reviews,
            'viewer' => [
                'logged_in' => $loggedIn,
                'eligible' => $eligible,
                'review' => $myReview,
            ],
        ]);
    }

    public function saveProductReview(int $productId = 0): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $userId = (int) (session()->get('user_id') ?? 0);
        $isCustomer = session()->get('logged_in') === true
            && (int) (session()->get('us_role_id') ?? 0) === self::ROLE_CUSTOMER;
        if (!$isCustomer || $userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => false,
                'message' => 'Please sign in with a customer account to review this product.',
            ]);
        }

        $productId = max(0, $productId);
        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }
        $rating = (int) ($payload['rating'] ?? 0);
        $reviewText = trim((string) ($payload['review_text'] ?? ''));

        if ($productId <= 0 || $rating < 1 || $rating > 5) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Choose a star rating from 1 to 5.',
            ]);
        }
        if (mb_strlen($reviewText) > 1000) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Review must be 1000 characters or fewer.',
            ]);
        }

        try {
            $db = db_connect();
            if (!$db->tableExists('products') || !(new ProductsModel())->find($productId)) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => false,
                    'message' => 'Product not found.',
                ]);
            }
            if (!$db->tableExists('product_reviews')) {
                return $this->response->setStatusCode(503)->setJSON([
                    'status' => false,
                    'message' => 'Reviews are not available until the database migration is run.',
                ]);
            }
            if (!$this->hasDeliveredProductPurchase($userId, $productId)) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => false,
                    'message' => 'You can review this product after a delivered purchase.',
                ]);
            }

            $model = new ProductReviewsModel();
            $existing = $model
                ->where('product_id', $productId)
                ->where('user_id', $userId)
                ->first();
            $data = [
                'product_id' => $productId,
                'user_id' => $userId,
                'rating' => $rating,
                'review_text' => $reviewText !== '' ? $reviewText : null,
                'is_verified_purchase' => 1,
                'status' => 1,
            ];

            if ($existing) {
                $model->update((int) $existing['id'], $data);
                $message = 'Your review was updated.';
            } else {
                $model->insert($data);
                $message = 'Your review was submitted.';
            }
        } catch (\Throwable $e) {
            log_message('error', 'Unable to save product review: {message}', ['message' => $e->getMessage()]);
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Unable to save your review right now.',
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => $message,
        ]);
    }

    private function hasDeliveredProductPurchase(int $userId, int $productId): bool
    {
        if ($userId <= 0 || $productId <= 0) {
            return false;
        }

        $db = db_connect();
        if (!$db->tableExists('orders')) {
            return false;
        }

        $orders = (new OrdersModel())
            ->select('status, items_json')
            ->where('user_id', $userId)
            ->findAll();

        foreach ($orders as $order) {
            if ($this->orderStatusText($order['status'] ?? '') !== 'delivered') {
                continue;
            }

            $items = json_decode((string) ($order['items_json'] ?? '[]'), true);
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (is_array($item) && (int) ($item['id'] ?? 0) === $productId) {
                    return true;
                }
            }
        }

        return false;
    }

    private function orderStatusText($value): string
    {
        if (is_numeric($value)) {
            $status = (int) $value;
            return match ($status) {
                2 => 'delivered',
                3 => 'cancelled',
                default => 'processing',
            };
        }

        $text = strtolower(trim((string) $value));
        return in_array($text, ['processing', 'delivered', 'cancelled'], true)
            ? $text
            : 'processing';
    }

    private function normalizePaymentMethod($value): string
    {
        if (is_numeric($value)) {
            $code = (int) $value;
            if ($code === 0 || $code === 1) {
                return 'cod';
            }
        }

        $text = strtolower(trim((string) $value));
        return $text !== '' ? $text : 'cod';
    }

    private function deliveryStatusText(int $value): string
    {
        return match ($value) {
            2 => 'out_for_delivery',
            3 => 'delivered',
            4 => 'delayed',
            default => $value > 0 ? 'processing' : 'not_assigned',
        };
    }

    private function paymentStatusText(int $value): string
    {
        return match ($value) {
            2 => 'paid',
            3 => 'failed',
            4 => 'refunded',
            default => $value > 0 ? 'pending' : 'not_recorded',
        };
    }

    public function validateCoupon(): ResponseInterface
    {
        $unauthorized = $this->guardApiToken();
        if ($unauthorized !== null) {
            return $unauthorized;
        }

        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $cart = $this->buildCartSummary($payload);
        if (!$cart['ok']) {
            return $this->response
                ->setStatusCode($cart['statusCode'])
                ->setJSON([
                    'status' => false,
                    'message' => $cart['message'],
                ]);
        }

        $couponCode = $this->normalizeCouponCode((string) ($payload['coupon_code'] ?? ''));
        if ($couponCode === '') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'message' => 'Coupon code is required.',
                ]);
        }

        $couponCheck = $this->evaluateCoupon($couponCode, $cart['subtotal']);
        if (!$couponCheck['ok']) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => false,
                    'message' => $couponCheck['message'],
                ]);
        }

        $discount = $couponCheck['discount'];

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Coupon applied successfully.',
            'coupon' => $couponCheck['coupon'],
            'subtotal' => round($cart['subtotal'], 2),
            'discount' => round($discount, 2),
            'total' => round(max($cart['subtotal'] - $discount, 0), 2),
        ]);
    }

    private function guardApiToken(): ?ResponseInterface
    {
        $sessionToken = trim((string) session()->get('rest_api_token'));
        if ($sessionToken === '') {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['status' => false, 'message' => 'API token missing in session.']);
        }

        $provided = trim((string) ($this->request->getHeaderLine('X-Api-Token') ?: ''));
        if ($provided === '') {
            $authHeader = trim((string) $this->request->getHeaderLine('Authorization'));
            if (stripos($authHeader, 'Bearer ') === 0) {
                $provided = trim(substr($authHeader, 7));
            }
        }

        if ($provided === '' || !hash_equals($sessionToken, $provided)) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['status' => false, 'message' => 'Invalid API token.']);
        }

        return null;
    }

    private function buildCartSummary(array $payload): array
    {
        $itemsInput = $payload['items'] ?? [];
        if (!is_array($itemsInput) || empty($itemsInput)) {
            return [
                'ok' => false,
                'statusCode' => 422,
                'message' => 'Cart items are required.',
            ];
        }

        $ids = [];
        $qtyMap = [];
        foreach ($itemsInput as $row) {
            if (!is_array($row)) {
                continue;
            }
            $id = (int) ($row['id'] ?? 0);
            $qty = (int) ($row['qty'] ?? 0);
            if ($id <= 0 || $qty <= 0) {
                continue;
            }
            $ids[] = $id;
            $qtyMap[(string) $id] = $qty;
        }

        $ids = array_values(array_unique($ids));
        if (empty($ids)) {
            return [
                'ok' => false,
                'statusCode' => 422,
                'message' => 'Invalid cart items.',
            ];
        }

        $db = db_connect();
        if (!$db->tableExists('products')) {
            return [
                'ok' => false,
                'statusCode' => 500,
                'message' => 'Products table does not exist.',
            ];
        }

        $rows = (new ProductsModel())
            ->where('pr_status', 1)
            ->whereIn('id', $ids)
            ->findAll();

        if (empty($rows)) {
            return [
                'ok' => false,
                'statusCode' => 404,
                'message' => 'Products not found.',
            ];
        }

        $items = [];
        $subtotal = 0.0;
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $qty = (int) ($qtyMap[(string) $id] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            $stock = (int) ($row['pr_stock'] ?? 0);
            if ($stock > 0 && $qty > $stock) {
                return [
                    'ok' => false,
                    'statusCode' => 422,
                    'message' => 'Requested quantity exceeds available stock.',
                    'extra' => ['product_id' => $id],
                ];
            }

            $name = trim((string) ($row['pr_name'] ?? 'Product'));
            $image = trim((string) ($row['pr_image'] ?? ''));
            $price = (float) ($row['pr_price'] ?? 0);
            $discount = (float) ($row['pr_discount'] ?? 0);
            if ($discount < 0) {
                $discount = 0;
            }
            if ($discount > 100) {
                $discount = 100;
            }
            $unitPrice = $discount > 0 ? $price - ($price * ($discount / 100)) : $price;
            $lineTotal = $unitPrice * $qty;
            $subtotal += $lineTotal;

            $items[] = [
                'id' => $id,
                'name' => $name !== '' ? $name : 'Product',
                'qty' => $qty,
                'price' => round($unitPrice, 2),
                'image_url' => $image !== '' ? base_url('uploads/products/' . $image) : '',
            ];
        }

        if (empty($items)) {
            return [
                'ok' => false,
                'statusCode' => 422,
                'message' => 'Unable to process cart items.',
            ];
        }

        return [
            'ok' => true,
            'items' => $items,
            'subtotal' => $subtotal,
        ];
    }

    private function normalizeCouponCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    private function evaluateCoupon(string $code, float $subtotal): array
    {
        $db = db_connect();
        if (!$db->tableExists('coupons')) {
            return [
                'ok' => false,
                'message' => 'Coupons table does not exist.',
            ];
        }

        $coupon = (new CouponsModel())
            ->where('cp_code', $code)
            ->first();

        if (!$coupon) {
            return [
                'ok' => false,
                'message' => 'Coupon not found.',
            ];
        }

        $status = (int) ($coupon['cp_status'] ?? 1);
        if ($status !== 1) {
            return [
                'ok' => false,
                'message' => 'Coupon is not active.',
            ];
        }

        $today = date('Y-m-d');
        $startDate = trim((string) ($coupon['cp_start_date'] ?? ''));
        $endDate = trim((string) ($coupon['cp_end_date'] ?? ''));
        if ($startDate !== '' && $today < $startDate) {
            return [
                'ok' => false,
                'message' => 'Coupon is not active yet.',
            ];
        }
        if ($endDate !== '' && $today > $endDate) {
            return [
                'ok' => false,
                'message' => 'Coupon has expired.',
            ];
        }

        $usageLimit = $coupon['cp_usage_limit'] ?? null;
        $usedCount = (int) ($coupon['cp_used_count'] ?? 0);
        if ($usageLimit !== null && (int) $usageLimit > 0 && $usedCount >= (int) $usageLimit) {
            return [
                'ok' => false,
                'message' => 'Coupon usage limit reached.',
            ];
        }

        $minOrder = (float) ($coupon['cp_min_order'] ?? 0);
        if ($minOrder > 0 && $subtotal < $minOrder) {
            return [
                'ok' => false,
                'message' => 'Minimum order of ' . number_format($minOrder, 2, '.', '') . ' is required for this coupon.',
            ];
        }

        $type = (int) ($coupon['cp_type'] ?? 1);
        $value = (float) ($coupon['cp_value'] ?? 0);
        $maxDiscount = isset($coupon['cp_max_discount']) ? (float) ($coupon['cp_max_discount'] ?? 0) : 0;
        $discount = 0.0;

        if ($type === 2) {
            $discount = $value;
        } else {
            $discount = $subtotal * ($value / 100);
            if ($maxDiscount > 0) {
                $discount = min($discount, $maxDiscount);
            }
        }

        if ($discount <= 0) {
            return [
                'ok' => false,
                'message' => 'Coupon does not apply to this order.',
            ];
        }

        $discount = min($discount, $subtotal);

        return [
            'ok' => true,
            'coupon' => [
                'id' => (int) ($coupon['id'] ?? 0),
                'code' => (string) ($coupon['cp_code'] ?? ''),
                'title' => (string) ($coupon['cp_title'] ?? ''),
                'type' => $type,
                'value' => round($value, 2),
                'min_order' => round($minOrder, 2),
                'max_discount' => $maxDiscount > 0 ? round($maxDiscount, 2) : null,
            ],
            'discount' => round($discount, 2),
        ];
    }

    private function incrementCouponUsage(int $couponId): void
    {
        if ($couponId <= 0) {
            return;
        }

        try {
            $db = db_connect();
            if (!$db->tableExists('coupons')) {
                return;
            }
            (new CouponsModel())->where('id', $couponId)->set('cp_used_count', 'cp_used_count + 1', false)->update();
        } catch (\Throwable $e) {
            // ignore increment errors
        }
    }

    private function getPaymentGatewaySettings(): array
    {
        $defaults = [
            'gateway_enabled' => '0',
            'cod_enabled' => '1',
            'upi_enabled' => '1',
            'razorpay_mode' => 'test',
            'razorpay_key_id' => '',
            'razorpay_key_secret' => '',
            'checkout_name' => 'Ebolt',
            'checkout_theme_color' => '#0f6cad',
        ];

        try {
            $db = db_connect();
            if ($db->tableExists('settings') || $db->tableExists('system_settings')) {
                $stored = (new SettingsModel())->getGroupSettings('payment');
                if (is_array($stored)) {
                    $defaults = array_merge($defaults, $stored);
                }
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        $envKeyId = trim((string) env('razorpay.keyId', ''));
        $envKeySecret = trim((string) env('razorpay.keySecret', ''));
        if ($envKeyId !== '') {
            $defaults['razorpay_key_id'] = $envKeyId;
        }
        if ($envKeySecret !== '') {
            $defaults['razorpay_key_secret'] = $envKeySecret;
        }

        return array_map(static fn($value): string => trim((string) $value), $defaults);
    }

    private function getDefaultUserDashboardTemplate(): array
    {
        return [
            'design' => [
                'page_bg' => '#f4f7fb',
                'surface' => '#ffffff',
                'text_color' => '#1b2432',
                'muted_text' => '#5f6f86',
                'hero_gradient_start' => '#79a9d1',
                'hero_gradient_end' => '#d4e9f7',
                'accent' => '#f15a3b',
                'font_family' => '"Nunito Sans", sans-serif',
                'content_max_width' => '100%',
                'radius_xl' => '34px',
                'radius_lg' => '22px',
                'radius_md' => '16px',
                'shadow_soft' => '0 14px 34px rgba(25, 41, 72, 0.09)',
                'hero_min_height' => '330px',
                'product_columns' => '4',
                'custom_css' => '',
            ],
            'layers' => [
                'header' => true,
                'hero' => true,
                'section' => true,
                'business' => true,
                'business_alt' => true,
                'testimonials' => true,
                'newsletter' => true,
                'footer' => true,
            ],
            'nav' => [
                'home' => 'Home',
                'courses' => 'Courses',
                'contacts' => 'Contacts',
                'about' => 'About',
                'register' => 'Register',
                'login' => 'Login',
            ],
            'hero' => [
                'title' => 'Store for children',
                'subtitle' => 'shopping with joy',
                'search_placeholder' => 'Search',
                'background_image' => '',
            ],
            'sections' => [
                'categories_title' => 'Select Categories',
                'categories_link' => 'Show All',
                'categories' => [
                    'Plumbing & Repair',
                    'Art and Creativity',
                    'Hobby & Sport',
                    'Games & Puzzles',
                    'Clothes & Footwear',
                    'Health & Safety',
                    'Feeding & Nutrition',
                    'Food & Drinks',
                    'Boutiques & Shops',
                    'Goods for Mothers',
                ],
                'popular_title' => 'Most Popular',
                'popular_link' => 'Show All',
                'popular_description' => 'High quality kids product with safe materials and joyful design for daily use.',
            ],
            'business' => [
                'title' => 'Elevating Business Performance Through Strategic Solutions',
                'description' => 'Increasingly many people use digital media for learning and continuing education. E-learning content formats that are individually modified to meet each learner needs are fundamental for successful outcomes.',
                'image_url' => '',
                'services_title' => 'Featured Services',
                'services' => [
                    [
                        'title' => 'Talent Management Strategy',
                        'description' => 'Build stronger teams with focused hiring, role mapping, and continuous capability development.',
                    ],
                    [
                        'title' => 'Innovation & Digital Transformation',
                        'description' => 'Modernize operations through practical digital workflows, automation, and customer-first experiences.',
                    ],
                    [
                        'title' => 'Market Expansion Advisory',
                        'description' => 'Scale into new regions with structured research, channel planning, and measurable growth strategy.',
                    ],
                ],
            ],
            'business_alt' => [
                'title' => 'Driving Better Outcomes With Practical Business Execution',
                'description' => 'From planning to delivery, structured execution and clear communication help teams move faster, reduce risk, and create long-term business value.',
                'image_url' => '',
            ],
            'about' => [
                'title' => 'About Our Store',
                'description' => 'We provide trusted products for children and families, including toys, clothing, nutrition, and daily essentials. The dashboard helps users explore featured banners, browse categories, and discover popular products quickly on mobile and desktop.',
            ],
            'about_page' => [
                'hero_title' => 'Built for joyful childhoods.',
                'hero_description' => 'We curate thoughtful products and experiences for families who value quality, safety, and delight. From everyday essentials to playful discoveries, our mission is to make growing up feel magical.',
                'tags' => ['Safety-first materials', 'Family-led sourcing', 'Crafted for comfort'],
                'promise_title' => 'Our Promise',
                'promise_text_1' => 'We partner with trusted makers and verify every item for durability, comfort, and responsible sourcing.',
                'promise_text_2' => 'Every purchase supports community initiatives for early learning and childcare.',
                'stats' => [
                    ['value' => '12k+', 'label' => 'Families served'],
                    ['value' => '350+', 'label' => 'Trusted brands'],
                    ['value' => '98%', 'label' => 'Happy parent reviews'],
                ],
                'story_title' => 'Our Story',
                'story_description' => 'Started by parents who wanted better options for their children, we have grown into a trusted marketplace with a focus on long-lasting essentials and playful learning.',
                'work_title' => 'How We Work',
                'work_description' => 'We review supplier practices, test product durability, and prioritize makers who respect families and the environment. We obsess over details so you can shop with confidence.',
                'values' => [
                    ['title' => 'Care Driven', 'description' => 'Every product is chosen with real family needs in mind.'],
                    ['title' => 'Responsible', 'description' => 'We prioritize ethical production and long-term sustainability.'],
                    ['title' => 'Delightful', 'description' => 'Designs that spark joy and encourage learning every day.'],
                ],
            ],
            'contact_page' => [
                'hero_title' => 'Let us talk.',
                'hero_description' => 'Questions about products, sizing, delivery, or partnerships? Reach out and our support team will respond within 24 hours on business days.',
                'email' => 'support@child.com',
                'phone' => '+91 90000 00000',
                'address' => '9/14 Lake View Road, Chennai',
                'form_title' => 'Send a message',
                'name_label' => 'Full Name',
                'email_label' => 'Email',
                'phone_label' => 'Phone',
                'message_label' => 'Message',
                'phone_placeholder' => 'Optional',
                'submit_text' => 'Send Message',
                'reset_text' => 'Reset',
                'success_message' => 'Thanks! We received your message and will respond shortly.',
            ],
            'testimonials' => [
                'title' => 'Loved by families',
                'subtitle' => 'Real feedback from customers who shop with us.',
                'items' => [
                    [
                        'quote' => 'The product quality was excellent and delivery was quicker than expected.',
                        'name' => 'Priya Sharma',
                        'role' => 'Verified customer',
                    ],
                    [
                        'quote' => 'It is easy to find safe, useful products for every age group.',
                        'name' => 'Rahul Mehta',
                        'role' => 'Parent',
                    ],
                    [
                        'quote' => 'Helpful support, clear product details, and a smooth checkout experience.',
                        'name' => 'Ananya Patel',
                        'role' => 'Returning customer',
                    ],
                ],
            ],
            'newsletter' => [
                'title' => 'Get offers and new arrivals',
                'description' => 'Join our newsletter for product updates, family shopping tips, and exclusive deals.',
                'placeholder' => 'Enter your email address',
                'button_text' => 'Subscribe',
                'success_message' => 'Email saved on this device.',
            ],
            'footer' => [
                'column1_title' => 'Customer Care',
                'column1_links' => ['Help Center', 'Track Order', 'Return Policy'],
                'column2_title' => 'Company',
                'column2_links' => ['About Us', 'Privacy Policy', 'Terms & Conditions'],
                'column3_title' => 'Contact',
                'email' => 'support@child.com',
                'phone' => '+91 90000 00000',
                'copyright' => 'Copyright {year} child.com. All rights reserved.',
            ],
        ];
    }
}
