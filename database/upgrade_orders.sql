USE vite_gourmand;

CREATE TABLE customer_orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  menu_id INT UNSIGNED NOT NULL,
  quantity SMALLINT UNSIGNED NOT NULL,
  delivery_address VARCHAR(255) NOT NULL,
  delivery_date DATE NOT NULL,
  delivery_time TIME NOT NULL,
  distance_km DECIMAL(6,2) NOT NULL DEFAULT 0,
  menu_total DECIMAL(10,2) NOT NULL,
  discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  delivery_fee DECIMAL(10,2) NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  status ENUM('pending','accepted','preparing','delivering','delivered','waiting_equipment_return','completed','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_order_menu FOREIGN KEY (menu_id) REFERENCES menus(id)
);

CREATE TABLE order_status_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  status ENUM('pending','accepted','preparing','delivering','delivered','waiting_equipment_return','completed','cancelled') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_history_order FOREIGN KEY (order_id) REFERENCES customer_orders(id) ON DELETE CASCADE
);
