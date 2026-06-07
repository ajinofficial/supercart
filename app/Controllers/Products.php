<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BrandsModel;
use App\Models\CategoriesModel;
use App\Models\ProductsModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Products extends BaseController
{
    protected ProductsModel $productsModel;
    protected CategoriesModel $categoriesModel;
    protected BrandsModel $brandsModel;

    public function __construct()
    {
        $this->productsModel = new ProductsModel();
        $this->categoriesModel = new CategoriesModel();
        $this->brandsModel = new BrandsModel();
    }

    public function product_view()
    {
        $data['page'] = 'products';
        $data['products'] = [];
        $data['categories'] = [];
        $data['brands'] = [];

        try {
            $db = db_connect();

            if ($db->tableExists('products')) {
                $data['products'] = $this->productsModel->orderBy('id', 'DESC')->findAll();
            }

            if ($db->tableExists('categories')) {
                $data['categories'] = $this->categoriesModel
                    ->select('id, ct_name')
                    ->orderBy('ct_name', 'ASC')
                    ->findAll();
            }

            if ($db->tableExists('brands')) {
                $data['brands'] = $this->brandsModel
                    ->select('id, br_name')
                    ->orderBy('br_name', 'ASC')
                    ->findAll();
            }

            $categoryMap = [];
            foreach ($data['categories'] as $category) {
                $categoryId = (int) ($category['id'] ?? 0);
                $categoryName = trim((string) ($category['ct_name'] ?? ''));
                if ($categoryId > 0 && $categoryName !== '') {
                    $categoryMap[$categoryId] = $categoryName;
                }
            }

            $brandMap = [];
            $brandNameToIdMap = [];
            foreach ($data['brands'] as $brand) {
                $brandId = (int) ($brand['id'] ?? 0);
                $brandName = trim((string) ($brand['br_name'] ?? ''));
                if ($brandId > 0 && $brandName !== '') {
                    $brandMap[$brandId] = $brandName;
                    $brandNameToIdMap[$brandName] = $brandId;
                }
            }

            foreach ($data['products'] as &$product) {
                $storedCategory = trim((string) ($product['pr_category'] ?? ''));
                $categoryId = ctype_digit($storedCategory) ? (int) $storedCategory : 0;
                $categoryName = $storedCategory;

                if ($categoryId > 0 && isset($categoryMap[$categoryId])) {
                    $categoryName = $categoryMap[$categoryId];
                }

                $storedBrand = trim((string) ($product['pr_brand'] ?? ''));
                $brandId = ctype_digit($storedBrand) ? (int) $storedBrand : 0;
                $brandName = $storedBrand;
                if ($brandId > 0 && isset($brandMap[$brandId])) {
                    $brandName = $brandMap[$brandId];
                } elseif ($brandId === 0 && isset($brandNameToIdMap[$storedBrand])) {
                    $brandId = (int) $brandNameToIdMap[$storedBrand];
                }

                $product['category_id'] = $categoryId;
                $product['category_name'] = $categoryName;
                $product['brand_id'] = $brandId;
                $product['brand_name'] = $brandName;
            }
            unset($product);
        } catch (Throwable $e) {
            $data['products'] = [];
            $data['categories'] = [];
            $data['brands'] = [];
        }

        return view('admin/products', $data);
    }

    public function addProduct(): ResponseInterface
    {
        try {
            $db = db_connect();

            if (!$db->tableExists('categories')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Categories table does not exist.',
                ]);
            }

            if (!$db->tableExists('brands')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Brands table does not exist.',
                ]);
            }

            if (!$this->validate($this->getCommonRules())) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            if (!$db->tableExists('products')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Products table does not exist.',
                ]);
            }

            if (!$db->fieldExists('pr_image', 'products')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'pr_image column is missing. Run migrations.',
                ]);
            }

            if (!$db->fieldExists('pr_discount', 'products')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'pr_discount column is missing. Run migrations.',
                ]);
            }

            if (!$db->fieldExists('pr_description', 'products')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'pr_description column is missing. Run migrations.',
                ]);
            }

            if (!$db->fieldExists('pr_options', 'products')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'pr_options column is missing. Run migrations.',
                ]);
            }

            $imageUpload = $this->uploadProductImage(true);

            if (!$imageUpload['status']) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'product_image' => $imageUpload['message'],
                    ],
                ]);
            }

            $insertData = [
                'pr_name'        => trim((string) $this->request->getPost('product_name')),
                'pr_description' => trim((string) $this->request->getPost('description')),
                'pr_image'       => $imageUpload['file_name'],
                'pr_category'    => (string) ((int) $this->request->getPost('category')),
                'pr_brand'       => (string) ((int) $this->request->getPost('brand')),
                'pr_stock'       => (int) $this->request->getPost('stock'),
                'pr_price'       => (float) $this->request->getPost('price'),
                'pr_discount'    => $this->parseDiscount($this->request->getPost('discount')),
                'pr_options'     => $this->encodeProductOptions($this->request->getPost('options')),
                'pr_status'      => (int) $this->request->getPost('status'),
            ];

            if (!$this->productsModel->insert($insertData)) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Unable to add product.',
                ]);
            }

            $insertId = (int) $this->productsModel->getInsertID();
            $categoryId = (int) $insertData['pr_category'];
            $brandId = (int) $insertData['pr_brand'];
            $this->syncCategoryProductCount($categoryId);
            $this->syncBrandProductCount($brandId);
            $categoryName = $this->getCategoryNameById($categoryId);
            $brandName = $this->getBrandNameById($brandId);
            $now = date('d-m-Y');

            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Product added successfully.',
                'product' => [
                    'id'           => $insertId,
                    'product_id'   => 'P' . str_pad((string) $insertId, 4, '0', STR_PAD_LEFT),
                    'product_name' => $insertData['pr_name'],
                    'description'  => $insertData['pr_description'],
                    'image_url'    => base_url('uploads/products/' . $insertData['pr_image']),
                    'category_id'  => $categoryId,
                    'category'     => $categoryName,
                    'brand_id'     => $brandId,
                    'brand'        => $brandName,
                    'stock'        => $insertData['pr_stock'],
                    'price'        => number_format((float) $insertData['pr_price'], 2),
                    'discount'     => number_format((float) $insertData['pr_discount'], 2, '.', ''),
                    'discount_text' => $this->getDiscountText((float) $insertData['pr_discount']),
                    'options'      => $this->decodeProductOptions((string) $insertData['pr_options']),
                    'options_json' => $insertData['pr_options'],
                    'status'       => $insertData['pr_status'],
                    'status_text'  => $this->getStatusText($insertData['pr_status']),
                    'updated'      => $now,
                ],
            ]);
        } catch (Throwable $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status'  => false,
                    'message' => $e->getMessage(),
                ]);
        }
    }

    public function updateProduct(): ResponseInterface
    {
        try {
            $db = db_connect();

            if (!$db->tableExists('categories')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Categories table does not exist.',
                ]);
            }

            if (!$db->tableExists('brands')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Brands table does not exist.',
                ]);
            }

            if (!$db->tableExists('products')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Products table does not exist.',
                ]);
            }

            if (!$db->fieldExists('pr_discount', 'products')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'pr_discount column is missing. Run migrations.',
                ]);
            }

            if (!$db->fieldExists('pr_description', 'products')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'pr_description column is missing. Run migrations.',
                ]);
            }

            if (!$db->fieldExists('pr_options', 'products')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'pr_options column is missing. Run migrations.',
                ]);
            }

            if (!$this->validate($this->getCommonRules())) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $id = (int) $this->request->getPost('id');
            if ($id <= 0) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid product id.',
                ]);
            }

            $product = $this->productsModel->find($id);
            if (!$product) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Product not found.',
                ]);
            }

            $imageUpload = $this->uploadProductImage(false);
            if (!$imageUpload['status']) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'product_image' => $imageUpload['message'],
                    ],
                ]);
            }

            $imageFile = $product['pr_image'] ?? '';
            $previousCategoryValue = trim((string) ($product['pr_category'] ?? ''));
            $previousCategoryId = ctype_digit($previousCategoryValue) ? (int) $previousCategoryValue : 0;
            $previousBrandValue = trim((string) ($product['pr_brand'] ?? ''));
            $previousBrandId = $this->getBrandIdFromStoredValue($previousBrandValue);
            if (!empty($imageUpload['file_name'])) {
                $imageFile = $imageUpload['file_name'];
                $this->deleteProductImage((string) ($product['pr_image'] ?? ''));
            }

            $updateData = [
                'pr_name'        => trim((string) $this->request->getPost('product_name')),
                'pr_description' => trim((string) $this->request->getPost('description')),
                'pr_image'       => $imageFile,
                'pr_category'    => (string) ((int) $this->request->getPost('category')),
                'pr_brand'       => (string) ((int) $this->request->getPost('brand')),
                'pr_stock'       => (int) $this->request->getPost('stock'),
                'pr_price'       => (float) $this->request->getPost('price'),
                'pr_discount'    => $this->parseDiscount($this->request->getPost('discount')),
                'pr_options'     => $this->encodeProductOptions($this->request->getPost('options')),
                'pr_status'      => (int) $this->request->getPost('status'),
            ];

            if (!$this->productsModel->update($id, $updateData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to update product.',
                ]);
            }

            $categoryId = (int) $updateData['pr_category'];
            $brandId = (int) $updateData['pr_brand'];
            $this->syncCategoryProductCount($categoryId);
            if ($previousCategoryId > 0 && $previousCategoryId !== $categoryId) {
                $this->syncCategoryProductCount($previousCategoryId);
            }
            $this->syncBrandProductCount($brandId);
            if ($previousBrandId > 0 && $previousBrandId !== $brandId) {
                $this->syncBrandProductCount($previousBrandId);
            }
            $categoryName = $this->getCategoryNameById($categoryId);
            $brandName = $this->getBrandNameById($brandId);

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Product updated successfully.',
                'product' => [
                    'id'           => $id,
                    'product_id'   => 'P' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
                    'product_name' => $updateData['pr_name'],
                    'description'  => $updateData['pr_description'],
                    'image_url'    => !empty($updateData['pr_image']) ? base_url('uploads/products/' . $updateData['pr_image']) : '',
                    'category_id'  => $categoryId,
                    'category'     => $categoryName,
                    'brand_id'     => $brandId,
                    'brand'        => $brandName,
                    'stock'        => $updateData['pr_stock'],
                    'price'        => number_format((float) $updateData['pr_price'], 2),
                    'discount'     => number_format((float) $updateData['pr_discount'], 2, '.', ''),
                    'discount_text' => $this->getDiscountText((float) $updateData['pr_discount']),
                    'options'      => $this->decodeProductOptions((string) $updateData['pr_options']),
                    'options_json' => $updateData['pr_options'],
                    'status'       => $updateData['pr_status'],
                    'status_text'  => $this->getStatusText($updateData['pr_status']),
                    'updated'      => date('d-m-Y'),
                ],
            ]);
        } catch (Throwable $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status'  => false,
                    'message' => $e->getMessage(),
                ]);
        }
    }

    public function bulkDeleteProducts(): ResponseInterface
    {
        try {
            $db = db_connect();

            if (!$db->tableExists('products')) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Products table does not exist.',
                ]);
            }

            $ids = $this->request->getPost('ids');
            if (!is_array($ids)) {
                $ids = [];
            }

            $ids = array_values(array_unique(array_filter(array_map(static function ($id): int {
                return (int) $id;
            }, $ids), static function (int $id): bool {
                return $id > 0;
            })));

            if (empty($ids)) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Select at least one product.',
                ]);
            }

            $products = $this->productsModel->whereIn('id', $ids)->findAll();
            if (empty($products)) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Selected products were not found.',
                ]);
            }

            $activeProducts = array_filter($products, static function (array $product): bool {
                return (int) ($product['pr_status'] ?? 1) === 1;
            });

            if (!empty($activeProducts)) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => count($activeProducts) === 1
                        ? 'Active product cannot be deleted. Change status before deleting.'
                        : 'Active products cannot be deleted. Change their status before deleting.',
                    'type'    => 'warning',
                ]);
            }

            $categoryIds = [];
            $brandIds = [];
            foreach ($products as $product) {
                $categoryValue = trim((string) ($product['pr_category'] ?? ''));
                if (ctype_digit($categoryValue)) {
                    $categoryIds[] = (int) $categoryValue;
                }

                $brandIds[] = $this->getBrandIdFromStoredValue((string) ($product['pr_brand'] ?? ''));
            }

            $deleted = $this->productsModel->whereIn('id', $ids)->delete();
            if (!$deleted) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Unable to delete selected products.',
                ]);
            }

            foreach ($products as $product) {
                $this->deleteProductImage((string) ($product['pr_image'] ?? ''));
            }

            foreach (array_unique(array_filter($categoryIds)) as $categoryId) {
                $this->syncCategoryProductCount((int) $categoryId);
            }

            foreach (array_unique(array_filter($brandIds)) as $brandId) {
                $this->syncBrandProductCount((int) $brandId);
            }

            $deletedIds = array_map(static function (array $product): int {
                return (int) ($product['id'] ?? 0);
            }, $products);

            return $this->response->setJSON([
                'status'      => true,
                'message'     => count($deletedIds) === 1
                    ? 'Product deleted successfully.'
                    : count($deletedIds) . ' products deleted successfully.',
                'deleted_ids' => $deletedIds,
            ]);
        } catch (Throwable $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status'  => false,
                    'message' => $e->getMessage(),
                ]);
        }
    }

    private function getCommonRules(): array
    {
        return [
            'product_name' => 'required|min_length[2]|max_length[120]',
            'description'  => 'permit_empty|max_length[500]',
            'category'     => 'required|is_natural_no_zero|is_not_unique[categories.id]',
            'brand'        => 'required|is_natural_no_zero|is_not_unique[brands.id]',
            'stock'        => 'required|integer|greater_than_equal_to[0]',
            'price'        => 'required|decimal|greater_than[0]',
            'discount'     => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'status'       => 'required|in_list[1,2,3]',
        ];
    }

    private function parseDiscount(mixed $value): float
    {
        if ($value === null || trim((string) $value) === '') {
            return 0.0;
        }

        $discount = (float) $value;
        if ($discount < 0) {
            return 0.0;
        }

        if ($discount > 100) {
            return 100.0;
        }

        return $discount;
    }

    private function encodeProductOptions(mixed $options): string
    {
        if (!is_array($options)) {
            return '[]';
        }

        $normalized = [];
        foreach ($options as $option) {
            if (!is_array($option)) {
                continue;
            }

            $name = trim((string) ($option['name'] ?? ''));
            $value = trim((string) ($option['value'] ?? ''));

            if ($name === '' && $value === '') {
                continue;
            }

            if ($name === '' || $value === '') {
                continue;
            }

            $normalized[] = [
                'name'  => substr($name, 0, 80),
                'value' => substr($value, 0, 160),
            ];
        }

        if (empty($normalized)) {
            return '[]';
        }

        return json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]';
    }

    private function decodeProductOptions(string $optionsJson): array
    {
        $decoded = json_decode($optionsJson, true);
        if (!is_array($decoded)) {
            return [];
        }

        $options = [];
        foreach ($decoded as $option) {
            if (!is_array($option)) {
                continue;
            }

            $name = trim((string) ($option['name'] ?? ''));
            $value = trim((string) ($option['value'] ?? ''));
            if ($name === '' || $value === '') {
                continue;
            }

            $options[] = [
                'name'  => $name,
                'value' => $value,
            ];
        }

        return $options;
    }

    private function getCategoryNameById(int $categoryId): string
    {
        if ($categoryId <= 0) {
            return '';
        }

        $category = $this->categoriesModel->find($categoryId);
        return trim((string) ($category['ct_name'] ?? ''));
    }

    private function syncCategoryProductCount(int $categoryId): void
    {
        if ($categoryId <= 0) {
            return;
        }

        if (!$this->categoriesModel->find($categoryId)) {
            return;
        }

        $productCount = $this->productsModel
            ->where('pr_category', (string) $categoryId)
            ->countAllResults();

        $this->categoriesModel->update($categoryId, [
            'ct_products' => (int) $productCount,
        ]);
    }

    private function getBrandNameById(int $brandId): string
    {
        if ($brandId <= 0) {
            return '';
        }

        $brand = $this->brandsModel->find($brandId);
        return trim((string) ($brand['br_name'] ?? ''));
    }

    private function syncBrandProductCount(int $brandId): void
    {
        if ($brandId <= 0) {
            return;
        }

        if (!$this->brandsModel->find($brandId)) {
            return;
        }

        $productCount = $this->productsModel
            ->where('pr_brand', (string) $brandId)
            ->countAllResults();

        $this->brandsModel->update($brandId, [
            'br_products' => (int) $productCount,
        ]);
    }

    private function getBrandIdFromStoredValue(string $storedValue): int
    {
        $value = trim($storedValue);
        if ($value === '') {
            return 0;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        $brand = $this->brandsModel->where('br_name', $value)->first();
        return (int) ($brand['id'] ?? 0);
    }

    private function uploadProductImage(bool $required): array
    {
        $image = $this->request->getFile('product_image');

        if (!$image || $image->getError() === UPLOAD_ERR_NO_FILE) {
            if ($required) {
                return [
                    'status' => false,
                    'message' => 'Product image is required.',
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
                'message' => 'Valid product image is required.',
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

        $uploadDir = FCPATH . 'uploads/products';

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

    private function deleteProductImage(string $fileName): void
    {
        if ($fileName === '') {
            return;
        }

        $path = FCPATH . 'uploads/products/' . $fileName;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function getStatusText(int $status): string
    {
        return match ($status) {
            2 => 'Low Stock',
            3 => 'Out of Stock',
            default => 'Active',
        };
    }

    private function getDiscountText(float $discount): string
    {
        if ($discount <= 0) {
            return '-';
        }

        return rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.') . '%';
    }
}
