CREATE TABLE IF NOT EXISTS login_attempts (
  bucket_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
  attempts SMALLINT UNSIGNED NOT NULL,
  window_started_at DATETIME NOT NULL,
  locked_until DATETIME NULL,
  INDEX (window_started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
