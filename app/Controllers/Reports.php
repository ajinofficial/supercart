<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Reports extends BaseController
{
    private const SOURCES = ['all', 'orders', 'payments', 'delivery', 'returns'];

    public function reports_view()
    {
        $filters = $this->resolveFilters(
            (string) $this->request->getGet('from'),
            (string) $this->request->getGet('to'),
            (string) $this->request->getGet('source')
        );
        $report = $this->buildReport($filters);

        return view('admin/reports', array_merge([
            'page' => 'reports',
            'filters' => $filters,
        ], $report));
    }

    public function reportData(): ResponseInterface
    {
        try {
            $filters = $this->resolveFilters(
                (string) $this->request->getPost('from'),
                (string) $this->request->getPost('to'),
                (string) $this->request->getPost('source')
            );

            return $this->response->setJSON(array_merge([
                'status' => true,
                'filters' => $filters,
            ], $this->buildReport($filters)));
        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Unable to generate report.',
            ]);
        }
    }

    private function buildReport(array $filters): array
    {
        $rows = [];
        $source = $filters['source'];
        if ($source === 'all' || $source === 'orders') {
            $rows = array_merge($rows, $this->fetchOrders($filters['from'], $filters['to']));
        }
        if ($source === 'all' || $source === 'payments') {
            $rows = array_merge($rows, $this->fetchPayments($filters['from'], $filters['to']));
        }
        if ($source === 'all' || $source === 'delivery') {
            $rows = array_merge($rows, $this->fetchDeliveries($filters['from'], $filters['to']));
        }
        if ($source === 'all' || $source === 'returns') {
            $rows = array_merge($rows, $this->fetchReturns($filters['from'], $filters['to']));
        }

        usort($rows, static fn(array $a, array $b): int => strcmp($b['event_date'], $a['event_date']));

        return [
            'summary' => $this->buildSummary($rows),
            'status_chart' => $this->bucketChart($rows, 'status'),
            'source_chart' => $this->bucketChart($rows, 'source'),
            'trend_chart' => $this->buildTrendChart($rows, $filters['from'], $filters['to']),
            'rows' => $rows,
        ];
    }

    private function fetchOrders(string $from, string $to): array
    {
        $db = db_connect();
        if (!$db->tableExists('orders')) {
            return [];
        }
        $rows = $db->table('orders')
            ->select('order_code, customer_name, status, total, payment_method, created_at')
            ->where('DATE(created_at) >=', $from)
            ->where('DATE(created_at) <=', $to)
            ->get()->getResultArray();

        return array_map(function (array $row): array {
            $status = $this->orderStatus((string) ($row['status'] ?? 'processing'));
            $amount = (float) ($row['total'] ?? 0);
            return $this->row(
                'Orders',
                (string) ($row['order_code'] ?? ''),
                (string) ($row['order_code'] ?? ''),
                (string) ($row['customer_name'] ?? ''),
                $status,
                $amount,
                $this->paymentMethod($row['payment_method'] ?? 'cod'),
                substr((string) ($row['created_at'] ?? ''), 0, 10),
                $status === 'Delivered',
                $status === 'Processing',
                false,
                $status === 'Cancelled' ? 0.0 : $amount
            );
        }, $rows);
    }

    private function fetchPayments(string $from, string $to): array
    {
        $db = db_connect();
        if (!$db->tableExists('payments')) {
            return [];
        }
        $rows = $db->table('payments')
            ->select('pm_transaction_code, pm_order_code, pm_customer_name, pm_method, pm_amount, pm_paid_on, pm_status')
            ->where('pm_paid_on >=', $from)
            ->where('pm_paid_on <=', $to)
            ->get()->getResultArray();

        return array_map(function (array $row): array {
            $statusCode = (int) ($row['pm_status'] ?? 1);
            $status = match ($statusCode) {
                2 => 'Paid',
                3 => 'Failed',
                4 => 'Refunded',
                default => 'Pending',
            };
            $amount = (float) ($row['pm_amount'] ?? 0);
            return $this->row(
                'Payments',
                (string) ($row['pm_transaction_code'] ?? ''),
                (string) ($row['pm_order_code'] ?? ''),
                (string) ($row['pm_customer_name'] ?? ''),
                $status,
                $amount,
                $this->paymentMethod($row['pm_method'] ?? ''),
                (string) ($row['pm_paid_on'] ?? ''),
                $statusCode === 2,
                $statusCode === 1,
                $statusCode === 4,
                $statusCode === 2 ? $amount : 0.0
            );
        }, $rows);
    }

    private function fetchDeliveries(string $from, string $to): array
    {
        $db = db_connect();
        if (!$db->tableExists('deliveries')) {
            return [];
        }
        $rows = $db->table('deliveries')
            ->select('dl_shipment_code, dl_order_code, dl_customer_name, dl_status, dl_priority, dl_eta_date')
            ->where('dl_eta_date >=', $from)
            ->where('dl_eta_date <=', $to)
            ->get()->getResultArray();

        return array_map(function (array $row): array {
            $statusCode = (int) ($row['dl_status'] ?? 1);
            $status = match ($statusCode) {
                2 => 'Out For Delivery',
                3 => 'Delivered',
                4 => 'Delayed',
                default => 'Processing',
            };
            return $this->row(
                'Delivery',
                (string) ($row['dl_shipment_code'] ?? ''),
                (string) ($row['dl_order_code'] ?? ''),
                (string) ($row['dl_customer_name'] ?? ''),
                $status,
                0,
                match ((int) ($row['dl_priority'] ?? 1)) {
                    2 => 'High Priority',
                    3 => 'Critical Priority',
                    default => 'Normal Priority',
                },
                (string) ($row['dl_eta_date'] ?? ''),
                $statusCode === 3,
                in_array($statusCode, [1, 2, 4], true),
                false,
                0.0
            );
        }, $rows);
    }

    private function fetchReturns(string $from, string $to): array
    {
        $db = db_connect();
        if (!$db->tableExists('returns')) {
            return [];
        }
        $rows = $db->table('returns')
            ->select('rt_return_code, rt_order_code, rt_customer_name, rt_status, rt_refund_amount, rt_refund_mode, rt_refund_state, rt_requested_on')
            ->where('rt_requested_on >=', $from)
            ->where('rt_requested_on <=', $to)
            ->get()->getResultArray();

        return array_map(function (array $row): array {
            $statusCode = (int) ($row['rt_status'] ?? 1);
            $refundState = (int) ($row['rt_refund_state'] ?? 1);
            $status = match ($statusCode) {
                2 => 'Picked Up',
                3 => 'In Inspection',
                4 => 'Completed',
                5 => 'Rejected',
                default => 'Requested',
            };
            $amount = (float) ($row['rt_refund_amount'] ?? 0);
            return $this->row(
                'Returns',
                (string) ($row['rt_return_code'] ?? ''),
                (string) ($row['rt_order_code'] ?? ''),
                (string) ($row['rt_customer_name'] ?? ''),
                $status,
                $amount,
                (string) ($row['rt_refund_mode'] ?? '-'),
                (string) ($row['rt_requested_on'] ?? ''),
                $statusCode === 4,
                in_array($statusCode, [1, 2, 3], true),
                $refundState === 3,
                0.0
            );
        }, $rows);
    }

    private function row(
        string $source,
        string $reference,
        string $orderCode,
        string $customer,
        string $status,
        float $amount,
        string $channel,
        string $date,
        bool $completed,
        bool $pending,
        bool $refunded,
        float $revenue
    ): array {
        return [
            'source' => $source,
            'reference' => $reference,
            'order_code' => $orderCode,
            'customer' => $customer,
            'status' => $status,
            'amount' => $this->getCurrencySymbol() . ' ' . number_format($amount, 2),
            'amount_value' => $amount,
            'channel' => $channel,
            'event_date' => $date,
            'event_date_display' => $date !== '' ? date('d M Y', strtotime($date)) : '-',
            'is_completed' => $completed,
            'is_pending' => $pending,
            'is_refunded' => $refunded,
            'revenue_value' => $revenue,
        ];
    }

    private function buildSummary(array $rows): array
    {
        $summary = [
            'total' => count($rows),
            'completed' => 0,
            'pending' => 0,
            'orders' => 0,
            'paid' => 0,
            'revenue' => 0.0,
            'refund_total' => 0.0,
        ];
        $orderRevenue = 0.0;
        $paymentRevenue = 0.0;
        foreach ($rows as $row) {
            $summary['completed'] += !empty($row['is_completed']) ? 1 : 0;
            $summary['pending'] += !empty($row['is_pending']) ? 1 : 0;
            $summary['orders'] += $row['source'] === 'Orders' ? 1 : 0;
            $summary['paid'] += $row['source'] === 'Payments' && $row['status'] === 'Paid' ? 1 : 0;
            if ($row['source'] === 'Orders') {
                $orderRevenue += (float) ($row['revenue_value'] ?? 0);
            }
            if ($row['source'] === 'Payments') {
                $paymentRevenue += (float) ($row['revenue_value'] ?? 0);
            }
            $summary['refund_total'] += !empty($row['is_refunded']) ? (float) ($row['amount_value'] ?? 0) : 0;
        }
        $summary['revenue'] = $summary['orders'] > 0 ? $orderRevenue : $paymentRevenue;
        $summary['completion_rate'] = $summary['total'] > 0
            ? round(($summary['completed'] / $summary['total']) * 100, 1)
            : 0.0;
        $summary['revenue'] = number_format($summary['revenue'], 2, '.', '');
        $summary['refund_total'] = number_format($summary['refund_total'], 2, '.', '');
        return $summary;
    }

    private function bucketChart(array $rows, string $field): array
    {
        $buckets = [];
        foreach ($rows as $row) {
            $key = (string) ($row[$field] ?? 'Unknown');
            $buckets[$key] = ($buckets[$key] ?? 0) + 1;
        }
        arsort($buckets);
        return ['labels' => array_keys($buckets), 'values' => array_values($buckets)];
    }

    private function buildTrendChart(array $rows, string $from, string $to): array
    {
        $dayCount = max(1, (int) floor((strtotime($to) - strtotime($from)) / 86400) + 1);
        $daily = $dayCount <= 45;
        $buckets = [];
        if ($daily) {
            for ($cursor = strtotime($from); $cursor <= strtotime($to); $cursor = strtotime('+1 day', $cursor)) {
                $buckets[date('Y-m-d', $cursor)] = 0;
            }
        } else {
            for ($cursor = strtotime(date('Y-m-01', strtotime($from))); $cursor <= strtotime($to); $cursor = strtotime('+1 month', $cursor)) {
                $buckets[date('Y-m', $cursor)] = 0;
            }
        }
        foreach ($rows as $row) {
            if ($row['event_date'] === '') {
                continue;
            }
            $key = date($daily ? 'Y-m-d' : 'Y-m', strtotime($row['event_date']));
            if (isset($buckets[$key])) {
                $buckets[$key]++;
            }
        }
        return [
            'labels' => array_map(
                static fn(string $key): string => date($daily ? 'd M' : 'M Y', strtotime($daily ? $key : $key . '-01')),
                array_keys($buckets)
            ),
            'values' => array_values($buckets),
            'granularity' => $daily ? 'daily' : 'monthly',
        ];
    }

    private function resolveFilters(string $fromInput, string $toInput, string $sourceInput): array
    {
        $to = $this->normalizeDate($toInput) ?? date('Y-m-d');
        $from = $this->normalizeDate($fromInput) ?? date('Y-m-d', strtotime('-89 days'));
        if (strtotime($from) > strtotime($to)) {
            [$from, $to] = [$to, $from];
        }
        $source = strtolower(trim($sourceInput));
        if (!in_array($source, self::SOURCES, true)) {
            $source = 'all';
        }
        return ['from' => $from, 'to' => $to, 'source' => $source];
    }

    private function normalizeDate(string $value): ?string
    {
        $timestamp = strtotime(trim($value));
        return trim($value) !== '' && $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

    private function orderStatus(string $value): string
    {
        return match (strtolower(trim($value))) {
            'delivered', '2' => 'Delivered',
            'cancelled', '3' => 'Cancelled',
            default => 'Processing',
        };
    }

    private function paymentMethod($value): string
    {
        if (is_numeric($value) && in_array((int) $value, [0, 1], true)) {
            return 'COD';
        }
        $method = trim((string) $value);
        return $method !== '' ? strtoupper($method) : 'COD';
    }
}
