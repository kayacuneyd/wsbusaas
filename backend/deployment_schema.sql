CREATE TABLE IF NOT EXISTS deployment_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed', 'retrying') DEFAULT 'pending',
    current_step INT DEFAULT 0,
    total_steps INT DEFAULT 9,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS deployment_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    step_name VARCHAR(100) NOT NULL,
    status ENUM('pending', 'started', 'success', 'failed') DEFAULT 'pending',
    payload JSON,
    response JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES deployment_jobs(id) ON DELETE CASCADE
);
