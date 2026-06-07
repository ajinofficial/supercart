<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReturnsModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Returns extends BaseController
{
    protected ReturnsModel $returnsModel;

    public function __construct()
    {
        $this->returnsModel = new ReturnsModel();
    }

    public function returns_view()
    {
        $data['page'] = 'returns';
        $data['returns'] = [];

        try {
            $db = db_connect();
            if ($db->tableExists('returns')) {
                $rows = $this->returnsModel->orderBy('id', 'DESC')->findAll();
                $data['returns'] = $this->mapReturnRows($rows);
            }
        } catch (Throwable $e) {
            $data['returns'] = [];
        }

        return view('admin/returns', $data);
    }

    public function updateReturn(): ResponseInterface
    {
        try {
            $db = db_connect();
            if (!$db->tableExists('returns')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Returns table does not exist.',
                ]);
            }

            $rules = [
                'id' => 'required|is_natural_no_zero',
                'status' => 'required|in_list[1,2,3,4,5]',
                'refund_mode' => 'required|min_length[2]|max_length[40]',
                'refund_state' => 'required|in_list[1,2,3,4]',
                'refund_amount' => 'required|decimal',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $id = (int) $this->request->getPost('id');
            $existing = $this->returnsModel->find($id);
            if (!$existing) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Return request not found.',
                ]);
            }

            $refundAmount = (float) $this->request->getPost('refund_amount');
            if ($refundAmount < 0) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Refund amount must be 0 or more.',
                ]);
            }

            $updateData = [
                'rt_status' => (int) $this->request->getPost('status'),
                'rt_refund_mode' => trim((string) $this->request->getPost('refund_mode')),
                'rt_refund_state' => (int) $this->request->getPost('refund_state'),
                'rt_refund_amount' => number_format($refundAmount, 2, '.', ''),
            ];

            if (!$this->returnsModel->update($id, $updateData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to update return request.',
                ]);
            }

            $updated = $this->returnsModel->find($id);
            if (!$updated) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Return request updated but not found.',
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Return request updated successfully.',
                'item' => $this->mapReturnRow($updated),
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function mapReturnRows(array $rows): array
    {
        return array_map(fn(array $row): array => $this->mapReturnRow($row), $rows);
    }

    private function mapReturnRow(array $row): array
    {
        $status = (int) ($row['rt_status'] ?? 1);
        $refundState = (int) ($row['rt_refund_state'] ?? 1);
        $refundAmount = (float) ($row['rt_refund_amount'] ?? 0);
        $requestedOn = (string) ($row['rt_requested_on'] ?? '');

        return [
            'id' => (int) ($row['id'] ?? 0),
            'return_code' => (string) ($row['rt_return_code'] ?? ''),
            'order_code' => (string) ($row['rt_order_code'] ?? ''),
            'customer_name' => (string) ($row['rt_customer_name'] ?? ''),
            'reason' => (string) ($row['rt_reason'] ?? ''),
            'requested_on' => $requestedOn,
            'requested_on_display' => $requestedOn !== '' ? date('d-m-Y', strtotime($requestedOn)) : '',
            'status' => $status,
            'status_text' => $this->statusText($status),
            'status_class' => $this->statusClass($status),
            'refund_amount' => number_format($refundAmount, 2, '.', ''),
            'refund_display' => 'Rs ' . number_format($refundAmount, 2, '.', ''),
            'refund_mode' => (string) ($row['rt_refund_mode'] ?? ''),
            'refund_state' => $refundState,
            'refund_state_text' => $this->refundStateText($refundState),
            'refund_state_class' => $this->refundStateClass($refundState),
        ];
    }

    private function statusText(int $status): string
    {
        return match ($status) {
            2 => 'Picked Up',
            3 => 'In Inspection',
            4 => 'Completed',
            5 => 'Rejected',
            default => 'Requested',
        };
    }

    private function statusClass(int $status): string
    {
        return match ($status) {
            2 => 'status-picked',
            3 => 'status-inspection',
            4 => 'status-completed',
            5 => 'status-rejected',
            default => 'status-requested',
        };
    }

    private function refundStateText(int $state): string
    {
        return match ($state) {
            2 => 'Processing',
            3 => 'Refunded',
            4 => 'Declined',
            default => 'Pending',
        };
    }

    private function refundStateClass(int $state): string
    {
        return match ($state) {
            2 => 'refund-processing',
            3 => 'refund-refunded',
            4 => 'refund-declined',
            default => 'refund-pending',
        };
    }
}
