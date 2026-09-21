CREATE DATABASE IF NOT EXISTS assessment_db;
USE assessment_db;

CREATE TABLE IF NOT EXISTS clients (
  client_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(50),
  address VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS services (
  service_id INT AUTO_INCREMENT PRIMARY KEY,
  service_name VARCHAR(150) NOT NULL,
  description TEXT,
  hourly_rate DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bookings (
  booking_id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  service_id INT NOT NULL,
  booking_date DATE NOT NULL,
  hours INT NOT NULL DEFAULT 1,
  hourly_rate_snapshot DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(30) NOT NULL DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tools (
  tool_id INT AUTO_INCREMENT PRIMARY KEY,
  tool_name VARCHAR(150) NOT NULL,
  quantity_total INT NOT NULL DEFAULT 0,
  quantity_available INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS booking_tools (
  booking_tool_id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  tool_id INT NOT NULL,
  qty_used INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS payments (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  method VARCHAR(50) NOT NULL DEFAULT 'CASH',
  payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO services (service_name, description, hourly_rate, is_active) VALUES
('Plumbing', 'Leak repairs and installation', 500.00, 1),
('Electrical', 'Wiring and troubleshooting', 600.00, 1),
('Carpentry Works', 'Wood repairs and minor builds', 450.00, 1);

INSERT INTO tools (tool_name, quantity_total, quantity_available) VALUES
('Power Drill', 5, 5),
('Hammer', 10, 10),
('Ladder', 3, 3);