<?php

namespace App\Controllers;

use App\Models\SettingsModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Auth extends BaseController
{
    private const ROLE_ADMIN = 1;
    private const ROLE_CUSTOMER = 2;
    protected UserModel $userModel;
    protected SettingsModel $settingsModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->settingsModel = new SettingsModel();
        helper(['form']);
    }

    public function login()
    {
        if (session()->get('logged_in') === true) {
            $roleId = (int) (session()->get('us_role_id') ?? 0);
            return redirect()->to($this->resolveRedirectByRole($roleId));
        }

        return view('auth/login', [
            'branding' => $this->getAuthBranding(),
        ]);
    }

    public function register()
    {
        if (session()->get('logged_in') === true) {
            $roleId = (int) (session()->get('us_role_id') ?? 0);
            return redirect()->to($this->resolveRedirectByRole($roleId));
        }

        return view('auth/register', [
            'branding' => $this->getAuthBranding(),
        ]);
    }

    public function logout()
    {
        $session = session();
        $userId  = $session->get('user_id');

        if (!empty($userId)) {
            cache()->delete('login_session_user_' . $userId);
        }

        $session->destroy();

        return redirect()->to(base_url('login'));
    }

    public function loginValidate(): ResponseInterface
    {
        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        $user = $this->userModel->getUserByEmail($email);

        if (!$user) {
            return $this->response->setJSON([
                'status'   => false,
                'input'    => 'email',
                'message'  => 'Email not registered',
                'csrfHash' => csrf_hash(),
            ]);
        }

        if (!password_verify($password, $user['us_password'])) {
            return $this->response->setJSON([
                'status'   => false,
                'input'    => 'password',
                'message'  => 'Incorrect password',
                'csrfHash' => csrf_hash(),
            ]);
        }

        session()->set([
            'user_id'   => $user['id'],
            'email'     => $user['us_email'],
            'us_name'   => $user['us_name'] ?? '',
            'us_role_id'=> $user['us_role_id'] ?? null,
            'us_image'  => $user['us_image'] ?? '',
            'logged_in' => true,
        ]);

        cache()->save(
            'login_session_user_' . $user['id'],
            [
                'session_id'   => session_id(),
                'user_id'      => $user['id'],
                'email'        => $user['us_email'],
                'logged_in_at' => date('Y-m-d H:i:s'),
            ],
            3600
        );

        $this->saveSessionActivity($user);

        $roleId = (int) ($user['us_role_id'] ?? 0);
        session()->remove('redirect_after_login');
        $redirectUrl = $this->resolveRedirectByRole($roleId);

        return $this->response->setJSON([
            'status'   => true,
            'message'  => 'Login successful',
            'redirect' => $redirectUrl,
            'csrfHash' => csrf_hash(),
        ]);
    }

    public function sessionApi(): ResponseInterface
    {
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

    private function resolveRedirectByRole(int $roleId): string
    {
        if ($roleId === self::ROLE_CUSTOMER) {
            return base_url('user/dashboard');
        }

        return base_url('admin/dashboard');
    }

    private function saveSessionActivity(array $user): void
    {
        try {
            $db = db_connect();

            if (!$db->tableExists('session_activity')) {
                return;
            }

            $table      = $db->table('session_activity');
            $fields     = $db->getFieldNames('session_activity');
            $now        = date('Y-m-d H:i:s');
            $userAgent  = substr((string) $this->request->getUserAgent(), 0, 255);
            $columnData = [
                'sa_user_id'   => $user['id'],
                'sa_email'     => $user['us_email'],
                'sa_session_id'=> session_id(),
                'sa_ip_address'=> $this->request->getIPAddress(),
                'sa_user_agent'=> $userAgent,
                'sa_status'    => 1,
                'sa_login_at'  => $now,
            ];

            $insertData = [];

            foreach ($columnData as $column => $value) {
                if (in_array($column, $fields, true)) {
                    $insertData[$column] = $value;
                }
            }

            if (!empty($insertData)) {
                if (!$table->insert($insertData)) {
                    log_message('error', 'session_activity insert failed: {error}', ['error' => json_encode($db->error())]);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to write session activity: {message}', ['message' => $e->getMessage()]);
        }
    }

    private function getAuthBranding(): array
    {
        $branding = [
            'website_name' => 'Ebolt',
            'website_tagline' => 'Power your commerce',
            'font_family' => 'inter',
            'logo_url' => base_url('materials/admin/images/logo.png'),
            'theme_color' => '#0f6cad',
            'auth_background_url' => '',
        ];

        try {
            $db = db_connect();
            if (!$db->tableExists('settings') && !$db->tableExists('system_settings')) {
                return $branding;
            }

            $general = $this->settingsModel->getGroupSettings('general');
            $system = $this->settingsModel->getGroupSettings('system');
            $websiteName = trim((string) ($general['website_name'] ?? ''));
            $websiteTagline = trim((string) ($general['website_tagline'] ?? ''));
            $fontFamily = trim((string) ($general['font_family'] ?? ''));
            $logoFile = trim((string) ($general['website_logo'] ?? ''));
            $authBackground = trim((string) ($general['auth_background_image'] ?? ''));
            $themeColor = trim((string) ($general['theme_color'] ?? ($system['theme_color'] ?? '')));

            if ($websiteName !== '') {
                $branding['website_name'] = $websiteName;
            }

            if ($websiteTagline !== '') {
                $branding['website_tagline'] = $websiteTagline;
            }

            if (in_array($fontFamily, ['inter', 'manrope', 'poppins', 'roboto', 'open_sans', 'lato', 'nunito'], true)) {
                $branding['font_family'] = $fontFamily;
            }

            if ($logoFile !== '') {
                $branding['logo_url'] = base_url('uploads/settings/' . $logoFile);
            }

            if ($authBackground !== '') {
                $branding['auth_background_url'] = base_url('uploads/settings/' . $authBackground);
            }

            if (preg_match('/^#([A-Fa-f0-9]{6})$/', $themeColor)) {
                $branding['theme_color'] = strtolower($themeColor);
            }
        } catch (Throwable $e) {
            // Keep fallback branding
        }

        return $branding;
    }
}
