<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrdersModel;
use App\Models\PaymentsModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Payments extends BaseController
{
    protected PaymentsModel $paymentsModel;
    protected OrdersModel $ordersModel;

    public function __construct()
    {
        $this->paymentsModel = new PaymentsModel();
        $this->ordersModel = new OrdersModel();
    }

    public function payments_view()
    {
        $data['page'] = 'payments';
        $data['payments'] = [];
        $data['methods'] = [];

        try {
            $db = db_connect();
            if ($db->tableExists('payments')) {
                $this->syncPaymentsFromOrders();
                $payments = $this->paymentsModel->orderBy('id', 'DESC')->findAll();
                $data['payments'] = $this->mapPaymentRows($payments);
                $data['methods'] = $this->extractMethods($data['payments']);
            }
        } catch (Throwable $e) {
            $data['payments'] = [];
            $data['methods'] = [];
        }

        return view('admin/payments', $data);
    }

    public function updatePayment(): ResponseInterface
    {
        try {
            $db = db_connect();
            if (!$db->tableExists('payments')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Payments table does not exist.',
                ]);
            }

            $rules = [
                'id' => 'required|is_natural_no_zero',
                'method' => 'required|min_length[2]|max_length[40]',
                'gateway_ref' => 'permit_empty|max_length[60]',
                'amount' => 'required|decimal',
                'paid_on' => 'required|valid_date[Y-m-d]',
                'status' => 'required|in_list[1,2,3,4]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $id = (int) $this->request->getPost('id');
            $existing = $this->paymentsModel->find($id);
            if (!$existing) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Payment transaction not found.',
                ]);
            }

            $amount = (float) $this->request->getPost('amount');
            if ($amount < 0) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Amount must be 0 or greater.',
                ]);
            }

            $updateData = [
                'pm_method' => trim((string) $this->request->getPost('method')),
                'pm_gateway_ref' => trim((string) $this->request->getPost('gateway_ref')),
                'pm_amount' => number_format($amount, 2, '.', ''),
                'pm_paid_on' => trim((string) $this->request->getPost('paid_on')),
                'pm_status' => (int) $this->request->getPost('status'),
            ];

            if (!$this->paymentsModel->update($id, $updateData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to update payment.',
                ]);
            }

            $this->syncOrderStatus((string) ($existing['pm_order_code'] ?? ''), (int) $updateData['pm_status']);

            $updated = $this->paymentsModel->find($id);
            if (!$updated) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Payment updated but not found.',
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Payment updated successfully.',
                'payment' => $this->mapPaymentRow($updated),
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function syncFromOrders(): ResponseInterface
    {
        try {
            $result = $this->syncPaymentsFromOrders();

            return $this->response->setJSON([
                'status' => true,
                'message' => $result['created'] > 0
                    ? $result['created'] . ' payment record(s) created from orders.'
                    : 'Payment records are already up to date.',
                'created' => $result['created'],
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function mapPaymentRows(array $rows): array
    {
        return array_map(fn(array $row): array => $this->mapPaymentRow($row), $rows);
    }

    private function mapPaymentRow(array $row): array
    {
        $status = (int) ($row['pm_status'] ?? 1);
        $amount = (float) ($row['pm_amount'] ?? 0);
        $paidOn = (string) ($row['pm_paid_on'] ?? '');

        return [
            'id' => (int) ($row['id'] ?? 0),
            'transaction_code' => (string) ($row['pm_transaction_code'] ?? ''),
            'order_code' => (string) ($row['pm_order_code'] ?? ''),
            'customer_name' => (string) ($row['pm_customer_name'] ?? ''),
            'method' => (string) ($row['pm_method'] ?? ''),
            'gateway_ref' => (string) ($row['pm_gateway_ref'] ?? ''),
            'amount' => number_format($amount, 2, '.', ''),
            'amount_display' => 'Rs ' . number_format($amount, 2, '.', ''),
            'paid_on' => $paidOn,
            'paid_on_display' => $paidOn !== '' ? date('d-m-Y', strtotime($paidOn)) : '',
            'status' => $status,
            'status_text' => $this->statusText($status),
            'status_class' => $this->statusClass($status),
        ];
    }

    private function extractMethods(array $rows): array
    {
        $methods = [];
        foreach ($rows as $row) {
            $method = trim((string) ($row['method'] ?? ''));
            if ($method !== '') {
                $methods[$method] = $method;
            }
        }

        ksort($methods);
        return array_values($methods);
    }

    private function statusText(int $status): string
    {
        return match ($status) {
            2 => 'Paid',
            3 => 'Failed',
            4 => 'Refunded',
            default => 'Pending',
        };
    }

    private function statusClass(int $status): string
    {
        return match ($status) {
            2 => 'status-paid',
            3 => 'status-failed',
            4 => 'status-refunded',
            default => 'status-pending',
        };
    }

    private function syncPaymentsFromOrders(): array
    {
        $db = db_connect();
        if (!$db->tableExists('payments') || !$db->tableExists('orders')) {
            return ['created' => 0];
        }

        $orders = $this->ordersModel
            ->orderBy('id', 'DESC')
            ->findAll(100);

        $created = 0;

        foreach ($orders as $order) {
            $orderCode = trim((string) ($order['order_code'] ?? ''));
            $orderId = (int) ($order['id'] ?? 0);
            if ($orderCode === '' && $orderId > 0) {
                $orderCode = '#' . $orderId;
            }

            if ($orderCode === '') {
                continue;
            }

            $existing = $this->paymentsModel
                ->where('pm_order_code', $orderCode)
                ->first();
            if ($existing) {
                continue;
            }

            $method = $this->formatPaymentMethod((string) ($order['payment_method'] ?? 'cod'));
            $status = strtolower($method) === 'cod' ? 1 : 2;
            $createdAt = trim((string) ($order['created_at'] ?? ''));

            $inserted = $this->paymentsModel->insert([
                'pm_transaction_code' => $this->buildTransactionCode($orderId),
                'pm_order_code' => $orderCode,
                'pm_customer_name' => trim((string) ($order['customer_name'] ?? 'Customer')),
                'pm_method' => $method,
                'pm_gateway_ref' => strtolower($method) === 'cod' ? 'COD' : '',
                'pm_amount' => number_format((float) ($order['total'] ?? 0), 2, '.', ''),
                'pm_paid_on' => $createdAt !== '' ? date('Y-m-d', strtotime($createdAt)) : date('Y-m-d'),
                'pm_status' => $status,
            ]);

            if ($inserted) {
                $created++;
            }
        }

        return ['created' => $created];
    }

    private function syncOrderStatus(string $orderCode, int $paymentStatus): void
    {
        $orderCode = trim($orderCode);
        if ($orderCode === '') {
            return;
        }

        $db = db_connect();
        if (!$db->tableExists('orders')) {
            return;
        }

        $orderStatus = match ($paymentStatus) {
            3, 4 => 'cancelled',
            default => 'processing',
        };

        $this->ordersModel
            ->where('order_code', $orderCode)
            ->set(['status' => $orderStatus])
            ->update();
    }

    private function buildTransactionCode(int $orderId): string
    {
        $base = $orderId > 0 ? $orderId : random_int(1000, 9999);
        $code = 'TXN-' . str_pad((string) $base, 5, '0', STR_PAD_LEFT);
        $existing = $this->paymentsModel->where('pm_transaction_code', $code)->first();

        if (!$existing) {
            return $code;
        }

        return 'TXN-' . date('ymd') . '-' . random_int(100, 999);
    }

    private function formatPaymentMethod(string $method): string
    {
        $method = strtolower(trim($method));
        return match ($method) {
            'upi' => 'UPI',
            'card' => 'Card',
            'net_banking', 'net banking' => 'Net Banking',
            'wallet' => 'Wallet',
            'cod', 'cash on delivery' => 'COD',
            default => $method !== '' ? ucwords(str_replace(['_', '-'], ' ', $method)) : 'COD',
        };
    }
}
