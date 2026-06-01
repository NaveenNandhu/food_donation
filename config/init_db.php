<?php
require_once __DIR__ . '/database.php';

$sql = "
-- MySQL requires INT AUTO_INCREMENT PRIMARY KEY for serial-like behavior.
-- ENUM is used instead of CHECK constraints for better compatibility with MySQL.
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    user_type ENUM('donor', 'charity', 'admin') NOT NULL, -- ADDED: 'admin' type
    organization_name VARCHAR(200),
    pan_card VARCHAR(20) NULL, -- ADDED: PAN Card field
    is_verified BOOLEAN DEFAULT FALSE, -- ADDED: Verification status (false by default)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    food_name VARCHAR(200) NOT NULL,
    food_type VARCHAR(50) NOT NULL,
    quantity VARCHAR(100) NOT NULL,
    description TEXT,
    expiry_date DATE,
    pickup_address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    image_path VARCHAR(255) NULL, 
    status ENUM('available', 'requested', 'completed', 'expired') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donation_id INT NOT NULL,
    charity_id INT NOT NULL,
    message TEXT,
    status ENUM('pending', 'accepted', 'rejected', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE,
    FOREIGN KEY (charity_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Explicit indexes for query performance
CREATE INDEX idx_donations_status ON donations(status);
CREATE INDEX idx_donations_city ON donations(city);
CREATE INDEX idx_requests_status ON requests(status);
";

try {
    $pdo->exec($sql);
    echo "Database tables created successfully!";
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>