SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS cjm_cosmetic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cjm_cosmetic;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS media;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE media (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(100) NOT NULL UNIQUE,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  category VARCHAR(50) NOT NULL DEFAULT 'General',
  description TEXT NULL,
  price DECIMAL(10,2) NOT NULL,
  old_price DECIMAL(10,2) NULL,
  image VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(100) NOT NULL,
  phone VARCHAR(15) NOT NULL,
  address VARCHAR(255) NOT NULL DEFAULT '',
  total DECIMAL(10,2) NOT NULL,
  status ENUM('PENDING','PAID','FAILED') NOT NULL DEFAULT 'PENDING',
  checkout_request_id VARCHAR(100) NULL,
  mpesa_receipt VARCHAR(30) NULL,
  result_desc VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  qty INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  duration_min INT NOT NULL DEFAULT 60
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(100) NOT NULL,
  phone VARCHAR(15) NOT NULL,
  email VARCHAR(100) NULL,
  service_id INT NULL,
  service_name VARCHAR(100) NOT NULL,
  appt_date DATE NOT NULL,
  appt_time TIME NOT NULL,
  notes VARCHAR(500) NULL,
  status ENUM('PENDING','CONFIRMED','COMPLETED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO products (name, category, description, price, old_price) VALUES
('Matte Lipstick', 'Makeup', 'Long-lasting matte finish in a rich, creamy formula.', 1200, 1500),
('Liquid Foundation', 'Makeup', 'Medium to full coverage with a natural finish.', 1800, NULL),
('Waterproof Mascara', 'Makeup', 'Volumising, smudge-proof mascara.', 950, 1200),
('Vitamin C Serum', 'Skincare', 'Brightening serum for an even skin tone.', 2400, 2900),
('Body Lotion', 'Skincare', 'Deep moisture for soft, glowing skin.', 850, NULL),
('Face Wash', 'Skincare', 'Gentle daily cleanser for all skin types.', 900, NULL),
('Argan Hair Oil', 'Hair', 'Nourishing oil that adds shine and reduces frizz.', 1400, 1700),
('Leave-in Conditioner', 'Hair', 'Detangles and protects hair all day.', 1100, NULL),
('Floral Perfume', 'Fragrance', 'A fresh floral scent with a warm base.', 3500, 4200),
('Body Mist', 'Fragrance', 'Light everyday fragrance spray.', 1300, NULL),
('Gel Nail Polish Set', 'Nails', 'Six glossy shades, chip resistant.', 1100, NULL),
('Lip Balm (test item)', 'Makeup', 'KES 1 item for testing M-Pesa payments.', 1, NULL);

INSERT INTO services (name, price, duration_min) VALUES
('Facial Treatment', 3000, 60),
('Makeup Application', 2500, 60),
('Bridal Makeup', 8000, 120),
('Hair Styling', 2000, 90),
('Manicure and Pedicure', 2500, 90);
-- No admin is inserted: visit /admin/login.php the first time to create one.
