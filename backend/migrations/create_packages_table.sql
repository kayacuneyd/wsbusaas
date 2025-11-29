-- Migration: Create packages table
-- Run this script to add package management functionality

CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2),
    payment_link TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Ensure category column exists for existing installations
ALTER TABLE packages ADD COLUMN IF NOT EXISTS category VARCHAR(100) DEFAULT 'general' AFTER price;

-- Insert default packages if they don't exist
INSERT IGNORE INTO packages (name, slug, description, price, category, payment_link, is_active, display_order) VALUES
('Starter Paket', 'starter', 'Temel website paketi - Domain, hosting ve tasarım dahil', 299.00, 'website', NULL, TRUE, 1),
('Professional Paket', 'professional', 'Profesyonel website paketi - Gelişmiş özellikler', 499.00, 'website', NULL, TRUE, 2),
('Business Paket', 'business', 'İşletme paketi - Tüm özellikler ve öncelikli destek', 799.00, 'website', NULL, TRUE, 3),
('Bakım & Destek', 'maintenance', 'Mevcut siteniz için bakım, onarım ve destek paketi', 99.00, 'maintenance', NULL, TRUE, 4),
('Tasarım Yenileme', 'design-refresh', 'Var olan sitenizde tema/tasarım yenileme hizmeti', 199.00, 'design', NULL, TRUE, 5);
