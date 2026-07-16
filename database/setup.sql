-- VAR Cars Database Setup
-- Run this once in phpMyAdmin (or MySQL CLI) before launching the site.
--
-- ACCOUNTS SUMMARY
-- ─────────────────────────────────────────────────────────────
-- ADMIN (hardcoded in PHP — not in this table)
--   Email    : admin@varcars.com
--   Password : 123
--   Access   : /public/admin/index.php
--
-- SAMPLE USERS (inserted below, password = 1234)
--   nagiseishiro@email.com
--   isagiyoichi@email.com
-- ─────────────────────────────────────────────────────────────

CREATE DATABASE IF NOT EXISTS var_cars
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE var_cars;

-- Car catalogue shown in the store
CREATE TABLE IF NOT EXISTS vehicles (
    id           INT PRIMARY KEY,
    make         VARCHAR(100)   NOT NULL,
    model        VARCHAR(100)   NOT NULL,
    year         INT            NOT NULL,
    price        DECIMAL(15, 2) NOT NULL,
    type         VARCHAR(50)    NOT NULL,
    transmission VARCHAR(100)   NOT NULL,
    engine       VARCHAR(100)   NOT NULL,
    fuel         VARCHAR(50)    NOT NULL,
    img          VARCHAR(255)   NOT NULL
);

INSERT INTO vehicles (id, make, model, year, price, type, transmission, engine, fuel, img) VALUES
(1,  'BMW', '3 Series', 2024, 3990000.00, 'Sedan', '8-Speed Steptronic', '2.0L TwinPower Turbo', 'Gasoline', 'ST BMW 3 SERIES.jpg'),
(2,  'Mercedes-Benz', 'C-Class', 2024, 4290000.00, 'Sedan', '9G-TRONIC', '1.5L EQ Boost Mild Hybrid', 'Mild Hybrid', 'ST MERCEDEZ BENZ C-CLASS.jpg'),
(3,  'BMW', 'X5', 2024, 6590000.00, 'SUV', '8-Speed Steptronic', '3.0L B58 Inline-6 TwinPower', 'Gasoline', 'ST BMW X5.jpg'),
(4,  'Lexus', 'RX 350h', 2024, 5398000.00, 'SUV', 'E-CVT', '2.5L Hybrid', 'Hybrid', 'ST LEXUS RX.jpg'),
(5,  'Porsche', 'Cayenne', 2024, 8995000.00, 'SUV', '8-Speed Tiptronic S', '3.0L V6 Turbo', 'Gasoline', 'ST PORSCHE CAYENNE.webp'),
(6,  'Lamborghini', 'Urus S', 2024, 24500000.00, 'SUV', '8-Speed Automatic', '4.0L V8 Twin-Turbo', 'Gasoline', 'ST LAMBORGHINI URUS.png'),
(7,  'Ferrari', 'Roma', 2024, 23500000.00, 'Coupe', '8-Speed DCT', '3.9L V8 Twin-Turbo', 'Gasoline', 'ST FERRARI ROMA.jpg'),
(8,  'Bentley', 'Continental GT', 2024, 23800000.00, 'Coupe', '8-Speed Dual-Clutch', '6.0L W12 TSI', 'Gasoline', 'ST BENTLEY CONTINENTAL GT.jpg'),
(9,  'Rolls-Royce', 'Ghost', 2024, 35000000.00, 'Sedan', '8-Speed Automatic', '6.75L V12 Twin-Turbo', 'Gasoline', 'ST ROLLS ROYCE GHOST.jpg'),
(10, 'Aston Martin', 'DB12', 2024, 24900000.00, 'Coupe', '8-Speed Automatic', '4.0L V8 Twin-Turbo', 'Gasoline', 'ST ASTON MARTIN DB12.jpg'),
(11, 'Audi', 'R8', 2024, 15500000.00, 'Coupe', '7-Speed S tronic', '5.2L V10', 'Gasoline', 'ST AUDI R8.jpg'),
(12, 'Mercedes-Benz', 'AMG GT 63', 2024, 14990000.00, 'Coupe', '9-Speed AMG Speedshift', '4.0L V8 AMG Biturbo', 'Gasoline', 'ST MERCEDES AMG GT 63.jpg'),
(13, 'Porsche', '911 GT3 RS', 2024, 19990000.00, 'Coupe', '7-Speed PDK', '4.0L Naturally Aspirated Flat-6', 'Gasoline', 'ST Porsche 911 GT3 RS.png'),
(14, 'BMW', 'M3 Competition', 2024, 9190000.00, 'Sedan', '8-Speed M Steptronic', '3.0L S58 Inline-6 Twin-Turbo', 'Gasoline', 'ST BMW M3 Competition.png'),
(15, 'Audi', 'Q8', 2024, 7590000.00, 'SUV', '8-Speed Tiptronic', '3.0L TFSI V6', 'Gasoline', 'ST Audi Q8.jpg'),
(16, 'Lamborghini', 'Huracán EVO', 2024, 26500000.00, 'Coupe', '7-Speed LDF Dual-Clutch', '5.2L Naturally Aspirated V10', 'Gasoline', 'ST Lamborghini Huracán EVO.jpg'),
(17, 'Ferrari', 'SF90 Stradale', 2024, 50000000.00, 'Coupe', '8-Speed Dual-Clutch', '4.0L V8 Twin-Turbo + 3 Electric Motors', 'Hybrid', 'ST FERRARI ROMA.jpg'),
(18, 'Bentley', 'Flying Spur', 2024, 25500000.00, 'Sedan', '8-Speed Dual-Clutch', '4.0L V8 Twin-Turbo', 'Gasoline', 'ST Bentley Flying Spur.jpg'),
(19, 'Rolls-Royce', 'Phantom', 2024, 46000000.00, 'Sedan', '8-Speed Automatic', '6.75L V12 Twin-Turbo', 'Gasoline', 'ST Rolls-Royce Phantom.jpg'),
(20, 'Aston Martin', 'Vantage', 2024, 16500000.00, 'Coupe', '8-Speed Automatic', '4.0L V8 Twin-Turbo', 'Gasoline', 'ST Aston Martin Vantage.webp'),
(21, 'Mercedes-Benz', 'G 63 AMG', 2024, 16490000.00, 'SUV', '9-Speed AMG Speedshift', '4.0L V8 AMG Biturbo', 'Gasoline', 'ST Mercedes-Benz G 63 AMG.jpg');

-- Users registered through the site
CREATE TABLE IF NOT EXISTS users (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    full_name          VARCHAR(255) NOT NULL,
    email              VARCHAR(255) NOT NULL UNIQUE,
    password           VARCHAR(32)  NOT NULL,
    address            TEXT         NOT NULL,
    contact            VARCHAR(50)  NOT NULL,
    is_verified        TINYINT(1)   NOT NULL DEFAULT 0,
    verification_token VARCHAR(64)  NULL,
    created_at         DATETIME     DEFAULT CURRENT_TIMESTAMP
);

-- Saved cart contents per user (restored when they log back in)
CREATE TABLE IF NOT EXISTS cart_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    vehicle_id INT NOT NULL,
    added_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY user_vehicle (user_id, vehicle_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Orders placed at checkout
CREATE TABLE IF NOT EXISTS orders (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    buyer_name  VARCHAR(255)   NOT NULL,
    email       VARCHAR(255)   NOT NULL,
    total       DECIMAL(15, 2) NOT NULL,
    item_count  INT            NOT NULL,
    pay_method  VARCHAR(50)    NOT NULL,
    created_at  DATETIME       DEFAULT CURRENT_TIMESTAMP
);

-- Individual vehicles inside each order
CREATE TABLE IF NOT EXISTS order_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT            NOT NULL,
    vehicle_id INT            NOT NULL,
    make       VARCHAR(100)   NOT NULL,
    model      VARCHAR(100)   NOT NULL,
    price      DECIMAL(15, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- ─────────────────────────────────────────────────────────────
-- Sample user accounts (password for all: 1234)
-- MD5() here matches PHP's md5() so logins work
-- ─────────────────────────────────────────────────────────────
-- is_verified = 1 so sample accounts can log in immediately without going through email
INSERT INTO users (full_name, email, password, address, contact, is_verified) VALUES
(
    'Nagi Seishiro',
    'nagiseishiro@email.com',
    MD5('1234'),
    '628 EDSA Vivaldi Residences Cubao',
    '+63 912 345 6789',
    1
),
(
    'Isagi Yoichi',
    'isagiyoichi@email.com',
    MD5('1234'),
    '12 29 De Agosto San Juan City',
    '+63 912 345 6789',
    1
);