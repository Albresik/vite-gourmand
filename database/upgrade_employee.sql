USE vite_gourmand;

ALTER TABLE customer_orders
  ADD COLUMN cancel_contact_method ENUM('phone','email') NULL,
  ADD COLUMN cancel_reason VARCHAR(500) NULL;
