<?php

namespace App\Controllers;

use App\Models\BillingInvoiceModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Billing extends BaseController
{
    private const STATUSES = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];

    protected BillingInvoiceModel $invoiceModel;

    public function __construct()
    {
        $this->invoiceModel = new BillingInvoiceModel();
    }

    public function index()
    {
        $data = [
            'page' => 'billing',
            'invoices' => [],
            'orders' => [],
            'products' => [],
            'customers' => [],
        ];

        try {
            $db = db_connect();
            if ($db->tableExists('billing_invoices')) {
                $data['invoices'] = array_map(
                    fn(array $row): array => $this->mapInvoice($row),
                    $this->invoiceModel->orderBy('id', 'DESC')->findAll()
                );
            }
            if ($db->tableExists('orders')) {
                $data['orders'] = array_map(
                    fn(array $row): array => $this->mapOrder($row),
                    $db->table('orders')->orderBy('id', 'DESC')->limit(200)->get()->getResultArray()
                );
            }
            if ($db->tableExists('products')) {
                $data['products'] = array_map(
                    static fn(array $row): array => [
                        'id' => (int) ($row['id'] ?? 0),
                        'name' => (string) ($row['pr_name'] ?? 'Product'),
                        'price' => round((float) ($row['pr_price'] ?? 0), 2),
                    ],
                    $db->table('products')
                        ->select('id, pr_name, pr_price')
                        ->where('pr_status', 1)
                        ->orderBy('pr_name', 'ASC')
                        ->get()->getResultArray()
                );
            }
            if ($db->tableExists('users')) {
                $data['customers'] = $db->table('users')
                    ->select('id, us_name, us_email, us_phone, us_address_line1, us_address_line2, us_city, us_state, us_postal_code, us_country')
                    ->where('us_role_id', 2)
                    ->orderBy('us_name', 'ASC')
                    ->get()->getResultArray();
            }
        } catch (Throwable $e) {
            // Render the module with empty collections.
        }

        return view('admin/billing', $data);
    }

    public function save(): ResponseInterface
    {
        try {
            $db = db_connect();
            if (!$db->tableExists('billing_invoices')) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Billing table is missing. Run migrations.',
                ]);
            }

            $rules = [
                'customer_name' => 'required|min_length[2]|max_length[120]',
                'customer_email' => 'permit_empty|valid_email|max_length[150]',
                'customer_phone' => 'permit_empty|max_length[40]',
                'invoice_date' => 'required|valid_date[Y-m-d]',
                'due_date' => 'required|valid_date[Y-m-d]',
                'status' => 'required|in_list[draft,sent,paid,overdue,cancelled]',
            ];
            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->validator->getErrors(),
                ]);
            }

            $invoiceDate = trim((string) $this->request->getPost('invoice_date'));
            $dueDate = trim((string) $this->request->getPost('due_date'));
            if ($dueDate < $invoiceDate) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Due date cannot be before the invoice date.',
                ]);
            }

            $items = $this->normalizeItems((string) $this->request->getPost('items'));
            if ($items === []) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Add at least one valid invoice item.',
                ]);
            }

            $subtotal = array_sum(array_column($items, 'total'));
            $discount = max(0, round((float) $this->request->getPost('discount'), 2));
            $discount = min($discount, $subtotal);
            $taxRate = max(0, min(100, round((float) $this->request->getPost('tax_rate'), 2)));
            $taxable = max(0, $subtotal - $discount);
            $taxAmount = round($taxable * ($taxRate / 100), 2);
            $total = round($taxable + $taxAmount, 2);
            $orderCode = trim((string) $this->request->getPost('order_code'));

            if ($orderCode !== '' && $this->invoiceModel->where('order_code', $orderCode)->first()) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'An invoice already exists for this order.',
                ]);
            }

            $payload = [
                'invoice_number' => $this->nextInvoiceNumber(),
                'order_code' => $orderCode !== '' ? $orderCode : null,
                'customer_name' => trim((string) $this->request->getPost('customer_name')),
                'customer_email' => strtolower(trim((string) $this->request->getPost('customer_email'))),
                'customer_phone' => trim((string) $this->request->getPost('customer_phone')),
                'billing_address' => trim((string) $this->request->getPost('billing_address')),
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'items_json' => json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'discount' => number_format($discount, 2, '.', ''),
                'tax_rate' => number_format($taxRate, 2, '.', ''),
                'tax_amount' => number_format($taxAmount, 2, '.', ''),
                'total' => number_format($total, 2, '.', ''),
                'status' => strtolower(trim((string) $this->request->getPost('status'))),
                'notes' => trim((string) $this->request->getPost('notes')),
            ];

            if (!$this->invoiceModel->insert($payload)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Unable to create invoice.',
                ]);
            }

            $invoice = $this->invoiceModel->find((int) $this->invoiceModel->getInsertID());
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Invoice created successfully.',
                'invoice' => $this->mapInvoice($invoice ?? $payload),
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Unexpected error creating invoice.',
            ]);
        }
    }

    public function updateStatus(): ResponseInterface
    {
        $id = (int) $this->request->getPost('id');
        $status = strtolower(trim((string) $this->request->getPost('status')));
        if ($id <= 0 || !in_array($status, self::STATUSES, true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Invalid invoice status.']);
        }

        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Invoice not found.']);
        }

        $this->invoiceModel->update($id, ['status' => $status]);
        $updated = $this->invoiceModel->find($id);
        return $this->response->setJSON([
            'status' => true,
            'message' => 'Invoice status updated.',
            'invoice' => $this->mapInvoice($updated ?? $invoice),
        ]);
    }

    public function delete(): ResponseInterface
    {
        $id = (int) $this->request->getPost('id');
        $invoice = $id > 0 ? $this->invoiceModel->find($id) : null;
        if (!$invoice) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Invoice not found.']);
        }
        if (($invoice['status'] ?? '') !== 'draft') {
            return $this->response->setJSON(['status' => false, 'message' => 'Only draft invoices can be deleted.']);
        }

        $this->invoiceModel->delete($id);
        return $this->response->setJSON(['status' => true, 'message' => 'Draft invoice deleted.']);
    }

    private function normalizeItems(string $itemsJson): array
    {
        $decoded = json_decode($itemsJson, true);
        if (!is_array($decoded)) {
            return [];
        }

        $items = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }
            $name = trim((string) ($item['name'] ?? ''));
            $quantity = max(0, (int) ($item['quantity'] ?? $item['qty'] ?? 0));
            $price = max(0, round((float) ($item['price'] ?? 0), 2));
            if ($name === '' || $quantity <= 0) {
                continue;
            }
            $items[] = [
                'name' => mb_substr($name, 0, 160),
                'quantity' => $quantity,
                'price' => $price,
                'total' => round($quantity * $price, 2),
            ];
        }

        return $items;
    }

    private function nextInvoiceNumber(): string
    {
        do {
            $number = 'INV-' . date('ymd') . '-' . random_int(1000, 9999);
        } while ($this->invoiceModel->where('invoice_number', $number)->first());

        return $number;
    }

    private function mapOrder(array $row): array
    {
        $items = json_decode((string) ($row['items_json'] ?? '[]'), true);
        if (!is_array($items)) {
            $items = [];
        }

        return [
            'code' => (string) ($row['order_code'] ?? ''),
            'customer_name' => (string) ($row['customer_name'] ?? ''),
            'customer_phone' => (string) ($row['customer_phone'] ?? ''),
            'billing_address' => (string) ($row['customer_address'] ?? ''),
            'discount' => round((float) ($row['discount'] ?? 0), 2),
            'items' => array_values(array_map(static fn(array $item): array => [
                'name' => (string) ($item['name'] ?? 'Product'),
                'quantity' => max(1, (int) ($item['qty'] ?? $item['quantity'] ?? 1)),
                'price' => round((float) ($item['price'] ?? 0), 2),
            ], array_filter($items, 'is_array'))),
        ];
    }

    private function mapInvoice(array $row): array
    {
        $items = json_decode((string) ($row['items_json'] ?? '[]'), true);
        if (!is_array($items)) {
            $items = [];
        }
        $status = strtolower((string) ($row['status'] ?? 'draft'));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'invoice_number' => (string) ($row['invoice_number'] ?? ''),
            'order_code' => (string) ($row['order_code'] ?? ''),
            'customer_name' => (string) ($row['customer_name'] ?? ''),
            'customer_email' => (string) ($row['customer_email'] ?? ''),
            'customer_phone' => (string) ($row['customer_phone'] ?? ''),
            'billing_address' => (string) ($row['billing_address'] ?? ''),
            'invoice_date' => (string) ($row['invoice_date'] ?? ''),
            'due_date' => (string) ($row['due_date'] ?? ''),
            'items' => $items,
            'subtotal' => round((float) ($row['subtotal'] ?? 0), 2),
            'discount' => round((float) ($row['discount'] ?? 0), 2),
            'tax_rate' => round((float) ($row['tax_rate'] ?? 0), 2),
            'tax_amount' => round((float) ($row['tax_amount'] ?? 0), 2),
            'total' => round((float) ($row['total'] ?? 0), 2),
            'status' => $status,
            'status_text' => ucfirst($status),
            'notes' => (string) ($row['notes'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }
}
