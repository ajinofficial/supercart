<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationConversationModel;
use App\Models\NotificationMessageModel;
use App\Models\OrdersModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Order extends BaseController
{
    protected OrdersModel $ordersModel;

    public function __construct()
    {
        $this->ordersModel = new OrdersModel();
    }

    /**
     * Orders list page
     */
    public function order_view()
    {
        $data['page'] = 'orders';
        $data['orders'] = [];
        $data['stats'] = [
            'total' => 0,
            'processing' => 0,
            'delivered' => 0,
            'cancelled' => 0,
            'revenue' => 0.0,
        ];

        try {
            $db = db_connect();
            if ($db->tableExists('orders')) {
                $deliveries = [];
                if ($db->tableExists('deliveries')) {
                    foreach ($db->table('deliveries')->get()->getResultArray() as $row) {
                        $deliveries[(string) ($row['dl_order_code'] ?? '')] = $row;
                    }
                }

                $payments = [];
                if ($db->tableExists('payments')) {
                    foreach ($db->table('payments')->get()->getResultArray() as $row) {
                        $payments[(string) ($row['pm_order_code'] ?? '')] = $row;
                    }
                }

                $rows = $this->ordersModel->orderBy('id', 'DESC')->findAll();
                foreach ($rows as $row) {
                    $order = $this->mapOrder($row, $deliveries, $payments);
                    $data['orders'][] = $order;
                    $data['stats']['total']++;
                    $data['stats'][$order['status']]++;
                    if ($order['status'] !== 'cancelled') {
                        $data['stats']['revenue'] += $order['total'];
                    }
                }
            }
        } catch (Throwable $e) {
            $data['orders'] = [];
        }

        return view('admin/orders', $data);
    }

    public function updateOrder(): ResponseInterface
    {
        try {
            $db = db_connect();
            if (!$db->tableExists('orders')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Orders table does not exist.',
                ]);
            }

            $id = (int) $this->request->getPost('id');
            $status = strtolower(trim((string) $this->request->getPost('status')));
            $allowed = ['processing', 'delivered', 'cancelled'];
            if ($id <= 0 || !in_array($status, $allowed, true)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid order update.',
                ]);
            }

            $existing = $this->ordersModel->find($id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => false,
                    'message' => 'Order not found.',
                ]);
            }

            $db->transBegin();
            $db->table('orders')
                ->where('id', $id)
                ->update([
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $storedOrder = $db->table('orders')
                ->where('id', $id)
                ->get()
                ->getRowArray();

            if (
                $db->transStatus() === false
                || !$storedOrder
                || strtolower(trim((string) ($storedOrder['status'] ?? ''))) !== $status
            ) {
                $db->transRollback();
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to save order status to the database.',
                ]);
            }
            $db->transCommit();

            $this->syncDeliveryStatus((string) ($storedOrder['order_code'] ?? ''), $status);
            $this->notifyCustomer($storedOrder, $status);

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Order status saved to database.',
                'order_id' => $id,
                'status_value' => (string) $storedOrder['status'],
                'status_text' => ucfirst((string) $storedOrder['status']),
                'updated_at' => (string) ($storedOrder['updated_at'] ?? ''),
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Unexpected error updating order.',
            ]);
        }
    }

    private function mapOrder(array $row, array $deliveries, array $payments): array
    {
        $code = trim((string) ($row['order_code'] ?? ''));
        $items = json_decode((string) ($row['items_json'] ?? '[]'), true);
        if (!is_array($items)) {
            $items = [];
        }

        $delivery = $deliveries[$code] ?? [];
        $payment = $payments[$code] ?? [];
        $paymentMethod = $row['payment_method'] ?? 'cod';
        if (is_numeric($paymentMethod) && in_array((int) $paymentMethod, [0, 1], true)) {
            $paymentMethod = 'cod';
        }

        return [
            'id' => (int) ($row['id'] ?? 0),
            'code' => $code !== '' ? $code : '#' . (int) ($row['id'] ?? 0),
            'user_id' => (int) ($row['user_id'] ?? 0),
            'customer_name' => trim((string) ($row['customer_name'] ?? '')) ?: 'Customer',
            'customer_phone' => (string) ($row['customer_phone'] ?? ''),
            'customer_address' => (string) ($row['customer_address'] ?? ''),
            'customer_note' => (string) ($row['customer_note'] ?? ''),
            'status' => $this->normalizeStatus($row['status'] ?? 'processing'),
            'payment_method' => strtoupper((string) $paymentMethod),
            'subtotal' => (float) ($row['subtotal'] ?? 0),
            'discount' => (float) ($row['discount'] ?? 0),
            'total' => (float) ($row['total'] ?? 0),
            'coupon_code' => (string) ($row['coupon_code'] ?? ''),
            'items' => $items,
            'item_count' => array_sum(array_map(
                static fn(array $item): int => max(1, (int) ($item['qty'] ?? 1)),
                $items
            )),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
            'delivery' => [
                'id' => (int) ($delivery['id'] ?? 0),
                'shipment_code' => (string) ($delivery['dl_shipment_code'] ?? ''),
                'hub' => (string) ($delivery['dl_hub'] ?? ''),
                'rider_name' => (string) ($delivery['dl_rider_name'] ?? ''),
                'eta_date' => (string) ($delivery['dl_eta_date'] ?? ''),
                'status' => $this->deliveryStatusText((int) ($delivery['dl_status'] ?? 0)),
            ],
            'payment' => [
                'id' => (int) ($payment['id'] ?? 0),
                'transaction_code' => (string) ($payment['pm_transaction_code'] ?? ''),
                'gateway_ref' => (string) ($payment['pm_gateway_ref'] ?? ''),
                'method' => (string) ($payment['pm_method'] ?? $paymentMethod),
                'status' => $this->paymentStatusText((int) ($payment['pm_status'] ?? 0)),
                'paid_on' => (string) ($payment['pm_paid_on'] ?? ''),
            ],
        ];
    }

    private function normalizeStatus($value): string
    {
        if (is_numeric($value)) {
            return match ((int) $value) {
                2 => 'delivered',
                3 => 'cancelled',
                default => 'processing',
            };
        }

        $status = strtolower(trim((string) $value));
        return in_array($status, ['processing', 'delivered', 'cancelled'], true)
            ? $status
            : 'processing';
    }

    private function deliveryStatusText(int $status): string
    {
        return match ($status) {
            2 => 'Out for delivery',
            3 => 'Delivered',
            4 => 'Delayed',
            default => $status > 0 ? 'Processing' : 'Not assigned',
        };
    }

    private function paymentStatusText(int $status): string
    {
        return match ($status) {
            2 => 'Paid',
            3 => 'Failed',
            4 => 'Refunded',
            default => $status > 0 ? 'Pending' : 'Not recorded',
        };
    }

    private function syncDeliveryStatus(string $orderCode, string $status): void
    {
        if ($orderCode === '' || $status !== 'delivered') {
            return;
        }

        $db = db_connect();
        if ($db->tableExists('deliveries')) {
            $db->table('deliveries')
                ->where('dl_order_code', $orderCode)
                ->update(['dl_status' => 3]);
        }
    }

    private function notifyCustomer(array $order, string $status): void
    {
        $userId = (int) ($order['user_id'] ?? 0);
        if ($userId <= 0) {
            return;
        }

        try {
            $conversations = new NotificationConversationModel();
            $messages = new NotificationMessageModel();
            $code = (string) ($order['order_code'] ?? ('#' . ($order['id'] ?? '')));
            $title = 'Order ' . $code;
            $message = 'Your order status was updated to ' . ucfirst($status) . '.';
            $conversation = $conversations
                ->where('nc_user_id', $userId)
                ->where('nc_title', $title)
                ->first();

            if (!$conversation) {
                $conversationId = (int) $conversations->insert([
                    'nc_user_id' => $userId,
                    'nc_title' => $title,
                    'nc_participant' => (string) ($order['customer_name'] ?? 'Customer'),
                    'nc_type' => 'customer',
                    'nc_last_message' => $message,
                    'nc_unread_count' => 0,
                    'nc_user_unread_count' => 1,
                    'nc_status' => 1,
                ]);
            } else {
                $conversationId = (int) $conversation['id'];
                $conversations->update($conversationId, [
                    'nc_last_message' => $message,
                    'nc_user_unread_count' => (int) ($conversation['nc_user_unread_count'] ?? 0) + 1,
                ]);
            }

            if ($conversationId > 0) {
                $messages->insert([
                    'nm_conversation_id' => $conversationId,
                    'nm_sender_type' => 'admin',
                    'nm_sender_name' => (string) (session()->get('us_name') ?: 'Administrator'),
                    'nm_message' => $message,
                    'nm_is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (Throwable $e) {
            // Order updates should not fail when notification delivery is unavailable.
        }
    }
}
