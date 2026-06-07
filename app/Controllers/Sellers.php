<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SellersModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Sellers extends BaseController
{
    private const SELLER_ROLE_ID = 3;
    private const DEFAULT_IMAGE_FILE = 'default-user.svg';

    protected SellersModel $sellersModel;

    public function __construct()
    {
        $this->sellersModel = new SellersModel();
    }

    public function sellers_view()
    {
        $data['page'] = 'sellers';
        $data['sellers'] = [];

        try {
            $db = db_connect();
            if ($db->tableExists('users')) {
                $data['sellers'] = $this->sellersModel
                    ->where('us_role_id', self::SELLER_ROLE_ID)
                    ->orderBy('id', 'DESC')
                    ->findAll();
            }
        } catch (Throwable $e) {
            $data['sellers'] = [];
        }

        return view('admin/sellers', $data);
    }

    public function addSeller(): ResponseInterface
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
                $imageUpload = $this->uploadSellerImage();
            } elseif ($this->hasIncomingImageFile()) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'seller_image' => 'Seller image column is missing. Run migrations.',
                    ],
                ]);
            }

            if (!$imageUpload['status']) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'seller_image' => $imageUpload['message'],
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
                'us_role_id' => self::SELLER_ROLE_ID,
                'us_password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            ];
            if ($hasImageColumn) {
                $insertData['us_image'] = $imageUpload['file_name'] !== '' ? $imageUpload['file_name'] : self::DEFAULT_IMAGE_FILE;
            }

            if (!$this->sellersModel->insert($insertData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to add seller.',
                ]);
            }

            $insertId = (int) $this->sellersModel->getInsertID();

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Seller added successfully.',
                'seller' => $this->buildSellerPayload(
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

    public function updateSeller(): ResponseInterface
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
                $imageUpload = $this->uploadSellerImage();
            } elseif ($this->hasIncomingImageFile()) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'seller_image' => 'Seller image column is missing. Run migrations.',
                    ],
                ]);
            }

            if (!$imageUpload['status']) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'seller_image' => $imageUpload['message'],
                    ],
                ]);
            }

            $id = (int) $this->request->getPost('id');
            $existing = $this->sellersModel
                ->where('id', $id)
                ->where('us_role_id', self::SELLER_ROLE_ID)
                ->first();

            if (!$existing) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Seller not found.',
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
                $this->deleteSellerImage($oldFile);
            }

            if (!$this->sellersModel->update($id, $updateData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to update seller.',
                ]);
            }

            $imageFile = self::DEFAULT_IMAGE_FILE;
            if ($hasImageColumn) {
                $imageFile = (string) ($updateData['us_image'] ?? ($existing['us_image'] ?? self::DEFAULT_IMAGE_FILE));
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Seller updated successfully.',
                'seller' => $this->buildSellerPayload(
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
        $builder = $this->sellersModel->where('us_email', $email);
        if ($excludeId !== null && $excludeId > 0) {
            $builder = $builder->where('id !=', $excludeId);
        }

        return $builder->first() !== null;
    }

    private function buildSellerPayload(int $id, string $name, string $email, string $phone, string $updated, ?string $imageFile = null): array
    {
        $image = trim((string) ($imageFile ?? ''));
        if ($image === '') {
            $image = self::DEFAULT_IMAGE_FILE;
        }

        return [
            'id' => $id,
            'seller_id' => 'SL' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => 'Seller',
            'updated' => $updated,
            'image_file' => $image,
            'image_url' => $this->sellerImageUrl($image),
        ];
    }

    private function uploadSellerImage(): array
    {
        $image = $this->request->getFile('seller_image');
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
                'message' => 'Valid seller image is required.',
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
                'message' => 'Unable to create seller upload directory.',
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
        $image = $this->request->getFile('seller_image');
        if (!$image) {
            return false;
        }

        return $image->getError() !== UPLOAD_ERR_NO_FILE;
    }

    private function deleteSellerImage(string $fileName): void
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

    private function sellerImageUrl(string $fileName): string
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
