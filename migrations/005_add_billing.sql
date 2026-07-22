-- Billing: plans, payments (Paystack), and monthly usage counters

ALTER TABLE users ADD COLUMN plan VARCHAR(20) NOT NULL DEFAULT 'free' AFTER name;
ALTER TABLE users ADD COLUMN plan_expires_at TIMESTAMP NULL AFTER plan;
ALTER TABLE users ADD COLUMN paystack_customer_code VARCHAR(64) NULL AFTER plan_expires_at;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan VARCHAR(20) NOT NULL,
    reference VARCHAR(100) NOT NULL UNIQUE,
    amount_kobo BIGINT NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'NGN',
    status ENUM('pending','success','failed') DEFAULT 'pending',
    paystack_response JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tracks usage that resets every calendar month (emails sent, verifier checks, etc.)
CREATE TABLE IF NOT EXISTS usage_monthly (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    period VARCHAR(7) NOT NULL, -- e.g. '2026-07'
    metric VARCHAR(30) NOT NULL, -- 'emails_sent' | 'verifier_checks'
    count INT NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_period_metric (user_id, period, metric)
);
