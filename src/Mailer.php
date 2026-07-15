<?php
namespace App;

class Mailer
{
    /**
     * Send a single email via the SendGrid API.
     * Returns [success => bool, error => string|null]
     */
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): array
    {
        $apiKey = $_ENV['SENDGRID_API_KEY'];
        $fromEmail = $_ENV['FROM_EMAIL'];
        $fromName = $_ENV['FROM_NAME'];

        $payload = [
            'personalizations' => [[
                'to' => [['email' => $toEmail, 'name' => $toName]],
                'subject' => $subject,
            ]],
            'from' => ['email' => $fromEmail, 'name' => $fromName],
            'content' => [[
                'type' => 'text/html',
                'value' => $htmlBody,
            ]],
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => $curlError];
        }

        // SendGrid returns 202 on success with an empty body
        if ($httpCode === 202) {
            return ['success' => true, 'error' => null];
        }

        return ['success' => false, 'error' => "HTTP $httpCode: $response"];
    }
}
