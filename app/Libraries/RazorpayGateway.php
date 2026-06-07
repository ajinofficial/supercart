<?php

namespace App\Libraries;

use RuntimeException;

class RazorpayGateway
{
    private string $keyId;
    private string $keySecret;

    public function __construct(string $keyId, string $keySecret)
    {
        $this->keyId = trim($keyId);
        $this->keySecret = trim($keySecret);

        if ($this->keyId === '' || $this->keySecret === '') {
            throw new RuntimeException('Razorpay credentials are not configured.');
        }
    }

    public function createOrder(int $amount, string $currency, string $receipt, array $notes = []): array
    {
        return $this->request('POST', 'orders', [
            'amount' => $amount,
            'currency' => strtoupper($currency),
            'receipt' => substr($receipt, 0, 40),
            'notes' => $notes,
        ]);
    }

    public function fetchPayment(string $paymentId): array
    {
        return $this->request('GET', 'payments/' . rawurlencode($paymentId));
    }

    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return $signature !== '' && hash_equals($expected, $signature);
    }

    private function request(string $method, string $path, array $json = []): array
    {
        $client = service('curlrequest');
        $options = [
            'auth' => [$this->keyId, $this->keySecret, 'basic'],
            'headers' => ['Accept' => 'application/json'],
            'http_errors' => false,
            'timeout' => 20,
        ];

        if ($json !== []) {
            $options['json'] = $json;
        }

        $response = $client->request($method, 'https://api.razorpay.com/v1/' . ltrim($path, '/'), $options);
        $body = json_decode((string) $response->getBody(), true);
        $body = is_array($body) ? $body : [];

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $message = trim((string) ($body['error']['description'] ?? $body['error']['reason'] ?? 'Razorpay request failed.'));
            throw new RuntimeException($message !== '' ? $message : 'Razorpay request failed.');
        }

        return $body;
    }
}
