CREATE TABLE IF NOT EXISTS custom_domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    domain VARCHAR(255) NOT NULL UNIQUE,
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Which of a user's verified domains is currently used for displaying/sharing
-- their short links. Nullable: falls back to the app's own domain.
ALTER TABLE users ADD COLUMN active_domain_id INT NULL AFTER paystack_customer_code;
ALTER TABLE users ADD CONSTRAINT fk_users_active_domain FOREIGN KEY (active_domain_id) REFERENCES custom_domains(id) ON DELETE SET NULL;
