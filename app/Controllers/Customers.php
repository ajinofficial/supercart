<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomersModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Customers extends BaseController
{
    private const CUSTOMER_ROLE_ID = 2;
    private const DEFAULT_IMAGE_FILE = 'default-user.svg';

    protected CustomersModel $customersModel;

    public function __construct()
    {
        $this->customersModel = new CustomersModel();
    }

    public function customers_view()
    {
        $data['page'] = 'customers';
        $data['customers'] = [];

        try {
            $db = db_connect();
            if ($db->tableExists('users')) {
                $data['customers'] = $this->customersModel
                    ->where('us_role_id', self::CUSTOMER_ROLE_ID)
                    ->orderBy('id', 'DESC')
                    ->findAll();
            }
        } catch (Throwable $e) {
            $data['customers'] = [];
        }

        return view('admin/customers', $data);
    }

    public function addCustomer(): ResponseInterface
    {
        try {
            $db = db_connect();
            if (!$db->tableExists('users')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Users table does not exist.',
                ]);
            }

            if (!$this->validate($this->getCreateRules())) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $hasImageColumn = $db->fieldExists('us_image', 'users');
            $imageUpload = [
                'status' => true,
                'file_name' => '',
                'message' => '',
            ];
            if ($hasImageColumn) {
                $imageUpload = $this->uploadCustomerImage();
            } elseif ($this->hasIncomingImageFile()) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'customer_image' => 'Customer image column is missing. Run migrations.',
                    ],
                ]);
            }
            if (!$imageUpload['status']) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'customer_image' => $imageUpload['message'],
                    ],
                ]);
            }

            $email = strtolower(trim((string) $this->request->getPost('email')));
            if ($this->emailExists($email)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'email' => 'Email already exists.',
                    ],
                ]);
            }

            $insertData = [
                'us_name' => trim((string) $this->request->getPost('name')),
                'us_email' => $email,
                'us_phone' => trim((string) $this->request->getPost('phone')),
                'us_role_id' => self::CUSTOMER_ROLE_ID,
                'us_password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            ];
            if ($hasImageColumn) {
                $insertData['us_image'] = $imageUpload['file_name'] !== '' ? $imageUpload['file_name'] : self::DEFAULT_IMAGE_FILE;
            }

            if (!$this->customersModel->insert($insertData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to add customer.',
                ]);
            }

            $insertId = (int) $this->customersModel->getInsertID();

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Customer added successfully.',
                'customer' => $this->buildCustomerPayload(
                    $insertId,
                    $insertData['us_name'],
                    $insertData['us_email'],
                    $insertData['us_phone'],
                    date('d-m-Y'),
                    $insertData['us_image'] ?? self::DEFAULT_IMAGE_FILE
                ),
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

    public function updateCustomer(): ResponseInterface
    {
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

            $hasImageColumn = $db->fieldExists('us_image', 'users');
            $imageUpload = [
                'status' => true,
                'file_name' => '',
                'message' => '',
            ];
            if ($hasImageColumn) {
                $imageUpload = $this->uploadCustomerImage();
            } elseif ($this->hasIncomingImageFile()) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'customer_image' => 'Customer image column is missing. Run migrations.',
                    ],
                ]);
            }
            if (!$imageUpload['status']) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'customer_image' => $imageUpload['message'],
                    ],
                ]);
            }

            $id = (int) $this->request->getPost('id');
            $existing = $this->customersModel
                ->where('id', $id)
                ->where('us_role_id', self::CUSTOMER_ROLE_ID)
                ->first();

            if (!$existing) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Customer not found.',
                ]);
            }

            $email = strtolower(trim((string) $this->request->getPost('email')));
            if ($this->emailExists($email, $id)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'email' => 'Email already exists.',
                    ],
                ]);
            }

            $updateData = [
                'us_name' => trim((string) $this->request->getPost('name')),
                'us_email' => $email,
                'us_phone' => trim((string) $this->request->getPost('phone')),
            ];

            $password = trim((string) $this->request->getPost('password'));
            if ($password !== '') {
                $updateData['us_password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            if ($hasImageColumn && $imageUpload['file_name'] !== '') {
                $oldFile = (string) ($existing['us_image'] ?? '');
                $updateData['us_image'] = $imageUpload['file_name'];
                $this->deleteCustomerImage($oldFile);
            }

            if (!$this->customersModel->update($id, $updateData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to update customer.',
                ]);
            }

            $imageFile = self::DEFAULT_IMAGE_FILE;
            if ($hasImageColumn) {
                $imageFile = (string) ($updateData['us_image'] ?? ($existing['us_image'] ?? self::DEFAULT_IMAGE_FILE));
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Customer updated successfully.',
                'customer' => $this->buildCustomerPayload(
                    $id,
                    $updateData['us_name'],
                    $updateData['us_email'],
                    $updateData['us_phone'],
                    date('d-m-Y'),
                    $imageFile
                ),
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

    private function getCreateRules(): array
    {
        return [
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]',
            'phone' => 'required|min_length[8]|max_length[20]',
            'password' => 'required|min_length[8]|max_length[100]',
        ];
    }

    private function getUpdateRules(): array
    {
        return [
            'id' => 'required|is_natural_no_zero',
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]',
            'phone' => 'required|min_length[8]|max_length[20]',
            'password' => 'permit_empty|min_length[8]|max_length[100]',
        ];
    }

    private function emailExists(string $email, ?int $excludeId = null): bool
    {
        $builder = $this->customersModel->where('us_email', $email);
        if ($excludeId !== null && $excludeId > 0) {
            $builder = $builder->where('id !=', $excludeId);
        }

        return $builder->first() !== null;
    }

    private function buildCustomerPayload(int $id, string $name, string $email, string $phone, string $updated, ?string $imageFile = null): array
    {
        $image = trim((string) ($imageFile ?? ''));
        if ($image === '') {
            $image = self::DEFAULT_IMAGE_FILE;
        }

        return [
            'id' => $id,
            'customer_id' => 'CU' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => 'Customer',
            'updated' => $updated,
            'image_file' => $image,
            'image_url' => $this->customerImageUrl($image),
        ];
    }

    private function uploadCustomerImage(): array
    {
        $image = $this->request->getFile('customer_image');
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
                'message' => 'Valid customer image is required.',
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
                'message' => 'Unable to create customer upload directory.',
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
        $image = $this->request->getFile('customer_image');
        if (!$image) {
            return false;
        }

        return $image->getError() !== UPLOAD_ERR_NO_FILE;
    }

    private function deleteCustomerImage(string $fileName): void
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

    private function customerImageUrl(string $fileName): string
    {
        $fileName = trim($fileName);
        if ($fileName === '') {
            $fileName = self::DEFAULT_IMAGE_FILE;
        }

        $path = FCPATH . 'uploads/customers/' . $fileName;
        if (!is_file($path)) {
            $fileName = self::DEFAULT_IMAGE_FILE;
        }

        return base_url('uploads/customers/' . $fileName);
    }

}
