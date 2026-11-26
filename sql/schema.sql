-- Import this file in phpMyAdmin or the MySQL client.
-- It creates the application database and does not drop existing tables or data.
CREATE DATABASE IF NOT EXISTS library_catalogue
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE library_catalogue;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
  bucket_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
  attempts SMALLINT UNSIGNED NOT NULL,
  window_started_at DATETIME NOT NULL,
  locked_until DATETIME NULL,
  INDEX (window_started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS genres (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS books (
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

INSERT IGNORE INTO genres (name) VALUES
  ('Sci-Fi'),('Fantasy'),('Mystery'),('Romance'),('History'),('Technology'),('Biography');

INSERT IGNORE INTO books (title, author, genre_id, year, isbn, description)
SELECT 'Starfall Protocol','Mara Quinn',id,2023,'9781000000011','A salvage crew finds a signal that should not exist.' FROM genres WHERE name = 'Sci-Fi'
UNION ALL SELECT 'The Last Orbital Garden','Idris Vale',id,2023,'9781000000028','Botany and rebellion aboard a dying station.' FROM genres WHERE name = 'Sci-Fi'
UNION ALL SELECT 'Neon Meridian','Aiko Tanabe',id,2021,'9781000000035','Cyberpunk noir across a flooded Tokyo.' FROM genres WHERE name = 'Sci-Fi'
UNION ALL SELECT 'Embers of Aldoria','Tomas Reyes',id,2022,'9781000000042','A reluctant heir inherits a dragon-haunted throne.' FROM genres WHERE name = 'Fantasy'
UNION ALL SELECT 'The Glass Cartographer','Helena Marsh',id,2023,'9781000000059','Maps that rewrite the land they describe.' FROM genres WHERE name = 'Fantasy'
UNION ALL SELECT 'Case of the Silent Clock','Peter Hollis',id,2020,'9781000000066','A village, a stopped clock, and three suspects.' FROM genres WHERE name = 'Mystery'
UNION ALL SELECT 'Letters from Lisbon','Sofia Almeida',id,2019,'9781000000073','Two strangers, one year of handwritten letters.' FROM genres WHERE name = 'Romance'
UNION ALL SELECT 'Empires of Salt','Daniel Kerr',id,2018,'9781000000080','How salt shaped trade routes and wars.' FROM genres WHERE name = 'History'
UNION ALL SELECT 'Practical Cloud Security','Priya Nair',id,2023,'9781000000097','Defence in depth for modern web applications.' FROM genres WHERE name = 'Technology'
UNION ALL SELECT 'Learning SQL the Hard Way','Chris Bowen',id,2022,'9781000000103','Relational thinking through real mistakes.' FROM genres WHERE name = 'Technology'
UNION ALL SELECT 'A Life in Mountains','Karma Sherpa',id,2017,'9781000000110','Memoir of a Himalayan guide.' FROM genres WHERE name = 'Biography'
UNION ALL SELECT 'Orbit of Glass','Mara Quinn',id,2024,'9781000000127','Sequel to Starfall Protocol.' FROM genres WHERE name = 'Sci-Fi';
