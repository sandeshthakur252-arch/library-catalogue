-- Run in phpMyAdmin / mysql client against your own database (it must already exist on mi-linux).
DROP TABLE IF EXISTS books; DROP TABLE IF EXISTS genres; DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE genres (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE books (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  author VARCHAR(120) NOT NULL,
  genre_id INT UNSIGNED NOT NULL,
  year SMALLINT UNSIGNED NOT NULL,
  isbn VARCHAR(17) NULL UNIQUE,
  description TEXT NULL,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (genre_id) REFERENCES genres(id),
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (title), INDEX (author), INDEX (year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO genres (name) VALUES ('Sci-Fi'),('Fantasy'),('Mystery'),('Romance'),('History'),('Technology'),('Biography');

INSERT INTO books (title, author, genre_id, year, isbn, description) VALUES
('Starfall Protocol','Mara Quinn',1,2023,'9781000000011','A salvage crew finds a signal that should not exist.'),
('The Last Orbital Garden','Idris Vale',1,2023,'9781000000028','Botany and rebellion aboard a dying station.'),
('Neon Meridian','Aiko Tanabe',1,2021,'9781000000035','Cyberpunk noir across a flooded Tokyo.'),
('Embers of Aldoria','Tomas Reyes',2,2022,'9781000000042','A reluctant heir inherits a dragon-haunted throne.'),
('The Glass Cartographer','Helena Marsh',2,2023,'9781000000059','Maps that rewrite the land they describe.'),
('Case of the Silent Clock','Peter Hollis',3,2020,'9781000000066','A village, a stopped clock, and three suspects.'),
('Letters from Lisbon','Sofia Almeida',4,2019,'9781000000073','Two strangers, one year of handwritten letters.'),
('Empires of Salt','Daniel Kerr',5,2018,'9781000000080','How salt shaped trade routes and wars.'),
('Practical Cloud Security','Priya Nair',6,2023,'9781000000097','Defence in depth for modern web applications.'),
('Learning SQL the Hard Way','Chris Bowen',6,2022,'9781000000103','Relational thinking through real mistakes.'),
('A Life in Mountains','Karma Sherpa',7,2017,'9781000000110','Memoir of a Himalayan guide.'),
('Orbit of Glass','Mara Quinn',1,2024,'9781000000127','Sequel to Starfall Protocol.');
