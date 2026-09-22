USE vite_gourmand;

CREATE TABLE opening_hours (
  day_of_week TINYINT UNSIGNED PRIMARY KEY,
  opening_time TIME NULL,
  closing_time TIME NULL,
  is_closed TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT chk_day_of_week CHECK (day_of_week BETWEEN 1 AND 7)
);

-- Horaires de démonstration : à confirmer avec Julie et José.
INSERT INTO opening_hours (day_of_week, opening_time, closing_time, is_closed) VALUES
(1, '09:00', '18:00', 0),
(2, '09:00', '18:00', 0),
(3, '09:00', '18:00', 0),
(4, '09:00', '18:00', 0),
(5, '09:00', '18:00', 0),
(6, '09:00', '18:00', 0),
(7, '09:00', '18:00', 0);
