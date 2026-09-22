CREATE DATABASE IF NOT EXISTS vite_gourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vite_gourmand;

CREATE TABLE menus (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  theme ENUM('Noël','Pâques','Classique','Événement') NOT NULL DEFAULT 'Classique',
  diet ENUM('Classique','Végétarien','Vegan') NOT NULL DEFAULT 'Classique',
  min_people SMALLINT UNSIGNED NOT NULL,
  price DECIMAL(8,2) NOT NULL,
  stock SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  image_url VARCHAR(500) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO menus (title, description, theme, diet, min_people, price, stock, image_url) VALUES
('Menu Bordelais', 'Une cuisine française généreuse : entrée, plat et dessert de saison.', 'Classique', 'Classique', 4, 32.00, 12, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=800&q=80'),
('Menu Jardin', 'Une proposition végétarienne fraîche et colorée, pensée pour tous les convives.', 'Événement', 'Végétarien', 6, 29.00, 8, 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=800&q=80'),
('Menu Fêtes', 'Un menu raffiné pour célébrer les grands moments autour d’une table.', 'Noël', 'Classique', 8, 46.00, 5, 'https://images.unsplash.com/photo-1515003197210-e0cd71810b5f?auto=format&fit=crop&w=800&q=80'),
('Menu Végétal', 'Cuisine vegan de saison, savoureuse et généreuse.', 'Classique', 'Vegan', 4, 27.50, 10, 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800&q=80');
