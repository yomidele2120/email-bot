<?php
namespace App;

class Template
{
    /**
     * Replace {{field}} placeholders in the template with contact data.
     * Supports {{name}}, {{email}}, and any custom field from the CSV.
     * Always injects {{unsubscribe_link}} if present in the template.
     */
    public static function render(string $html, array $contact, string $unsubscribeLink): string
    {
        $customFields = json_decode($contact['custom_fields'] ?? '{}', true) ?: [];

        $replacements = array_merge($customFields, [
            'name' => $contact['name'] ?? '',
            'email' => $contact['email'] ?? '',
            'unsubscribe_link' => $unsubscribeLink,
        ]);

        foreach ($replacements as $key => $value) {
            $html = str_replace('{{' . $key . '}}', htmlspecialchars((string)$value, ENT_QUOTES), $html);
        }

        // If the template didn't include an unsubscribe link, append one.
        // This is a legal requirement (CAN-SPAM / NDPR / GDPR) for bulk email.
        if (!str_contains($html, $unsubscribeLink)) {
            $html .= "<p style='font-size:12px;color:#888;margin-top:24px;'>
                <a href='{$unsubscribeLink}'>Unsubscribe</a> from these emails.</p>";
        }

        return $html;
    }
}
