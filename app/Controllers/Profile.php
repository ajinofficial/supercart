<?php

namespace App\Controllers;

use App\Models\CustomersModel;
use App\Models\SettingsModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Profile extends BaseController
{
    private const DEFAULT_IMAGE_FILE = 'default-user.svg';

    protected CustomersModel $customersModel;
    protected SettingsModel $settingsModel;

    public function __construct()
    {
        $this->customersModel = new CustomersModel();
        $this->settingsModel = new SettingsModel();
        helper(['form']);
    }

    public function show()
    {
        if (session()->get('logged_in') !== true) {
            session()->set('redirect_after_login', current_url());
            return redirect()->to(base_url('login'));
        }

        $apiToken = trim((string) session()->get('rest_api_token'));
        if ($apiToken === '') {
            try {
                $apiToken = bin2hex(random_bytes(24));
            } catch (Throwable $e) {
                $apiToken = sha1(uniqid('rest_api_', true));
            }
            session()->set('rest_api_token', $apiToken);
        }

        $branding = $this->getBranding();
        $profile = $this->getUserProfile();
        $currencySymbol = $this->getCurrencySymbol();
        $currencyCode = $this->getCurrencyCode();

        return view('user/profile', [
            'branding' => $branding,
            'profile' => $profile,
            'restApiToken' => $apiToken,
            'currencySymbol' => $currencySymbol,
            'currencyCode' => $currencyCode,
        ]);
    }

    public function update(): ResponseInterface
    {
        if (session()->get('logged_in') !== true) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status' => false,
                    'message' => 'Please login to update your profile.',
                ]);
        }

        try {
            $db = db_connect();
            if (!$db->tableExists('users')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Users table does not exist.',
                ]);
            }

            if (!$this->validate($this->getUpdateRules())) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $userId = (int) session()->get('user_id');
            if ($userId <= 0) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid session user.',
                ]);
            }

            $existing = $this->customersModel->find($userId);
            if (!$existing) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'User not found.',
                ]);
            }

            $email = strtolower(trim((string) $this->request->getPost('email')));
            if ($this->emailExists($email, $userId)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'email' => 'Email already exists.',
                    ],
                ]);
            }

            $fields = $db->getFieldNames('users');
            $updateData = [
                'us_name' => trim((string) $this->request->getPost('name')),
                'us_email' => $email,
            ];
            if (in_array('us_phone', $fields, true)) {
                $updateData['us_phone'] = trim((string) $this->request->getPost('phone'));
            }
            $addressMap = [
                'us_address_line1' => 'address_line1',
                'us_address_line2' => 'address_line2',
                'us_city' => 'city',
                'us_state' => 'state',
                'us_postal_code' => 'postal_code',
                'us_country' => 'country',
            ];
            foreach ($addressMap as $column => $inputName) {
                if (in_array($column, $fields, true)) {
                    $updateData[$column] = trim((string) $this->request->getPost($inputName));
                }
            }

            $password = trim((string) $this->request->getPost('password'));
            if ($password !== '') {
                $updateData['us_password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $imageUpload = [
                'status' => true,
                'file_name' => '',
                'message' => '',
            ];
            $hasImageColumn = in_array('us_image', $fields, true);
            if ($hasImageColumn) {
                $imageUpload = $this->uploadProfileImage();
            } elseif ($this->hasIncomingImageFile()) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'profile_image' => 'Profile image column is missing. Run migrations.',
                    ],
                ]);
            }
            if (!$imageUpload['status']) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'profile_image' => $imageUpload['message'],
                    ],
                ]);
            }

            if ($hasImageColumn && $imageUpload['file_name'] !== '') {
                $oldFile = (string) ($existing['us_image'] ?? '');
                $updateData['us_image'] = $imageUpload['file_name'];
                $this->deleteProfileImage($oldFile);
            }

            if (!$this->customersModel->update($userId, $updateData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to update profile.',
                ]);
            }

            $imageFile = $hasImageColumn
                ? (string) ($updateData['us_image'] ?? ($existing['us_image'] ?? self::DEFAULT_IMAGE_FILE))
                : self::DEFAULT_IMAGE_FILE;

            session()->set([
                'us_name' => $updateData['us_name'],
                'email' => $updateData['us_email'],
                'us_image' => $imageFile,
            ]);

            $userResponse = [
                'id' => $userId,
                'name' => $updateData['us_name'],
                'email' => $updateData['us_email'],
                'phone' => $updateData['us_phone'] ?? (string) ($existing['us_phone'] ?? ''),
                'image_url' => base_url('uploads/customers/' . ($imageFile !== '' ? $imageFile : self::DEFAULT_IMAGE_FILE)),
            ];
            foreach ($addressMap as $column => $inputName) {
                if (array_key_exists($column, $updateData)) {
                    $userResponse[$inputName] = (string) $updateData[$column];
                } elseif (isset($existing[$column])) {
                    $userResponse[$inputName] = (string) $existing[$column];
                }
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Profile updated successfully.',
                'user' => $userResponse,
            ]);
        } catch (Throwable $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => false,
                    'message' => $e->getMessage(),
                ]);
        }
    }

    private function getUpdateRules(): array
    {
        return [
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]',
            'phone' => 'permit_empty|min_length[8]|max_length[20]',
            'password' => 'permit_empty|min_length[8]|max_length[100]',
            'address_line1' => 'permit_empty|max_length[200]',
            'address_line2' => 'permit_empty|max_length[200]',
            'city' => 'permit_empty|max_length[100]',
            'state' => 'permit_empty|max_length[100]',
            'postal_code' => 'permit_empty|max_length[20]',
            'country' => 'permit_empty|max_length[100]',
        ];
    }

    private function getUserProfile(): array
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        $profile = [
            'id' => $userId,
            'name' => (string) (session()->get('us_name') ?? ''),
            'email' => (string) (session()->get('email') ?? ''),
            'phone' => '',
            'image_url' => base_url('uploads/customers/' . self::DEFAULT_IMAGE_FILE),
            'address_line1' => '',
            'address_line2' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'country' => '',
        ];

        if ($userId <= 0) {
            return $profile;
        }

        try {
            $row = $this->customersModel->find($userId);
            if (!$row) {
                return $profile;
            }

            $imageFile = trim((string) ($row['us_image'] ?? ''));
            if ($imageFile === '') {
                $imageFile = self::DEFAULT_IMAGE_FILE;
            }

            $profile['name'] = (string) ($row['us_name'] ?? $profile['name']);
            $profile['email'] = (string) ($row['us_email'] ?? $profile['email']);
            $profile['phone'] = (string) ($row['us_phone'] ?? '');
            $profile['image_url'] = base_url('uploads/customers/' . $imageFile);
            $profile['address_line1'] = (string) ($row['us_address_line1'] ?? '');
            $profile['address_line2'] = (string) ($row['us_address_line2'] ?? '');
            $profile['city'] = (string) ($row['us_city'] ?? '');
            $profile['state'] = (string) ($row['us_state'] ?? '');
            $profile['postal_code'] = (string) ($row['us_postal_code'] ?? '');
            $profile['country'] = (string) ($row['us_country'] ?? '');
        } catch (Throwable $e) {
            // keep fallback
        }

        return $profile;
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
            // keep fallback branding
        }

        return $branding;
    }

    private function emailExists(string $email, int $excludeId): bool
    {
        return $this->customersModel
            ->where('us_email', $email)
            ->where('id !=', $excludeId)
            ->first() !== null;
    }

    private function uploadProfileImage(): array
    {
        $image = $this->request->getFile('profile_image');
        if (!$image || $image->getError() === UPLOAD_ERR_NO_FILE) {
            return [
                'status' => true,
                'file_name' => '',
                'message' => '',
            ];
        }

        if (!$image->isValid()) {
            return [
                'status' => false,
                'file_name' => '',
                'message' => 'Valid profile image is required.',
            ];
        }

        $allowedMime = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'];
        if (!in_array(strtolower((string) $image->getMimeType()), $allowedMime, true)) {
            return [
                'status' => false,
                'file_name' => '',
                'message' => 'Only JPG, PNG, WEBP, SVG allowed.',
            ];
        }

        if ($image->getSizeByUnit('kb') > 2048) {
            return [
                'status' => false,
                'file_name' => '',
                'message' => 'Image size must be 2MB or less.',
            ];
        }

        $uploadDir = FCPATH . 'uploads/customers';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return [
                'status' => false,
                'file_name' => '',
                'message' => 'Unable to create upload directory.',
            ];
        }

        $newName = $image->getRandomName();
        $image->move($uploadDir, $newName);

        return [
            'status' => true,
            'file_name' => $newName,
            'message' => '',
        ];
    }

    private function hasIncomingImageFile(): bool
    {
        $image = $this->request->getFile('profile_image');
        if (!$image) {
            return false;
        }

        return $image->getError() !== UPLOAD_ERR_NO_FILE;
    }

    private function deleteProfileImage(string $fileName): void
    {
        $fileName = trim($fileName);
        if ($fileName === '' || $fileName === self::DEFAULT_IMAGE_FILE) {
            return;
        }

        $path = FCPATH . 'uploads/customers/' . $fileName;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
