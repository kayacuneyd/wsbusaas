-- Deployment Jobs Queue
CREATE TABLE IF NOT EXISTS deployment_jobs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed', 'retrying') DEFAULT 'pending',
    current_step INT DEFAULT 0,
    total_steps INT DEFAULT 9,
    retry_count INT DEFAULT 0,
    max_retries INT DEFAULT 3,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_order_id (order_id)
);

-- Domain Registrations Tracking
CREATE TABLE IF NOT EXISTS domain_registrations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT NOT NULL,
    domain_name VARCHAR(255) NOT NULL,
    status ENUM('pending', 'registered', 'failed', 'verified') DEFAULT 'pending',
    hostinger_order_id VARCHAR(100),
    hostinger_domain_id VARCHAR(100),
    expiration_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES deployment_jobs(id) ON DELETE CASCADE
);

-- Website Deployments Tracking
CREATE TABLE IF NOT EXISTS website_deployments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT NOT NULL,
    domain_name VARCHAR(255) NOT NULL,
    template_id VARCHAR(100) NOT NULL,
    ftp_path VARCHAR(255),
    status ENUM('pending', 'uploading', 'live', 'failed') DEFAULT 'pending',
    public_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES deployment_jobs(id) ON DELETE CASCADE
);

-- Detailed Step Logs
CREATE TABLE IF NOT EXISTS deployment_steps (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT NOT NULL,
    step_name VARCHAR(100) NOT NULL,
    status ENUM('started', 'success', 'failed') NOT NULL,
    payload JSON,
    response JSON,
    duration_ms INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES deployment_jobs(id) ON DELETE CASCADE
);

-- Email Notifications Log
CREATE TABLE IF NOT EXISTS email_notifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT NOT NULL,
    type ENUM('customer_success', 'customer_failed', 'admin_alert') NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    status ENUM('sent', 'failed') DEFAULT 'sent',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES deployment_jobs(id) ON DELETE CASCADE
);

-- Template Registry
CREATE TABLE IF NOT EXISTS template_registry (
    id VARCHAR(100) PRIMARY KEY, -- e.g., 'starter-static-v1'
    name VARCHAR(255) NOT NULL,
    version VARCHAR(20) NOT NULL,
    path VARCHAR(255) NOT NULL,
    checksum VARCHAR(64),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Update Orders Table
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS deployment_job_id BIGINT,
ADD COLUMN IF NOT EXISTS business_name VARCHAR(255),
ADD COLUMN IF NOT EXISTS business_phone VARCHAR(50),
ADD COLUMN IF NOT EXISTS business_address TEXT,
ADD COLUMN IF NOT EXISTS deployment_type ENUM('static', 'wordpress') DEFAULT 'static';
