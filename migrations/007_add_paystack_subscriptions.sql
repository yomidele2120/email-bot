-- Paystack Subscriptions module support (recurring billing, not one-off charges)

ALTER TABLE users ADD COLUMN paystack_subscription_code VARCHAR(64) NULL AFTER paystack_customer_code;
ALTER TABLE users ADD COLUMN paystack_email_token VARCHAR(100) NULL AFTER paystack_subscription_code;

-- Webhook events are idempotent-checked against this so a retried webhook
-- delivery (Paystack retries on non-200 responses) never double-grants access.
-- dedupe_key is built per event type (e.g. "charge.success:TRX_REFERENCE").
CREATE TABLE IF NOT EXISTS paystack_webhook_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dedupe_key VARCHAR(150) NOT NULL UNIQUE,
    event_type VARCHAR(50) NOT NULL,
    processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
