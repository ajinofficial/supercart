<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CouponsModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Coupons extends BaseController
{
    protected CouponsModel $couponsModel;

    public function __construct()
    {
        $this->couponsModel = new CouponsModel();
    }

    public function coupons_view()
    {
        $data['page'] = 'coupons';
        $data['coupons'] = [];

        try {
            $db = db_connect();
            if ($db->tableExists('coupons')) {
                $data['coupons'] = $this->couponsModel->orderBy('id', 'DESC')->findAll();
            }
        } catch (Throwable $e) {
            $data['coupons'] = [];
        }

        return view('admin/coupons', $data);
    }

    public function addCoupon(): ResponseInterface
    {
        try {
            $db = db_connect();
            if (!$db->tableExists('coupons')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Coupons table does not exist.',
                ]);
            }

            if (!$this->validate($this->getCommonRules())) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $code = $this->normalizedCode((string) $this->request->getPost('coupon_code'));
            if ($this->couponCodeExists($code)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'coupon_code' => 'Coupon code already exists.',
                    ],
                ]);
            }

            $dateValidation = $this->validateDates(
                (string) $this->request->getPost('start_date'),
                (string) $this->request->getPost('end_date')
            );
            if (!$dateValidation['status']) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $dateValidation['errors'],
                ]);
            }

            $insertData = $this->buildCouponDataFromRequest($code);

            if (!$this->couponsModel->insert($insertData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to add coupon.',
                ]);
            }

            $insertId = (int) $this->couponsModel->getInsertID();

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Coupon added successfully.',
                'coupon' => $this->buildCouponPayload($insertId, $insertData, date('d-m-Y')),
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updateCoupon(): ResponseInterface
    {
        try {
            $db = db_connect();
            if (!$db->tableExists('coupons')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Coupons table does not exist.',
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
            $existing = $this->couponsModel->find($id);
            if (!$existing) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Coupon not found.',
                ]);
            }

            $code = $this->normalizedCode((string) $this->request->getPost('coupon_code'));
            if ($this->couponCodeExists($code, $id)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => [
                        'coupon_code' => 'Coupon code already exists.',
                    ],
                ]);
            }

            $dateValidation = $this->validateDates(
                (string) $this->request->getPost('start_date'),
                (string) $this->request->getPost('end_date')
            );
            if (!$dateValidation['status']) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $dateValidation['errors'],
                ]);
            }

            $updateData = $this->buildCouponDataFromRequest($code);
            $updateData['cp_used_count'] = (int) ($existing['cp_used_count'] ?? 0);

            if (!$this->couponsModel->update($id, $updateData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to update coupon.',
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Coupon updated successfully.',
                'coupon' => $this->buildCouponPayload($id, $updateData, date('d-m-Y')),
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function getCommonRules(): array
    {
        return [
            'coupon_title' => 'required|min_length[2]|max_length[120]',
            'coupon_code' => 'required|min_length[3]|max_length[40]|regex_match[/^[A-Za-z0-9_-]+$/]',
            'coupon_type' => 'required|in_list[1,2]',
            'coupon_value' => 'required|decimal|greater_than[0]',
            'min_order' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'max_discount' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'start_date' => 'permit_empty|valid_date[Y-m-d]',
            'end_date' => 'permit_empty|valid_date[Y-m-d]',
            'usage_limit' => 'permit_empty|is_natural_no_zero',
            'status' => 'required|in_list[1,2,3]',
        ];
    }

    private function normalizedCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    private function couponCodeExists(string $code, ?int $excludeId = null): bool
    {
        $builder = $this->couponsModel->where('cp_code', $code);
        if ($excludeId !== null && $excludeId > 0) {
            $builder = $builder->where('id !=', $excludeId);
        }

        return $builder->first() !== null;
    }

    private function validateDates(string $startDate, string $endDate): array
    {
        $startDate = trim($startDate);
        $endDate = trim($endDate);

        if ($startDate !== '' && $endDate !== '' && strtotime($endDate) < strtotime($startDate)) {
            return [
                'status' => false,
                'errors' => [
                    'end_date' => 'End date must be after start date.',
                ],
            ];
        }

        return ['status' => true, 'errors' => []];
    }

    private function buildCouponDataFromRequest(string $code): array
    {
        $type = (int) $this->request->getPost('coupon_type');
        $maxDiscount = trim((string) $this->request->getPost('max_discount'));
        if ($type === 2) {
            $maxDiscount = '';
        }

        return [
            'cp_title' => trim((string) $this->request->getPost('coupon_title')),
            'cp_code' => $code,
            'cp_type' => $type,
            'cp_value' => number_format((float) $this->request->getPost('coupon_value'), 2, '.', ''),
            'cp_min_order' => number_format((float) ($this->request->getPost('min_order') ?: 0), 2, '.', ''),
            'cp_max_discount' => $maxDiscount === '' ? null : number_format((float) $maxDiscount, 2, '.', ''),
            'cp_start_date' => trim((string) $this->request->getPost('start_date')) ?: null,
            'cp_end_date' => trim((string) $this->request->getPost('end_date')) ?: null,
            'cp_usage_limit' => trim((string) $this->request->getPost('usage_limit')) !== ''
                ? (int) $this->request->getPost('usage_limit')
                : null,
            'cp_status' => (int) $this->request->getPost('status'),
        ];
    }

    private function getTypeText(int $type): string
    {
        return $type === 2 ? 'Fixed' : 'Percentage';
    }

    private function getStatusText(int $status): string
    {
        return match ($status) {
            2 => 'Draft',
            3 => 'Inactive',
            default => 'Active',
        };
    }

    private function buildCouponPayload(int $id, array $data, string $updated): array
    {
        $type = (int) $data['cp_type'];
        $value = (float) $data['cp_value'];
        $usedCount = (int) ($data['cp_used_count'] ?? 0);
        $usageLimit = isset($data['cp_usage_limit']) ? (int) $data['cp_usage_limit'] : null;
        $maxDiscount = isset($data['cp_max_discount']) && $data['cp_max_discount'] !== null
            ? (float) $data['cp_max_discount']
            : null;

        $valueText = $type === 2
            ? 'Rs ' . number_format($value, 2)
            : rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . '%';

        if ($type === 1 && $maxDiscount !== null && $maxDiscount > 0) {
            $valueText .= ' (Max Rs ' . number_format($maxDiscount, 2) . ')';
        }

        $validityText = 'Always';
        $startDate = $data['cp_start_date'] ?? null;
        $endDate = $data['cp_end_date'] ?? null;
        if (!empty($startDate) || !empty($endDate)) {
            $startText = !empty($startDate) ? date('d-m-Y', strtotime((string) $startDate)) : 'Now';
            $endText = !empty($endDate) ? date('d-m-Y', strtotime((string) $endDate)) : 'No end';
            $validityText = $startText . ' - ' . $endText;
        }

        return [
            'id' => $id,
            'coupon_id' => 'CP' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'title' => (string) $data['cp_title'],
            'code' => (string) $data['cp_code'],
            'type' => $type,
            'type_text' => $this->getTypeText($type),
            'value' => number_format($value, 2, '.', ''),
            'value_text' => $valueText,
            'min_order' => number_format((float) $data['cp_min_order'], 2, '.', ''),
            'usage_limit' => $usageLimit,
            'used_count' => $usedCount,
            'usage_text' => $usageLimit === null ? ($usedCount . ' / Unlimited') : ($usedCount . ' / ' . $usageLimit),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'validity_text' => $validityText,
            'max_discount' => $maxDiscount,
            'status' => (int) $data['cp_status'],
            'status_text' => $this->getStatusText((int) $data['cp_status']),
            'updated' => $updated,
        ];
    }
}
