<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CategoriesModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Categories extends BaseController
{
    protected CategoriesModel $categoriesModel;

    public function __construct()
    {
        $this->categoriesModel = new CategoriesModel();
    }

    public function categories_view()
    {
        $data['page'] = 'categories';
        $data['categories'] = [];

        try {
            $db = db_connect();
            if ($db->tableExists('categories')) {
                $data['categories'] = $this->categoriesModel->orderBy('id', 'DESC')->findAll();
            }
        } catch (Throwable $e) {
            $data['categories'] = [];
        }

        return view('admin/categories', $data);
    }

    public function addCategory(): ResponseInterface
    {
        try {
            $rules = [
                'category_name' => 'required|min_length[2]|max_length[100]',
                'status'        => 'required|in_list[1,2,3]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $db = db_connect();
            if (!$db->tableExists('categories')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Categories table does not exist.',
                ]);
            }

            $insertData = [
                'ct_name'     => trim((string) $this->request->getPost('category_name')),
                'ct_products' => 0,
                'ct_status'   => (int) $this->request->getPost('status'),
            ];

            if (!$this->categoriesModel->insert($insertData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to add category.',
                ]);
            }

            $insertId = (int) $this->categoriesModel->getInsertID();

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Category added successfully.',
                'category' => [
                    'id'            => $insertId,
                    'category_id'   => 'C' . str_pad((string) $insertId, 4, '0', STR_PAD_LEFT),
                    'category_name' => $insertData['ct_name'],
                    'products'      => $insertData['ct_products'],
                    'status'        => $insertData['ct_status'],
                    'status_text'   => $this->getStatusText($insertData['ct_status']),
                    'updated'       => date('d-m-Y'),
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

    public function updateCategory(): ResponseInterface
    {
        try {
            $rules = [
                'id'            => 'required|is_natural_no_zero',
                'category_name' => 'required|min_length[2]|max_length[100]',
                'status'        => 'required|in_list[1,2,3]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $db = db_connect();
            if (!$db->tableExists('categories')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Categories table does not exist.',
                ]);
            }

            $id = (int) $this->request->getPost('id');
            $existingCategory = $this->categoriesModel->find($id);
            if (!$existingCategory) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Category not found.',
                ]);
            }

            $updateData = [
                'ct_name'   => trim((string) $this->request->getPost('category_name')),
                'ct_status' => (int) $this->request->getPost('status'),
            ];

            if (!$this->categoriesModel->update($id, $updateData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to update category.',
                ]);
            }

            $productsCount = (int) ($existingCategory['ct_products'] ?? 0);

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Category updated successfully.',
                'category' => [
                    'id'            => $id,
                    'category_id'   => 'C' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
                    'category_name' => $updateData['ct_name'],
                    'products'      => $productsCount,
                    'status'        => $updateData['ct_status'],
                    'status_text'   => $this->getStatusText($updateData['ct_status']),
                    'updated'       => date('d-m-Y'),
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

    private function getStatusText(int $status): string
    {
        return match ($status) {
            2 => 'In Review',
            3 => 'Inactive',
            default => 'Active',
        };
    }
}
