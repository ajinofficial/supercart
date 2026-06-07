<?php

namespace App\Controllers;

use App\Models\DashboardTemplateModel;
use App\Models\SettingsModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Pages extends BaseController
{
    protected SettingsModel $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
        helper(['form']);
    }

    public function about()
    {
        $this->ensureRestApiToken();
        $currencySymbol = $this->getCurrencySymbol();
        $currencyCode = $this->getCurrencyCode();

        return view('user/about', [
            'branding' => $this->getBranding(),
            'pageTemplate' => $this->getPageTemplate(),
            'restApiToken' => (string) session()->get('rest_api_token'),
            'currencySymbol' => $currencySymbol,
            'currencyCode' => $currencyCode,
        ]);
    }

    public function contact()
    {
        $this->ensureRestApiToken();
        $currencySymbol = $this->getCurrencySymbol();
        $currencyCode = $this->getCurrencyCode();

        return view('user/contact', [
            'branding' => $this->getBranding(),
            'pageTemplate' => $this->getPageTemplate(),
            'restApiToken' => (string) session()->get('rest_api_token'),
            'currencySymbol' => $currencySymbol,
            'currencyCode' => $currencyCode,
        ]);
    }

    public function submitContact(): ResponseInterface
    {
        if (!$this->validate($this->getContactRules())) {
            return $this->response->setJSON([
                'status' => false,
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $payload = [
            'name' => trim((string) $this->request->getPost('name')),
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'phone' => trim((string) $this->request->getPost('phone')),
            'message' => trim((string) $this->request->getPost('message')),
        ];

        try {
            $db = db_connect();
            if ($db->tableExists('contact_messages')) {
                $fields = $db->getFieldNames('contact_messages');
                $insertData = [];

                foreach ($payload as $key => $value) {
                    $column = 'cm_' . $key;
                    if (in_array($column, $fields, true)) {
                        $insertData[$column] = $value;
                    } elseif (in_array($key, $fields, true)) {
                        $insertData[$key] = $value;
                    }
                }

                if (in_array('created_at', $fields, true)) {
                    $insertData['created_at'] = date('Y-m-d H:i:s');
                }

                if (!empty($insertData)) {
                    $db->table('contact_messages')->insert($insertData);
                }
            }
        } catch (Throwable $e) {
            log_message('error', 'Contact submit failed: {message}', ['message' => $e->getMessage()]);
        }

        $template = $this->getPageTemplate();

        return $this->response->setJSON([
            'status' => true,
            'message' => (string) ($template['contact_page']['success_message'] ?? 'Thanks! We received your message and will respond shortly.'),
        ]);
    }

    private function getContactRules(): array
    {
        return [
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]',
            'phone' => 'permit_empty|min_length[8]|max_length[20]',
            'message' => 'required|min_length[10]|max_length[1000]',
        ];
    }

    private function ensureRestApiToken(): void
    {
        $apiToken = trim((string) session()->get('rest_api_token'));
        if ($apiToken !== '') {
            return;
        }

        try {
            $apiToken = bin2hex(random_bytes(24));
        } catch (Throwable $e) {
            $apiToken = sha1(uniqid('rest_api_', true));
        }

        session()->set('rest_api_token', $apiToken);
    }

    private function getBranding(): array
    {
        $branding = [
            'website_name' => 'child.com',
            'logo_url' => '',
        ];

        try {
            $db = db_connect();
            if (!$db->tableExists('settings') && !$db->tableExists('system_settings')) {
                return $branding;
            }

            $general = $this->settingsModel->getGroupSettings('general');
            $name = trim((string) ($general['website_name'] ?? ''));
            $logoFile = trim((string) ($general['website_logo'] ?? ''));

            if ($name !== '') {
                $branding['website_name'] = $name;
            }

            if ($logoFile !== '') {
                $branding['logo_url'] = base_url('uploads/settings/' . $logoFile);
            }
        } catch (Throwable $e) {
            // keep defaults
        }

        return $branding;
    }

    private function getPageTemplate(): array
    {
        $template = [
            'nav' => [
                'home' => 'Home',
                'courses' => 'Courses',
                'contacts' => 'Contacts',
                'about' => 'About',
                'register' => 'Register',
                'login' => 'Login',
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
        ];

        try {
            $db = db_connect();
            if (!$db->tableExists('dashboard_templates')) {
                return $template;
            }

            $row = (new DashboardTemplateModel())
                ->where('dt_is_active', 1)
                ->where('dt_status', 1)
                ->orderBy('id', 'DESC')
                ->first();

            if (!$row) {
                return $template;
            }

            $stored = json_decode((string) ($row['dt_json'] ?? ''), true);
            if (is_array($stored)) {
                $template = array_replace_recursive($template, $stored);
            }
        } catch (Throwable $e) {
            // keep defaults
        }

        return $template;
    }
}
