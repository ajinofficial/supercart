<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BrandsModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Brands extends BaseController
{
    protected BrandsModel $brandsModel;

    public function __construct()
    {
        $this->brandsModel = new BrandsModel();
    }

    public function brands_view()
    {
        $data['page'] = 'brands';
        $data['brands'] = [];

        try {
            $db = db_connect();
            if ($db->tableExists('brands')) {
                $data['brands'] = $this->brandsModel->orderBy('id', 'DESC')->findAll();
            }
        } catch (Throwable $e) {
            $data['brands'] = [];
        }

        return view('admin/brands', $data);
    }

    public function addBrand(): ResponseInterface
    {
        try {
            if (!$this->validate($this->getCommonRules())) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $db = db_connect();
            if (!$db->tableExists('brands')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Brands table does not exist.',
                ]);
            }

            $insertData = [
                'br_name'   => trim((string) $this->request->getPost('brand_name')),
                'br_products' => 0,
                'br_status' => (int) $this->request->getPost('status'),
            ];

            if (!$this->brandsModel->insert($insertData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to add brand.',
                ]);
            }

            $insertId = (int) $this->brandsModel->getInsertID();

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Brand added successfully.',
                'brand' => [
                    'id'          => $insertId,
                    'brand_id'    => 'B' . str_pad((string) $insertId, 4, '0', STR_PAD_LEFT),
                    'brand_name'  => $insertData['br_name'],
                    'products'    => 0,
                    'status'      => $insertData['br_status'],
                    'status_text' => $this->getStatusText($insertData['br_status']),
                    'updated'     => date('d-m-Y'),
                ],
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

    public function updateBrand(): ResponseInterface
    {
        try {
            $rules = $this->getCommonRules();
            $rules['id'] = 'required|is_natural_no_zero';

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $db = db_connect();
            if (!$db->tableExists('brands')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Brands table does not exist.',
                ]);
            }

            $id = (int) $this->request->getPost('id');
            $existingBrand = $this->brandsModel->find($id);
            if (!$existingBrand) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Brand not found.',
                ]);
            }

            $updateData = [
                'br_name'   => trim((string) $this->request->getPost('brand_name')),
                'br_status' => (int) $this->request->getPost('status'),
            ];

            if (!$this->brandsModel->update($id, $updateData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to update brand.',
                ]);
            }

            $productsCount = (int) ($existingBrand['br_products'] ?? 0);

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Brand updated successfully.',
                'brand' => [
                    'id'          => $id,
                    'brand_id'    => 'B' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
                    'brand_name'  => $updateData['br_name'],
                    'products'    => $productsCount,
                    'status'      => $updateData['br_status'],
                    'status_text' => $this->getStatusText($updateData['br_status']),
                    'updated'     => date('d-m-Y'),
                ],
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
            'brand_name' => 'required|min_length[2]|max_length[100]',
            'status'     => 'required|in_list[1,2,3]',
        ];
    }

    private function getStatusText(int $status): string
    {
        return match ($status) {
            2 => 'In Review',
            3 => 'Inactive',
            default => 'Active',
        };
    }
}
