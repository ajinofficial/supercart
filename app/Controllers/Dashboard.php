<?php

namespace App\Controllers;

use App\Models\BannersModel;
use App\Models\BrandsModel;
use App\Models\CategoriesModel;
use App\Models\CustomersModel;
use App\Models\DashboardTemplateModel;
use App\Models\OrdersModel;
use App\Models\ProductsModel;
use App\Models\SettingsModel;

class Dashboard extends BaseController
{
    public function dashboard()
    {
        return view('admin/dashboard', $this->getAdminDashboardData());
    }

    public function userDashboard()
    {
        $banners = [];
        $branding = [
            'website_name' => 'child.com',
            'logo_url' => '',
        ];
        $template = $this->getDefaultUserDashboardTemplate();
        $currencySymbol = $this->getCurrencySymbol();
        $currencyCode = $this->getCurrencyCode();
        $apiToken = trim((string) session()->get('rest_api_token'));
        if ($apiToken === '') {
            try {
                $apiToken = bin2hex(random_bytes(24));
            } catch (\Throwable $e) {
                $apiToken = sha1(uniqid('rest_api_', true));
            }
            session()->set('rest_api_token', $apiToken);
        }
        $db = db_connect();

        try {
            if ($db->tableExists('banners')) {
                $banners = (new BannersModel())
                    ->where('bn_status', 1)
                    ->orderBy('id', 'DESC')
                    ->findAll(6);
            }

            if ($db->tableExists('settings') || $db->tableExists('system_settings')) {
                $general = (new SettingsModel())->getGroupSettings('general');
                $name = trim((string) ($general['website_name'] ?? ''));
                $logoFile = trim((string) ($general['website_logo'] ?? ''));
                if ($name !== '') {
                    $branding['website_name'] = $name;
                }

                if ($logoFile !== '') {
                    $branding['logo_url'] = base_url('uploads/settings/' . $logoFile);
                }
            }
        } catch (\Throwable $e) {
            $banners = [];
        }

        $storedTemplate = $this->getStoredUserDashboardTemplate();
        if (!empty($storedTemplate)) {
            $template = array_replace_recursive($template, $storedTemplate);
        }

        return view('user/dashboard', [
            'banners' => $banners,
            'userName' => (string) (session()->get('us_name') ?: 'User'),
            'branding' => $branding,
            'dashboardTemplate' => $template,
            'restApiToken' => $apiToken,
            'currencySymbol' => $currencySymbol,
            'currencyCode' => $currencyCode,
        ]);
    }

    public function userDashboardTemplateApi()
    {
        $template = $this->getDefaultUserDashboardTemplate();
        $stored = $this->getStoredUserDashboardTemplate();
        if (!empty($stored)) {
            $template = array_replace_recursive($template, $stored);
        }

        return $this->response->setJSON([
            'status' => true,
            'template' => $template,
        ]);
    }

    public function userDashboardProductsApi()
    {
        $limit = (int) ($this->request->getGet('limit') ?? 8);
        if ($limit <= 0 || $limit > 24) {
            $limit = 8;
        }

        $products = [];

        try {
            $db = db_connect();
            if ($db->tableExists('products')) {
                $rows = (new ProductsModel())
                    ->where('pr_status', 1)
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

    public function dashboardTemplateList()
    {
        $templates = [];

        try {
            $db = db_connect();
            if ($db->tableExists('dashboard_templates')) {
                $templates = (new DashboardTemplateModel())->orderBy('id', 'DESC')->findAll();
            }
        } catch (\Throwable $e) {
            $templates = [];
        }

        return view('admin/dashboard_templates', [
            'page' => 'dashboard_template',
            'templates' => $templates,
        ]);
    }

    public function dashboardTemplateView(int $id = 0)
    {
        $template = $this->getDefaultUserDashboardTemplate();
        $selectedTemplate = null;

        try {
            $db = db_connect();
            if ($db->tableExists('dashboard_templates')) {
                $templateModel = new DashboardTemplateModel();

                if ($id > 0) {
                    $selectedTemplate = $templateModel->find($id);
                }
            } elseif ($db->tableExists('settings') || $db->tableExists('system_settings')) {
                $stored = (new SettingsModel())->getGroupSettings('user_dashboard_template');
                $selectedTemplate = [
                    'id' => 0,
                    'dt_name' => 'Default Template',
                    'dt_is_active' => 1,
                    'dt_json' => json_encode($stored, JSON_UNESCAPED_UNICODE),
                ];
            }

            if ($selectedTemplate) {
                $stored = json_decode((string) ($selectedTemplate['dt_json'] ?? ''), true);
                if (is_array($stored)) {
                    $template = array_replace_recursive($template, $stored);
                }
            } else {
                $stored = $this->getStoredUserDashboardTemplate();
                if (!empty($stored)) {
                    $template = array_replace_recursive($template, $stored);
                }
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        return view('admin/dashboard_template', [
            'page' => 'dashboard_template',
            'selectedTemplate' => $selectedTemplate,
            'templateJson' => json_encode($template, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function saveDashboardTemplate()
    {
        $id = (int) ($this->request->getPost('id') ?? 0);
        $name = trim((string) $this->request->getPost('template_name'));
        $isActive = (int) ($this->request->getPost('is_active') ?? 0) === 1 ? 1 : 0;
        $raw = trim((string) $this->request->getPost('template_json'));
        if ($name === '') {
            return $this->response->setJSON([
                'status' => false,
                'errors' => ['template_name' => 'Template name is required.'],
            ]);
        }

        if ($raw === '') {
            return $this->response->setJSON([
                'status' => false,
                'errors' => ['template_json' => 'Template JSON is required.'],
            ]);
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $this->response->setJSON([
                'status' => false,
                'errors' => ['template_json' => 'Invalid JSON format.'],
            ]);
        }

        $normalized = array_replace_recursive($this->getDefaultUserDashboardTemplate(), $decoded);

        try {
            $db = db_connect();
            if (!$db->tableExists('dashboard_templates')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Dashboard templates table does not exist. Run migrations first.',
                ]);
            }

            $templateModel = new DashboardTemplateModel();
            $encoded = json_encode($normalized, JSON_UNESCAPED_UNICODE);
            if ($encoded === false) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to encode dashboard template.',
                ]);
            }

            $slug = $this->makeDashboardTemplateSlug($name, $id);
            $payload = [
                'dt_name' => $name,
                'dt_slug' => $slug,
                'dt_json' => $encoded,
                'dt_is_active' => $isActive,
                'dt_status' => 1,
            ];

            if ($id > 0) {
                $existing = $templateModel->find($id);
                if (!$existing) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Dashboard template not found.',
                    ]);
                }
                $ok = $templateModel->update($id, $payload);
                $savedId = $id;
            } else {
                $ok = $templateModel->insert($payload);
                $savedId = (int) $templateModel->getInsertID();
            }

            if ($ok && $isActive === 1) {
                $db->table('dashboard_templates')
                    ->where('id !=', $savedId)
                    ->update(['dt_is_active' => 0]);
            }
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }

        if (!$ok) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Unable to save dashboard template.',
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Dashboard template saved successfully.',
            'id' => $savedId,
            'template' => $normalized,
        ]);
    }

    public function uploadDashboardTemplateImage()
    {
        $image = $this->request->getFile('template_image');

        if (!$image || $image->getError() === UPLOAD_ERR_NO_FILE) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Please select an image.',
            ]);
        }

        if (!$image->isValid()) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'The selected image is not valid.',
            ]);
        }

        $allowedMime = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array(strtolower((string) $image->getMimeType()), $allowedMime, true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Only JPG, PNG, WEBP, and GIF images are allowed.',
            ]);
        }

        if ($image->getSizeByUnit('kb') > 3072) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Image size must be 3 MB or less.',
            ]);
        }

        $uploadDir = FCPATH . 'uploads/dashboard-templates';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Unable to create the dashboard template upload directory.',
            ]);
        }

        try {
            $fileName = $image->getRandomName();
            $image->move($uploadDir, $fileName);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Unable to upload the image.',
            ]);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Image uploaded successfully.',
            'file_name' => $fileName,
            'url' => base_url('uploads/dashboard-templates/' . $fileName),
        ]);
    }

    public function userCatalog()
    {
        $products = [];
        $categories = [];
        $branding = [
            'website_name' => 'child.com',
            'logo_url' => '',
        ];
        $template = $this->getDefaultUserDashboardTemplate();
        $currencySymbol = $this->getCurrencySymbol();
        $currencyCode = $this->getCurrencyCode();
        $apiToken = trim((string) session()->get('rest_api_token'));
        if ($apiToken === '') {
            try {
                $apiToken = bin2hex(random_bytes(24));
            } catch (\Throwable $e) {
                $apiToken = sha1(uniqid('rest_api_', true));
            }
            session()->set('rest_api_token', $apiToken);
        }
        $db = db_connect();

        try {
            if ($db->tableExists('products')) {
                $products = (new ProductsModel())
                    ->where('pr_status', 1)
                    ->orderBy('id', 'DESC')
                    ->findAll(12);
            }

            if ($db->tableExists('categories') && $db->tableExists('products')) {
                $categoryRows = $db->table('products')
                    ->select('pr_category, COUNT(*) as total')
                    ->where('pr_status', 1)
                    ->groupBy('pr_category')
                    ->get()
                    ->getResultArray();

                $countMap = [];
                foreach ($categoryRows as $row) {
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

            if ($db->tableExists('settings') || $db->tableExists('system_settings')) {
                $general = (new SettingsModel())->getGroupSettings('general');
                $name = trim((string) ($general['website_name'] ?? ''));
                $logoFile = trim((string) ($general['website_logo'] ?? ''));

                if ($name !== '') {
                    $branding['website_name'] = $name;
                }

                if ($logoFile !== '') {
                    $branding['logo_url'] = base_url('uploads/settings/' . $logoFile);
                }
            }
        } catch (\Throwable $e) {
            $products = [];
            $categories = [];
        }

        $storedTemplate = $this->getStoredUserDashboardTemplate();
        if (!empty($storedTemplate)) {
            $template = array_replace_recursive($template, $storedTemplate);
        }

        return view('user/catalog', [
            'products' => $products,
            'categories' => $categories,
            'branding' => $branding,
            'dashboardTemplate' => $template,
            'restApiToken' => $apiToken,
            'currencySymbol' => $currencySymbol,
            'currencyCode' => $currencyCode,
        ]);
    }

    public function userProduct(int $id = 0)
    {
        $product = null;
        $branding = [
            'website_name' => 'child.com',
            'logo_url' => '',
        ];
        $currencySymbol = $this->getCurrencySymbol();
        $currencyCode = $this->getCurrencyCode();
        $apiToken = trim((string) session()->get('rest_api_token'));
        if ($apiToken === '') {
            try {
                $apiToken = bin2hex(random_bytes(24));
            } catch (\Throwable $e) {
                $apiToken = sha1(uniqid('rest_api_', true));
            }
            session()->set('rest_api_token', $apiToken);
        }

        $id = (int) $id;

        try {
            $db = db_connect();
            if ($db->tableExists('products') && $id > 0) {
                $row = (new ProductsModel())
                    ->where('id', $id)
                    ->where('pr_status', 1)
                    ->first();

                if ($row) {
                    $image = trim((string) ($row['pr_image'] ?? ''));
                    $name = trim((string) ($row['pr_name'] ?? 'Product'));
                    $description = trim((string) ($row['pr_description'] ?? ''));
                    $categoryValue = trim((string) ($row['pr_category'] ?? ''));
                    $brandValue = trim((string) ($row['pr_brand'] ?? ''));

                    $categoryName = $categoryValue;
                    if ($db->tableExists('categories') && ctype_digit($categoryValue)) {
                        $categoryRow = (new CategoriesModel())->find((int) $categoryValue);
                        $categoryName = trim((string) ($categoryRow['ct_name'] ?? $categoryValue));
                    }

                    $brandName = $brandValue;
                    if ($db->tableExists('brands') && ctype_digit($brandValue)) {
                        $brandRow = (new BrandsModel())->find((int) $brandValue);
                        $brandName = trim((string) ($brandRow['br_name'] ?? $brandValue));
                    }

                    $product = [
                        'id' => (int) ($row['id'] ?? 0),
                        'name' => $name !== '' ? $name : 'Product',
                        'description' => $description,
                        'image_url' => $image !== '' ? base_url('uploads/products/' . $image) : '',
                        'price' => (float) ($row['pr_price'] ?? 0),
                        'stock' => (int) ($row['pr_stock'] ?? 0),
                        'discount' => (float) ($row['pr_discount'] ?? 0),
                        'category' => $categoryName,
                        'brand' => $brandName,
                    ];
                }
            }

            if ($db->tableExists('settings') || $db->tableExists('system_settings')) {
                $general = (new SettingsModel())->getGroupSettings('general');
                $name = trim((string) ($general['website_name'] ?? ''));
                $logoFile = trim((string) ($general['website_logo'] ?? ''));

                if ($name !== '') {
                    $branding['website_name'] = $name;
                }

                if ($logoFile !== '') {
                    $branding['logo_url'] = base_url('uploads/settings/' . $logoFile);
                }
            }
        } catch (\Throwable $e) {
            $product = null;
        }

        return view('user/product', [
            'product' => $product,
            'productId' => $id,
            'branding' => $branding,
            'restApiToken' => $apiToken,
            'currencySymbol' => $currencySymbol,
            'currencyCode' => $currencyCode,
        ]);
    }

    public function userCart()
    {
        $branding = [
            'website_name' => 'child.com',
            'logo_url' => '',
        ];
        $currencySymbol = $this->getCurrencySymbol();
        $currencyCode = $this->getCurrencyCode();
        $apiToken = trim((string) session()->get('rest_api_token'));
        if ($apiToken === '') {
            try {
                $apiToken = bin2hex(random_bytes(24));
            } catch (\Throwable $e) {
                $apiToken = sha1(uniqid('rest_api_', true));
            }
            session()->set('rest_api_token', $apiToken);
        }

        try {
            $db = db_connect();
            if ($db->tableExists('settings') || $db->tableExists('system_settings')) {
                $general = (new SettingsModel())->getGroupSettings('general');
                $name = trim((string) ($general['website_name'] ?? ''));
                $logoFile = trim((string) ($general['website_logo'] ?? ''));

                if ($name !== '') {
                    $branding['website_name'] = $name;
                }

                if ($logoFile !== '') {
                    $branding['logo_url'] = base_url('uploads/settings/' . $logoFile);
                }
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        return view('user/cart', [
            'branding' => $branding,
            'restApiToken' => $apiToken,
            'currencySymbol' => $currencySymbol,
            'currencyCode' => $currencyCode,
        ]);
    }

    public function userOrders()
    {
        $branding = [
            'website_name' => 'child.com',
            'logo_url' => '',
        ];
        $currencySymbol = $this->getCurrencySymbol();
        $currencyCode = $this->getCurrencyCode();
        $apiToken = trim((string) session()->get('rest_api_token'));
        if ($apiToken === '') {
            try {
                $apiToken = bin2hex(random_bytes(24));
            } catch (\Throwable $e) {
                $apiToken = sha1(uniqid('rest_api_', true));
            }
            session()->set('rest_api_token', $apiToken);
        }

        try {
            $db = db_connect();
            if ($db->tableExists('settings') || $db->tableExists('system_settings')) {
                $general = (new SettingsModel())->getGroupSettings('general');
                $name = trim((string) ($general['website_name'] ?? ''));
                $logoFile = trim((string) ($general['website_logo'] ?? ''));

                if ($name !== '') {
                    $branding['website_name'] = $name;
                }

                if ($logoFile !== '') {
                    $branding['logo_url'] = base_url('uploads/settings/' . $logoFile);
                }
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        return view('user/orders', [
            'branding' => $branding,
            'restApiToken' => $apiToken,
            'currencySymbol' => $currencySymbol,
            'currencyCode' => $currencyCode,
        ]);
    }

    private function getStoredUserDashboardTemplate(): array
    {
        try {
            $db = db_connect();
            if ($db->tableExists('dashboard_templates')) {
                $row = (new DashboardTemplateModel())
                    ->where('dt_is_active', 1)
                    ->where('dt_status', 1)
                    ->orderBy('id', 'DESC')
                    ->first();

                if ($row) {
                    $decoded = json_decode((string) ($row['dt_json'] ?? ''), true);
                    if (is_array($decoded)) {
                        return $decoded;
                    }
                }

                return [];
            }

            if ($db->tableExists('settings') || $db->tableExists('system_settings')) {
                $stored = (new SettingsModel())->getGroupSettings('user_dashboard_template');
                return is_array($stored) ? $stored : [];
            }
        } catch (\Throwable $e) {
            return [];
        }

        return [];
    }

    private function makeDashboardTemplateSlug(string $name, int $ignoreId = 0): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        if ($base === '') {
            $base = 'dashboard-template';
        }

        $slug = $base;
        $index = 2;
        $model = new DashboardTemplateModel();

        while (true) {
            $builder = $model->where('dt_slug', $slug);
            if ($ignoreId > 0) {
                $builder->where('id !=', $ignoreId);
            }

            if (!$builder->first()) {
                return $slug;
            }

            $slug = $base . '-' . $index;
            $index++;
        }
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

    private function getAdminDashboardData(): array
    {
        $data = [
            'page' => 'dashboard',
            'userName' => (string) (session()->get('us_name') ?: 'Administrator'),
            'currentDate' => date('d M Y'),
            'stats' => [
                'orders' => 0,
                'orders_week' => 0,
                'products' => 0,
                'products_week' => 0,
                'customers' => 0,
                'customers_week' => 0,
                'revenue' => 0.0,
                'revenue_week' => 0.0,
                'revenue_previous_week' => 0.0,
            ],
            'progress' => [
                'monthly_target' => 0,
                'order_fulfillment' => 0,
                'new_customers' => 0,
            ],
            'recentOrders' => [],
        ];

        try {
            $db = db_connect();
            $weekStart = date('Y-m-d 00:00:00', strtotime('monday this week'));
            $previousWeekStart = date('Y-m-d 00:00:00', strtotime('monday last week'));
            $previousWeekEnd = date('Y-m-d 23:59:59', strtotime('sunday last week'));
            $monthStart = date('Y-m-01 00:00:00');

            if ($db->tableExists('orders')) {
                $ordersModel = new OrdersModel();
                $data['stats']['orders'] = (int) $ordersModel->countAllResults(false);
                $data['stats']['orders_week'] = (int) (new OrdersModel())
                    ->where('created_at >=', $weekStart)
                    ->countAllResults();

                $revenueRow = $db->table('orders')
                    ->selectSum('total', 'total_revenue')
                    ->get()
                    ->getRowArray();
                $data['stats']['revenue'] = (float) ($revenueRow['total_revenue'] ?? 0);

                $weekRevenueRow = $db->table('orders')
                    ->selectSum('total', 'total_revenue')
                    ->where('created_at >=', $weekStart)
                    ->get()
                    ->getRowArray();
                $data['stats']['revenue_week'] = (float) ($weekRevenueRow['total_revenue'] ?? 0);

                $previousWeekRevenueRow = $db->table('orders')
                    ->selectSum('total', 'total_revenue')
                    ->where('created_at >=', $previousWeekStart)
                    ->where('created_at <=', $previousWeekEnd)
                    ->get()
                    ->getRowArray();
                $data['stats']['revenue_previous_week'] = (float) ($previousWeekRevenueRow['total_revenue'] ?? 0);

                $completedOrders = (int) (new OrdersModel())
                    ->whereIn('status', ['completed', 'delivered'])
                    ->countAllResults();
                $data['progress']['order_fulfillment'] = $data['stats']['orders'] > 0
                    ? (int) round(($completedOrders / $data['stats']['orders']) * 100)
                    : 0;

                $monthlyRevenueRow = $db->table('orders')
                    ->selectSum('total', 'total_revenue')
                    ->where('created_at >=', $monthStart)
                    ->get()
                    ->getRowArray();
                $monthlyRevenue = (float) ($monthlyRevenueRow['total_revenue'] ?? 0);
                $data['progress']['monthly_target'] = (int) min(100, round(($monthlyRevenue / 100000) * 100));

                $data['recentOrders'] = (new OrdersModel())
                    ->orderBy('id', 'DESC')
                    ->findAll(5);
            }

            if ($db->tableExists('products')) {
                $data['stats']['products'] = (int) (new ProductsModel())->countAllResults();
                $data['stats']['products_week'] = (int) (new ProductsModel())
                    ->where('created_at >=', $weekStart)
                    ->countAllResults();
            }

            if ($db->tableExists('users')) {
                $customersModel = new CustomersModel();
                $data['stats']['customers'] = (int) $customersModel
                    ->where('us_role_id', 2)
                    ->countAllResults();

                if ($db->fieldExists('created_at', 'users')) {
                    $data['stats']['customers_week'] = (int) (new CustomersModel())
                        ->where('us_role_id', 2)
                        ->where('created_at >=', $weekStart)
                        ->countAllResults();
                }

                $data['progress']['new_customers'] = $data['stats']['customers'] > 0
                    ? (int) min(100, round(($data['stats']['customers_week'] / $data['stats']['customers']) * 100))
                    : 0;
            }
        } catch (\Throwable $e) {
            // Keep safe dashboard defaults.
        }

        return $data;
    }
}
