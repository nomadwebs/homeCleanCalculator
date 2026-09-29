CREATE DATABASE IF NOT EXISTS home_clean_calculator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE home_clean_calculator;

-- Configuración general (una sola fila, id = 1)
CREATE TABLE IF NOT EXISTS settings (
  id TINYINT UNSIGNED PRIMARY KEY DEFAULT 1,
  hourly_rate DECIMAL(8,2) NOT NULL DEFAULT 10.00,
  default_hours DECIMAL(4,2) NOT NULL DEFAULT 4.00,
  region VARCHAR(6) NULL COMMENT 'Código ISO de comunidad autónoma, p. ej. ES-MD'
) ENGINE=InnoDB;
INSERT IGNORE INTO settings (id) VALUES (1);

-- Un pedido por mes
CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  year SMALLINT UNSIGNED NOT NULL,
  month TINYINT UNSIGNED NOT NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  total DECIMAL(10,2) NULL COMMENT 'Se congela al cerrar el pedido',
  closed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_year_month (year, month)
) ENGINE=InnoDB;

-- Una línea por día trabajado (la tarifa se copia de la configuración al crearla)
CREATE TABLE IF NOT EXISTS order_lines (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  work_date DATE NOT NULL,
  hours DECIMAL(4,2) NOT NULL,
  hourly_rate DECIMAL(8,2) NOT NULL,
  paid_at DATETIME NULL COMMENT 'NULL = pendiente de pago',
  payment_id INT UNSIGNED NULL,
  UNIQUE KEY uq_order_date (order_id, work_date),
  CONSTRAINT fk_line_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Festivos (importados de la API o añadidos a mano)
CREATE TABLE IF NOT EXISTS holidays (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  holiday_date DATE NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  source ENUM('api','manual') NOT NULL DEFAULT 'manual'
) ENGINE=InnoDB;

-- Pagos realmente entregados (pueden no coincidir con el importe de los días: sin cambio, redondeos...)
CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  paid_on DATE NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  note VARCHAR(200) NULL,
  CONSTRAINT fk_pay_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;
