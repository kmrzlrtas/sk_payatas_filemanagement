-- sk_payatas_db_install.sql
CREATE DATABASE IF NOT EXISTS sk_payatas_db;
USE sk_payatas_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  fullname VARCHAR(150),
  role ENUM('admin','official') DEFAULT 'official',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- sample admin (password: admin123)
INSERT INTO users (username, password, fullname, role) VALUES ('admin', '$2y$10$wHfQh3YbG2QqQh0nC8rWVeY4qWq6b1L9u3l0BG1D9b0xg3Jb0gVqO', 'System Admin', 'admin');

CREATE TABLE IF NOT EXISTS documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  filename VARCHAR(255),
  filepath VARCHAR(255),
  filesize BIGINT DEFAULT 0,
  uploaded_by INT,
  qr_code VARCHAR(255),
  status VARCHAR(100) DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS document_activity (
  id INT AUTO_INCREMENT PRIMARY KEY,
  doc_id INT,
  user_id INT NULL,
  action VARCHAR(255),
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (doc_id) REFERENCES documents(id) ON DELETE CASCADE
);
