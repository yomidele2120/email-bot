<?php
namespace App;

class Paystack
{
    private static function secretKey(): string
    {
        return $_ENV['PAYSTACK_SECRET_KEY'] ?? '';
    }

    public static function isConfigured(): bool
    {
        return !empty(self::secretKey());
    }

    private static function request(string $method, string $path, array $data = []): array
    {
        $ch = curl_init("https://api.paystack.co{$path}");
        $headers = [
            'Authorization: Bearer ' . self::secretKey(),
            'Content-Type: application/json',
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => false, 'message' => "Paystack request failed: $error"];
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : ['status' => false, 'message' => 'Invalid Paystack response'];
    }

    /**
     * Starts a transaction. Returns Paystack's response, which includes
     * data.authorization_url to redirect the user to, and data.reference.
     */
    public static function initializeTransaction(string $email, int $amountKobo, string $reference, string $callbackUrl, array $metadata = []): array
    {
        return self::request('POST', '/transaction/initialize', [
            'email' => $email,
            'amount' => $amountKobo,
            'currency' => 'NGN',
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => $metadata,
        ]);
    }

    /** Confirms a transaction actually succeeded. Call this before granting access. */
    public static function verifyTransaction(string $reference): array
    {
        return self::request('GET', '/transaction/verify/' . rawurlencode($reference));
    }
}
