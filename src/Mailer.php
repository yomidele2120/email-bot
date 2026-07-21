<?php
namespace App;

class Mailer
{
    /**
     * Send a single email via the SendGrid API.
     * $fromName and $replyTo let each user's campaigns show their own name
     * and reply address, even though the underlying authenticated "From" email
     * stays fixed (that part can't be arbitrary, see note in Settings).
     */
    public static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        ?string $fromName = null,
        ?string $replyTo = null
    ): array {
        $apiKey = $_ENV['SENDGRID_API_KEY'];
        $fromEmail = $_ENV['FROM_EMAIL'];
        $fromName = $fromName ?: $_ENV['FROM_NAME'];

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

        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $payload['reply_to'] = ['email' => $replyTo, 'name' => $fromName];
        }

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

        if ($httpCode === 202) {
            return ['success' => true, 'error' => null];
        }

        return ['success' => false, 'error' => "HTTP $httpCode: $response"];
    }
}
