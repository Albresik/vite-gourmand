USE vite_gourmand;

CREATE TABLE order_contact_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  employee_id INT UNSIGNED NOT NULL,
  contact_method ENUM('phone','email') NOT NULL,
  note VARCHAR(500) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES customer_orders(id) ON DELETE CASCADE,
  FOREIGN KEY (employee_id) REFERENCES users(id)
);
