<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DeliveryModel;
use App\Models\OrdersModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Delivery extends BaseController
{
    protected DeliveryModel $deliveryModel;
    protected OrdersModel $ordersModel;

    public function __construct()
    {
        $this->deliveryModel = new DeliveryModel();
        $this->ordersModel = new OrdersModel();
    }

    public function delivery_view()
    {
        $data['page'] = 'delivery';
        $data['deliveries'] = [];
        $data['hubs'] = [];

        try {
            $db = db_connect();
            if ($db->tableExists('deliveries')) {
                $this->syncDeliveriesFromOrders();
                $deliveries = $this->deliveryModel->orderBy('id', 'DESC')->findAll();
                $data['deliveries'] = $this->mapDeliveryRows($deliveries);
                $data['hubs'] = $this->extractHubs($data['deliveries']);
            }
        } catch (Throwable $e) {
            $data['deliveries'] = [];
            $data['hubs'] = [];
        }

        return view('admin/delivery', $data);
    }

    public function updateDelivery(): ResponseInterface
    {
        try {
            $db = db_connect();
            if (!$db->tableExists('deliveries')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Deliveries table does not exist.',
                ]);
            }

            $rules = [
                'id' => 'required|is_natural_no_zero',
                'rider_name' => 'required|min_length[2]|max_length[100]',
                'eta_date' => 'required|valid_date[Y-m-d]',
                'status' => 'required|in_list[1,2,3,4]',
                'priority' => 'required|in_list[1,2,3]',
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $id = (int) $this->request->getPost('id');
            $existing = $this->deliveryModel->find($id);
            if (!$existing) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Delivery not found.',
                ]);
            }

            $updateData = [
                'dl_rider_name' => trim((string) $this->request->getPost('rider_name')),
                'dl_eta_date' => trim((string) $this->request->getPost('eta_date')),
                'dl_status' => (int) $this->request->getPost('status'),
                'dl_priority' => (int) $this->request->getPost('priority'),
            ];

            if (!$this->deliveryModel->update($id, $updateData)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to update delivery.',
                ]);
            }

            $this->syncOrderStatus((string) ($existing['dl_order_code'] ?? ''), (int) $updateData['dl_status']);

            $updated = $this->deliveryModel->find($id);
            if (!$updated) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Delivery updated but not found.',
                ]);
            }

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Delivery updated successfully.',
                'delivery' => $this->mapDeliveryRow($updated),
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
            $result = $this->syncDeliveriesFromOrders();

            return $this->response->setJSON([
                'status' => true,
                'message' => $result['created'] > 0
                    ? $result['created'] . ' delivery record(s) created from orders.'
                    : 'Delivery records are already up to date.',
                'created' => $result['created'],
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function mapDeliveryRows(array $rows): array
    {
        return array_map(fn(array $row): array => $this->mapDeliveryRow($row), $rows);
    }

    private function mapDeliveryRow(array $row): array
    {
        $status = (int) ($row['dl_status'] ?? 1);
        $priority = (int) ($row['dl_priority'] ?? 1);
        $etaDate = (string) ($row['dl_eta_date'] ?? '');

        return [
            'id' => (int) ($row['id'] ?? 0),
            'shipment_code' => (string) ($row['dl_shipment_code'] ?? ''),
            'order_code' => (string) ($row['dl_order_code'] ?? ''),
            'customer_name' => (string) ($row['dl_customer_name'] ?? ''),
            'hub' => (string) ($row['dl_hub'] ?? ''),
            'rider_name' => (string) ($row['dl_rider_name'] ?? ''),
            'eta_date' => $etaDate,
            'eta_display' => $etaDate !== '' ? date('d-m-Y', strtotime($etaDate)) : '',
            'status' => $status,
            'status_text' => $this->statusText($status),
            'status_class' => $this->statusClass($status),
            'priority' => $priority,
            'priority_text' => $this->priorityText($priority),
            'priority_class' => $this->priorityClass($priority),
        ];
    }

    private function extractHubs(array $rows): array
    {
        $hubs = [];
        foreach ($rows as $row) {
            $hub = trim((string) ($row['hub'] ?? ''));
            if ($hub !== '') {
                $hubs[$hub] = $hub;
            }
        }

        ksort($hubs);
        return array_values($hubs);
    }

    private function statusText(int $status): string
    {
        return match ($status) {
            2 => 'Out For Delivery',
            3 => 'Delivered',
            4 => 'Delayed',
            default => 'Processing',
        };
    }

    private function statusClass(int $status): string
    {
        return match ($status) {
            2 => 'status-out',
            3 => 'status-delivered',
            4 => 'status-delayed',
            default => 'status-processing',
        };
    }

    private function priorityText(int $priority): string
    {
        return match ($priority) {
            2 => 'High',
            3 => 'Critical',
            default => 'Normal',
        };
    }

    private function priorityClass(int $priority): string
    {
        return match ($priority) {
            2 => 'priority-high',
            3 => 'priority-critical',
            default => 'priority-normal',
        };
    }

    private function syncDeliveriesFromOrders(): array
    {
        $db = db_connect();
        if (!$db->tableExists('deliveries') || !$db->tableExists('orders')) {
            return ['created' => 0];
        }

        $orders = $this->ordersModel
            ->whereNotIn('status', ['cancelled'])
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

            $existing = $this->deliveryModel
                ->where('dl_order_code', $orderCode)
                ->first();
            if ($existing) {
                continue;
            }

            $status = strtolower(trim((string) ($order['status'] ?? 'processing')));
            $deliveryStatus = $status === 'delivered' ? 3 : 1;
            $createdAt = trim((string) ($order['created_at'] ?? ''));
            $etaDate = $createdAt !== ''
                ? date('Y-m-d', strtotime($createdAt . ' +2 days'))
                : date('Y-m-d', strtotime('+2 days'));

            $inserted = $this->deliveryModel->insert([
                'dl_shipment_code' => $this->buildShipmentCode($orderId),
                'dl_order_code' => $orderCode,
                'dl_customer_name' => trim((string) ($order['customer_name'] ?? 'Customer')),
                'dl_hub' => 'Main Hub',
                'dl_rider_name' => 'Unassigned',
                'dl_eta_date' => $etaDate,
                'dl_status' => $deliveryStatus,
                'dl_priority' => 1,
            ]);

            if ($inserted) {
                $created++;
            }
        }

        return ['created' => $created];
    }

    private function syncOrderStatus(string $orderCode, int $deliveryStatus): void
    {
        $orderCode = trim($orderCode);
        if ($orderCode === '') {
            return;
        }

        $db = db_connect();
        if (!$db->tableExists('orders')) {
            return;
        }

        $orderStatus = match ($deliveryStatus) {
            3 => 'delivered',
            default => 'processing',
        };

        $this->ordersModel
            ->where('order_code', $orderCode)
            ->set(['status' => $orderStatus])
            ->update();
    }

    private function buildShipmentCode(int $orderId): string
    {
        $base = $orderId > 0 ? $orderId : random_int(1000, 9999);
        $code = 'DL-' . str_pad((string) $base, 5, '0', STR_PAD_LEFT);
        $existing = $this->deliveryModel->where('dl_shipment_code', $code)->first();

        if (!$existing) {
            return $code;
        }

        return 'DL-' . date('ymd') . '-' . random_int(100, 999);
    }
}
