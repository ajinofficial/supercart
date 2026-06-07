<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BannersModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Banners extends BaseController
{
    protected BannersModel $bannersModel;

    public function __construct()
    {
        $this->bannersModel = new BannersModel();
    }

    public function banners_view()
    {
        $data['page'] = 'banners';
        $data['banners'] = [];

        try {
            $db = db_connect();
            if ($db->tableExists('banners')) {
                $data['banners'] = $this->bannersModel->orderBy('id', 'DESC')->findAll();
            }
        } catch (Throwable $e) {
            $data['banners'] = [];
        }

        return view('admin/banners', $data);
    }

    public function addBanner(): ResponseInterface
    {
        try {
            $db = db_connect();
            if (!$db->tableExists('banners')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Banners table does not exist.',
                ]);
            }

            if (!$this->validate($this->getCommonRules())) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $imageUpload = $this->uploadBannerImage(true);
            if (!$imageUpload['status']) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'banner_image' => $imageUpload['message'],
                    ],
                ]);
            }

            $insertData = [
                'bn_title' => trim((string) $this->request->getPost('banner_title')),
                'bn_image' => $imageUpload['file_name'],
                'bn_link' => trim((string) $this->request->getPost('banner_link')),
                'bn_status' => (int) $this->request->getPost('status'),
            ];

            if (!$this->bannersModel->insert($insertData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to add banner.',
                ]);
            }

            $insertId = (int) $this->bannersModel->getInsertID();

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Banner added successfully.',
                'banner' => $this->buildBannerPayload($insertId, $insertData, date('d-m-Y')),
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

    public function updateBanner(): ResponseInterface
    {
        try {
            $db = db_connect();
            if (!$db->tableExists('banners')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Banners table does not exist.',
                ]);
            }

            $rules = $this->getCommonRules();
            $rules['id'] = 'required|is_natural_no_zero';

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $id = (int) $this->request->getPost('id');
            $existing = $this->bannersModel->find($id);
            if (!$existing) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Banner not found.',
                ]);
            }

            $imageUpload = $this->uploadBannerImage(false);
            if (!$imageUpload['status']) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'banner_image' => $imageUpload['message'],
                    ],
                ]);
            }

            $imageFile = (string) ($existing['bn_image'] ?? '');
            if (!empty($imageUpload['file_name'])) {
                $imageFile = $imageUpload['file_name'];
                $this->deleteBannerImage((string) ($existing['bn_image'] ?? ''));
            }

            $updateData = [
                'bn_title' => trim((string) $this->request->getPost('banner_title')),
                'bn_image' => $imageFile,
                'bn_link' => trim((string) $this->request->getPost('banner_link')),
                'bn_status' => (int) $this->request->getPost('status'),
            ];

            if (!$this->bannersModel->update($id, $updateData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to update banner.',
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Banner updated successfully.',
                'banner' => $this->buildBannerPayload($id, $updateData, date('d-m-Y')),
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

    private function getCommonRules(): array
    {
        return [
            'banner_title' => 'required|min_length[2]|max_length[120]',
            'banner_link' => 'permit_empty|max_length[255]',
            'status' => 'required|in_list[1,2,3]',
        ];
    }

    private function getStatusText(int $status): string
    {
        return match ($status) {
            2 => 'Draft',
            3 => 'Inactive',
            default => 'Active',
        };
    }

    private function buildBannerPayload(int $id, array $data, string $updated): array
    {
        return [
            'id' => $id,
            'banner_id' => 'BN' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'title' => $data['bn_title'],
            'image_url' => !empty($data['bn_image']) ? base_url('uploads/banners/' . $data['bn_image']) : '',
            'link' => $data['bn_link'],
            'status' => (int) $data['bn_status'],
            'status_text' => $this->getStatusText((int) $data['bn_status']),
            'updated' => $updated,
        ];
    }

    private function uploadBannerImage(bool $required): array
    {
        $image = $this->request->getFile('banner_image');

        if (!$image || $image->getError() === UPLOAD_ERR_NO_FILE) {
            if ($required) {
                return [
                    'status' => false,
                    'message' => 'Banner image is required.',
                ];
            }

            return [
                'status' => true,
                'file_name' => '',
            ];
        }

        if (!$image->isValid()) {
            return [
                'status' => false,
                'message' => 'Valid banner image is required.',
            ];
        }

        $allowedMime = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array(strtolower((string) $image->getMimeType()), $allowedMime, true)) {
            return [
                'status' => false,
                'message' => 'Only JPG, JPEG, PNG, WEBP allowed.',
            ];
        }

        if ($image->getSizeByUnit('kb') > 2048) {
            return [
                'status' => false,
                'message' => 'Image size must be 2MB or less.',
            ];
        }

        $uploadDir = FCPATH . 'uploads/banners';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return [
                'status' => false,
                'message' => 'Unable to create upload directory.',
            ];
        }

        $newName = $image->getRandomName();
        $image->move($uploadDir, $newName);

        return [
            'status' => true,
            'file_name' => $newName,
        ];
    }

    private function deleteBannerImage(string $fileName): void
    {
        if ($fileName === '') {
            return;
        }

        $path = FCPATH . 'uploads/banners/' . $fileName;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
