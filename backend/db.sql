CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(191) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','customer') DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_email VARCHAR(191) NOT NULL,
  theme_id VARCHAR(32) NOT NULL,
  status ENUM('draft','pending','paid','deploying','live','failed') DEFAULT 'draft',
  total_amount DECIMAL(10,2) NOT NULL,
  ruul_payment_link VARCHAR(512),
  domain VARCHAR(191),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE theme_configs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  primary_color VARCHAR(16),
  secondary_color VARCHAR(16),
  font VARCHAR(64),
  logo_path VARCHAR(255),
  sections_json JSON,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE domains (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  domain VARCHAR(191) NOT NULL,
  availability ENUM('unknown','available','taken') DEFAULT 'unknown',
  checked_at TIMESTAMP NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  ruul_payment_id VARCHAR(128),
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(8) DEFAULT 'USD',
  status ENUM('initiated','paid','failed') DEFAULT 'initiated',
  payload JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  level VARCHAR(10),
  message TEXT,
  context JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
