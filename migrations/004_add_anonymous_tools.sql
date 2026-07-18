-- Allow short links to be created anonymously (no account required for the first 3 uses)
ALTER TABLE short_links MODIFY user_id INT NULL;

-- Temporary file sharing tool
CREATE TABLE IF NOT EXISTS shared_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(40) NOT NULL UNIQUE,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    size_bytes BIGINT DEFAULT 0,
    downloads INT DEFAULT 0,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
