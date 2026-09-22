USE vite_gourmand;

ALTER TABLE menus
  ADD COLUMN conditions VARCHAR(500) NOT NULL DEFAULT 'Commande au moins 3 jours avant la prestation.',
  ADD COLUMN lead_days SMALLINT UNSIGNED NOT NULL DEFAULT 3;

CREATE TABLE dishes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description VARCHAR(255) NOT NULL DEFAULT '',
  course ENUM('Entrée','Plat','Dessert') NOT NULL
);

CREATE TABLE menu_dishes (
  menu_id INT UNSIGNED NOT NULL,
  dish_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (menu_id, dish_id),
  FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
  FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE
);

CREATE TABLE allergens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE dish_allergens (
  dish_id INT UNSIGNED NOT NULL,
  allergen_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (dish_id, allergen_id),
  FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE,
  FOREIGN KEY (allergen_id) REFERENCES allergens(id) ON DELETE CASCADE
);

CREATE TABLE menu_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id INT UNSIGNED NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  alt_text VARCHAR(160) NOT NULL,
  FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
);

INSERT INTO allergens (name) VALUES ('Gluten'), ('Lait'), ('Œufs'), ('Fruits à coque');

INSERT INTO dishes (name, description, course) VALUES
('Velouté de potimarron', 'Potimarron et crème.', 'Entrée'),
('Salade de lentilles', 'Lentilles et légumes de saison.', 'Entrée'),
('Quiche festive', 'Quiche au fromage.', 'Entrée'),
('Poulet rôti aux herbes', 'Poulet et légumes rôtis.', 'Plat'),
('Gratin de légumes', 'Légumes gratinés au fromage.', 'Plat'),
('Curry de légumes', 'Légumes et épices douces.', 'Plat'),
('Fondant au chocolat', 'Dessert au chocolat.', 'Dessert'),
('Compote de pommes', 'Pommes cuites de saison.', 'Dessert');

INSERT INTO dish_allergens (dish_id, allergen_id)
SELECT d.id, a.id FROM dishes d JOIN allergens a ON
  (d.name = 'Velouté de potimarron' AND a.name = 'Lait') OR
  (d.name = 'Quiche festive' AND a.name IN ('Gluten','Lait','Œufs')) OR
  (d.name = 'Gratin de légumes' AND a.name = 'Lait') OR
  (d.name = 'Fondant au chocolat' AND a.name IN ('Gluten','Lait','Œufs'));

INSERT INTO menu_dishes (menu_id, dish_id)
SELECT m.id, d.id FROM menus m JOIN dishes d ON
  (m.title = 'Menu Bordelais' AND d.name IN ('Velouté de potimarron','Poulet rôti aux herbes','Fondant au chocolat')) OR
  (m.title = 'Menu Jardin' AND d.name IN ('Salade de lentilles','Gratin de légumes','Compote de pommes')) OR
  (m.title = 'Menu Fêtes' AND d.name IN ('Quiche festive','Poulet rôti aux herbes','Fondant au chocolat')) OR
  (m.title = 'Menu Végétal' AND d.name IN ('Salade de lentilles','Curry de légumes','Compote de pommes'));

INSERT INTO menu_images (menu_id, image_url, alt_text)
SELECT id, image_url, CONCAT('Illustration du ', title) FROM menus;

UPDATE menus SET conditions = 'Commander au moins 7 jours avant la prestation.', lead_days = 7 WHERE title = 'Menu Fêtes';
