CREATE DATABASE IF NOT EXISTS luxury_watches CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE luxury_watches;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(80) NOT NULL,
    name VARCHAR(160) NOT NULL,
    description TEXT,
    price DECIMAL(12,2) NULL,
    image_url VARCHAR(500) NOT NULL,
    condition_label VARCHAR(80) NOT NULL,
    year SMALLINT UNSIGNED NULL,
    status ENUM('available','sold','rented','hidden') NOT NULL DEFAULT 'available',
    featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inquiries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('contact','valuation','rental') NOT NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(40) NULL,
    subject VARCHAR(190) NULL,
    details TEXT,
    status ENUM('new','in_progress','closed') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(120) NOT NULL,
    customer_email VARCHAR(190) NOT NULL,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('pending','contacted','paid','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(160) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    excerpt TEXT,
    content MEDIUMTEXT NOT NULL,
    image_url VARCHAR(500) NULL,
    category VARCHAR(80) NOT NULL DEFAULT 'Journal',
    published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO products (brand, name, description, price, image_url, condition_label, year, status, featured)
SELECT * FROM (SELECT 'Rolex', 'GMT-Master II “Pepsi”', 'Reference 126710BLRO with red and blue Cerachrom bezel.', 18950, 'https://images.unsplash.com/photo-1547996160-81dfa63595aa?auto=format&fit=crop&w=900&q=85', 'Unworn', 2024, 'available', 1) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'GMT-Master II “Pepsi”');

INSERT INTO products (brand, name, description, price, image_url, condition_label, year, status, featured)
SELECT * FROM (SELECT 'Audemars Piguet', 'Royal Oak 15500ST', 'Blue dial Royal Oak presented as a complete set.', 41500, 'https://images.unsplash.com/photo-1592496001020-d31bd830651f?auto=format&fit=crop&w=900&q=85', 'New / unworn', 2024, 'available', 1) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Royal Oak 15500ST');

INSERT INTO products (brand, name, description, price, image_url, condition_label, year, status, featured)
SELECT * FROM (SELECT 'Cartier', 'Santos de Cartier Large', 'Steel Santos with box and papers.', 8750, 'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=900&q=85', 'Excellent condition', 2022, 'available', 0) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Santos de Cartier Large');

INSERT INTO products (brand, name, description, price, image_url, condition_label, year, status, featured)
SELECT * FROM (SELECT 'Omega', 'Speedmaster Moonwatch', 'The classic Hesalite Moonwatch, presented as a full set.', 6900, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=900&q=85', 'Recently serviced', 2021, 'available', 0) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Speedmaster Moonwatch');

INSERT INTO products (brand, name, description, price, image_url, condition_label, year, status, featured)
SELECT * FROM (SELECT 'IWC', 'Portugieser Chronograph', 'A refined Portugieser chronograph with a silver dial.', 7450, 'https://images.unsplash.com/photo-1533139502658-0198f920d8e8?auto=format&fit=crop&w=900&q=85', 'Excellent condition', 2020, 'available', 0) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Portugieser Chronograph');

INSERT INTO products (brand, name, description, price, image_url, condition_label, year, status, featured)
SELECT * FROM (SELECT 'Rolex', 'Submariner Date 126610LN', 'The modern Submariner Date with a black Cerachrom bezel and Oyster bracelet.', 15750, 'https://images.unsplash.com/photo-1587836374828-4dbafa94cf0e?auto=format&fit=crop&w=900&q=85', 'Excellent condition', 2023, 'available', 0) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Submariner Date 126610LN');

INSERT INTO products (brand, name, description, price, image_url, condition_label, year, status, featured)
SELECT * FROM (SELECT 'Patek Philippe', 'Calatrava 6119R', 'A refined rose-gold Calatrava with a textured hobnail bezel.', 29500, 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=900&q=85', 'New / unworn', 2022, 'available', 0) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Calatrava 6119R');

INSERT INTO products (brand, name, description, price, image_url, condition_label, year, status, featured)
SELECT * FROM (SELECT 'Tudor', 'Black Bay Fifty-Eight', 'A compact vintage-inspired diver with a black dial and bezel.', 3950, 'https://images.unsplash.com/photo-1547996160-81dfa63595aa?auto=format&fit=crop&w=900&q=85', 'Excellent condition', 2021, 'available', 0) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Black Bay Fifty-Eight');

INSERT INTO products (brand, name, description, price, image_url, condition_label, year, status, featured)
SELECT * FROM (SELECT 'Vacheron Constantin', 'Overseas 4500V', 'A blue-dial Overseas with an interchangeable bracelet system.', 31800, 'https://images.unsplash.com/photo-1612817159949-195b6eb9e31a?auto=format&fit=crop&w=900&q=85', 'Excellent condition', 2022, 'available', 0) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Overseas 4500V');
