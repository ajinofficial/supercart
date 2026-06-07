<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SettingsModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Settings extends BaseController
{
    protected SettingsModel $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
    }

    public function settings_view()
    {
        $data['page'] = 'settings';
        $data['general'] = $this->getDefaultGeneral();
        $data['payment'] = $this->getDefaultPayment();
        $data['currency_catalog'] = [];
        $data['email'] = $this->getDefaultEmail();
        $data['system'] = $this->getDefaultSystem();

        try {
            $db = db_connect();
            if ($db->tableExists('settings') || $db->tableExists('system_settings')) {
                $data['general'] = array_merge($data['general'], $this->settingsModel->getGroupSettings('general'));
                $data['payment'] = array_merge($data['payment'], $this->settingsModel->getGroupSettings('payment'));
                $data['email'] = array_merge($data['email'], $this->settingsModel->getGroupSettings('email'));
                $data['system'] = array_merge($data['system'], $this->settingsModel->getGroupSettings('system'));
            }
        } catch (Throwable $e) {
            // keep defaults
        }

        $catalogPath = APPPATH . 'Config/currency_catalog.php';
        if (is_file($catalogPath)) {
            $data['currency_catalog'] = (array) include $catalogPath;
        }

        return view('admin/settings', $data);
    }

    public function saveGeneral(): ResponseInterface
    {
        try {
            if (!$this->validate([
                'website_name' => 'required|min_length[2]|max_length[120]',
                'website_tagline' => 'permit_empty|max_length[160]',
                'default_timezone' => 'required|max_length[100]',
                'theme_color' => 'required|regex_match[/^#([A-Fa-f0-9]{6})$/]',
                'font_family' => 'required|in_list[inter,manrope,poppins,roboto,open_sans,lato,nunito]',
            ])) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $timezone = trim((string) $this->request->getPost('default_timezone'));
            if (!in_array($timezone, timezone_identifiers_list(), true)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => ['default_timezone' => 'Please select a valid timezone.'],
                ]);
            }

            $existingGeneral = [];
            try {
                $existingGeneral = $this->settingsModel->getGroupSettings('general');
            } catch (Throwable $e) {
                $existingGeneral = [];
            }

            $existingLogo = trim((string) ($existingGeneral['website_logo'] ?? ''));
            $existingAuthBackground = trim((string) ($existingGeneral['auth_background_image'] ?? ''));

            $settings = [
                'website_name' => trim((string) $this->request->getPost('website_name')),
                'website_tagline' => trim((string) $this->request->getPost('website_tagline')),
                'default_timezone' => $timezone,
                'theme_color' => strtolower(trim((string) $this->request->getPost('theme_color'))),
                'font_family' => trim((string) $this->request->getPost('font_family')),
                // Preserve current logo when no new upload is provided.
                'website_logo' => $existingLogo,
                // Preserve current auth background image when no new upload is provided.
                'auth_background_image' => $existingAuthBackground,
            ];

            $logoUpload = $this->uploadWebsiteLogo();
            if (!$logoUpload['status']) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => ['website_logo' => $logoUpload['message']],
                ]);
            }

            if ($logoUpload['file_name'] !== '') {
                $settings['website_logo'] = $logoUpload['file_name'];
            }

            $backgroundUpload = $this->uploadAuthBackgroundImage();
            if (!$backgroundUpload['status']) {
                if ($logoUpload['file_name'] !== '') {
                    $this->deleteWebsiteLogo($logoUpload['file_name']);
                }
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => ['auth_background_image' => $backgroundUpload['message']],
                ]);
            }

            if ($backgroundUpload['file_name'] !== '') {
                $settings['auth_background_image'] = $backgroundUpload['file_name'];
            }

            if (!$this->settingsModel->saveGroupAsJson('general', $settings)) {
                if ($logoUpload['file_name'] !== '') {
                    $this->deleteWebsiteLogo($logoUpload['file_name']);
                }
                if ($backgroundUpload['file_name'] !== '') {
                    $this->deleteAuthBackgroundImage($backgroundUpload['file_name']);
                }
                return $this->response->setJSON([
                    'status' => false,
                    'message' => $this->settingsModel->getLastErrorMessage() !== ''
                        ? $this->settingsModel->getLastErrorMessage()
                        : 'Unable to save general settings.',
                ]);
            }

            if ($logoUpload['file_name'] !== '' && $existingLogo !== '') {
                $this->deleteWebsiteLogo($existingLogo);
            }
            if ($backgroundUpload['file_name'] !== '' && $existingAuthBackground !== '') {
                $this->deleteAuthBackgroundImage($existingAuthBackground);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'General settings updated successfully.',
                'font_family' => $settings['font_family'],
                'logo_url' => $settings['website_logo'] !== '' ? base_url('uploads/settings/' . $settings['website_logo']) : null,
                'auth_background_url' => $settings['auth_background_image'] !== '' ? base_url('uploads/settings/' . $settings['auth_background_image']) : null,
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function savePayment(): ResponseInterface
    {
        try {
            if (!$this->validate([
                'currency_code' => 'required|alpha|min_length[3]|max_length[3]',
                'currency_symbol' => 'required|max_length[10]',
                'tax_type' => 'required|in_list[inclusive,exclusive]',
                'tax_rate' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
                'gateway_enabled' => 'required|in_list[0,1]',
                'cod_enabled' => 'required|in_list[0,1]',
                'upi_enabled' => 'required|in_list[0,1]',
                'razorpay_mode' => 'required|in_list[test,live]',
                'razorpay_key_id' => 'permit_empty|max_length[100]',
                'razorpay_key_secret' => 'permit_empty|max_length[160]',
                'checkout_name' => 'required|min_length[2]|max_length[120]',
                'checkout_theme_color' => 'required|regex_match[/^#([A-Fa-f0-9]{6})$/]',
            ])) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $existingPayment = [];
            try {
                $existingPayment = $this->settingsModel->getGroupSettings('payment');
            } catch (Throwable $e) {
                $existingPayment = [];
            }

            $gatewayEnabled = trim((string) $this->request->getPost('gateway_enabled'));
            $codEnabled = trim((string) $this->request->getPost('cod_enabled'));
            $upiEnabled = trim((string) $this->request->getPost('upi_enabled'));
            if ($gatewayEnabled !== '1' && $codEnabled !== '1' && $upiEnabled !== '1') {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Enable at least one payment method.',
                ]);
            }
            $keyId = trim((string) $this->request->getPost('razorpay_key_id'));
            $keySecret = trim((string) $this->request->getPost('razorpay_key_secret'));
            $currencyCode = strtoupper(trim((string) $this->request->getPost('currency_code')));
            $gatewayMode = trim((string) $this->request->getPost('razorpay_mode'));
            if ($keyId === '') {
                $keyId = trim((string) ($existingPayment['razorpay_key_id'] ?? ''));
            }
            if ($keyId === '') {
                $keyId = trim((string) env('razorpay.keyId', ''));
            }
            if ($keySecret === '') {
                $keySecret = trim((string) ($existingPayment['razorpay_key_secret'] ?? ''));
            }
            if ($keySecret === '') {
                $keySecret = trim((string) env('razorpay.keySecret', ''));
            }
            if ($gatewayEnabled === '1' && ($keyId === '' || $keySecret === '')) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'razorpay_key_id' => $keyId === '' ? 'Razorpay Key ID is required when the gateway is enabled.' : '',
                        'razorpay_key_secret' => $keySecret === '' ? 'Razorpay Key Secret is required when the gateway is enabled.' : '',
                    ],
                ]);
            }
            if ($gatewayEnabled === '1' && $currencyCode !== 'INR') {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => ['currency_code' => 'UPI and Google Pay checkout requires INR currency.'],
                ]);
            }
            $expectedPrefix = $gatewayMode === 'live' ? 'rzp_live_' : 'rzp_test_';
            if ($gatewayEnabled === '1' && !str_starts_with($keyId, $expectedPrefix)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => ['razorpay_key_id' => 'Key ID must match the selected ' . $gatewayMode . ' mode.'],
                ]);
            }

            $settings = [
                'currency_code' => $currencyCode,
                'currency_symbol' => trim((string) $this->request->getPost('currency_symbol')),
                'tax_type' => trim((string) $this->request->getPost('tax_type')),
                'tax_rate' => (string) ((float) $this->request->getPost('tax_rate')),
                'gateway_enabled' => $gatewayEnabled,
                'cod_enabled' => $codEnabled,
                'upi_enabled' => $upiEnabled,
                'razorpay_mode' => $gatewayMode,
                'razorpay_key_id' => $keyId,
                'razorpay_key_secret' => $keySecret,
                'checkout_name' => trim((string) $this->request->getPost('checkout_name')),
                'checkout_theme_color' => strtolower(trim((string) $this->request->getPost('checkout_theme_color'))),
            ];

            if (!$this->settingsModel->saveGroupAsJson('payment', $settings)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to save payment settings.',
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Payment settings updated successfully.',
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function saveEmail(): ResponseInterface
    {
        try {
            if (!$this->validate([
                'smtp_host' => 'required|max_length[150]',
                'smtp_port' => 'required|integer|greater_than[0]|less_than[65536]',
                'smtp_username' => 'required|max_length[120]',
                'smtp_password' => 'permit_empty|max_length[120]',
                'smtp_encryption' => 'required|in_list[none,ssl,tls]',
                'from_email' => 'required|valid_email|max_length[150]',
                'from_name' => 'required|max_length[100]',
            ])) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $existingEmail = $this->settingsModel->getGroupSettings('email');
            $smtpPassword = trim((string) $this->request->getPost('smtp_password'));
            if ($smtpPassword === '') {
                $smtpPassword = trim((string) ($existingEmail['smtp_password'] ?? ''));
            }
            if ($smtpPassword === '') {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => ['smtp_password' => 'SMTP password is required until one has been configured.'],
                ]);
            }

            $settings = [
                'smtp_host' => trim((string) $this->request->getPost('smtp_host')),
                'smtp_port' => trim((string) $this->request->getPost('smtp_port')),
                'smtp_username' => trim((string) $this->request->getPost('smtp_username')),
                'smtp_password' => $smtpPassword,
                'smtp_encryption' => trim((string) $this->request->getPost('smtp_encryption')),
                'from_email' => trim((string) $this->request->getPost('from_email')),
                'from_name' => trim((string) $this->request->getPost('from_name')),
            ];

            if (!$this->settingsModel->saveGroupAsJson('email', $settings)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to save email settings.',
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Email settings updated successfully.',
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function saveSystem(): ResponseInterface
    {
        try {
            if (!$this->validate([
                'maintenance_mode' => 'required|in_list[0,1]',
                'allow_registration' => 'required|in_list[0,1]',
                'items_per_page' => 'required|integer|greater_than_equal_to[5]|less_than_equal_to[200]',
                'default_language' => 'required|in_list[en,ta,hi]',
            ])) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $settings = [
                'maintenance_mode' => trim((string) $this->request->getPost('maintenance_mode')),
                'allow_registration' => trim((string) $this->request->getPost('allow_registration')),
                'items_per_page' => trim((string) $this->request->getPost('items_per_page')),
                'default_language' => trim((string) $this->request->getPost('default_language')),
            ];

            if (!$this->settingsModel->saveGroupAsJson('system', $settings)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to save system settings.',
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'System preferences updated successfully.',
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function getDefaultGeneral(): array
    {
        return [
            'website_name' => 'Ebolt',
            'website_tagline' => 'Power your commerce',
            'default_timezone' => 'Asia/Kolkata',
            'theme_color' => '#0f6cad',
            'font_family' => 'inter',
            'website_logo' => '',
            'auth_background_image' => '',
        ];
    }

    private function getDefaultPayment(): array
    {
        return [
            'currency_code' => 'INR',
            'currency_symbol' => 'Rs',
            'tax_type' => 'exclusive',
            'tax_rate' => '18',
            'gateway_enabled' => '0',
            'cod_enabled' => '1',
            'upi_enabled' => '1',
            'razorpay_mode' => 'test',
            'razorpay_key_id' => '',
            'razorpay_key_secret' => '',
            'checkout_name' => 'Ebolt',
            'checkout_theme_color' => '#0f6cad',
        ];
    }

    private function getDefaultEmail(): array
    {
        return [
            'smtp_host' => '',
            'smtp_port' => '587',
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',
            'from_email' => '',
            'from_name' => '',
        ];
    }

    private function getDefaultSystem(): array
    {
        return [
            'maintenance_mode' => '0',
            'allow_registration' => '1',
            'items_per_page' => '10',
            'default_language' => 'en',
        ];
    }

    private function uploadWebsiteLogo(): array
    {
        $logo = $this->request->getFile('website_logo');

        if (!$logo || $logo->getError() === UPLOAD_ERR_NO_FILE) {
            return ['status' => true, 'file_name' => ''];
        }

        if (!$logo->isValid()) {
            return ['status' => false, 'message' => 'Valid logo file is required.'];
        }

        $allowedMime = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'];
        if (!in_array(strtolower((string) $logo->getMimeType()), $allowedMime, true)) {
            return ['status' => false, 'message' => 'Only JPG, PNG, WEBP, SVG allowed.'];
        }

        if ($logo->getSizeByUnit('kb') > 1024) {
            return ['status' => false, 'message' => 'Logo size must be 1MB or less.'];
        }

        $uploadDir = FCPATH . 'uploads/settings';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return ['status' => false, 'message' => 'Unable to create settings upload directory.'];
        }

        $newName = $logo->getRandomName();
        $logo->move($uploadDir, $newName);

        return ['status' => true, 'file_name' => $newName];
    }

    private function deleteWebsiteLogo(string $fileName): void
    {
        if ($fileName === '') {
            return;
        }

        $path = FCPATH . 'uploads/settings/' . $fileName;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function uploadAuthBackgroundImage(): array
    {
        $image = $this->request->getFile('auth_background_image');

        if (!$image || $image->getError() === UPLOAD_ERR_NO_FILE) {
            return ['status' => true, 'file_name' => ''];
        }

        if (!$image->isValid()) {
            return ['status' => false, 'message' => 'Valid background image is required.'];
        }

        $allowedMime = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array(strtolower((string) $image->getMimeType()), $allowedMime, true)) {
            return ['status' => false, 'message' => 'Only JPG, PNG, WEBP allowed for background image.'];
        }

        if ($image->getSizeByUnit('kb') > 3072) {
            return ['status' => false, 'message' => 'Background image size must be 3MB or less.'];
        }

        $uploadDir = FCPATH . 'uploads/settings';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return ['status' => false, 'message' => 'Unable to create settings upload directory.'];
        }

        $newName = $image->getRandomName();
        $image->move($uploadDir, $newName);

        return ['status' => true, 'file_name' => $newName];
    }

    private function deleteAuthBackgroundImage(string $fileName): void
    {
        if ($fileName === '') {
            return;
        }

        $path = FCPATH . 'uploads/settings/' . $fileName;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
