CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Scope contacts and campaigns to a user account
ALTER TABLE contacts ADD COLUMN user_id INT NULL AFTER id;
ALTER TABLE contacts ADD CONSTRAINT fk_contacts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE campaigns ADD COLUMN user_id INT NULL AFTER id;
ALTER TABLE campaigns ADD CONSTRAINT fk_campaigns_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Emails should only be unique per-user now, not globally
ALTER TABLE contacts DROP INDEX email;
ALTER TABLE contacts ADD UNIQUE KEY unique_email_per_user (user_id, email);
