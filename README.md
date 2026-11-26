# Library Catalogue (PHP + MySQL + Twig + Ajax)

# Library Catalogue

A PHP and MySQL book catalogue using Twig templates and JavaScript/Ajax.

## Features

- Browse, view, add, edit, and delete books. Adding requires login; users can edit or delete only books they created.
- Search by title/author, genre, and year range, with pagination.
- Ajax autocomplete for title/author and live ISBN format/duplicate checks.
- Twig templates separate presentation from PHP request handling.
- Prepared PDO queries, HTML autoescaping, CSRF tokens on POST forms, password hashing, session ID regeneration, server-side validation, login throttling, and security headers.

## Requirements

- PHP with the PDO MySQL extension and sessions enabled.
- MySQL with permission to create a database and tables.
- A web server configured to serve PHP. The included `.htaccess` protections require Apache with `AllowOverride` enabled; configure equivalent rules if the host uses another server.

## Setup with XAMPP

1. Copy the project folder into XAMPP's `htdocs` directory (for example, `C:\xampp\htdocs\library-catalogue`).
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`, choose **Import**, select `sql/schema.sql`, and run the import. The script creates and selects the `library_catalogue` database, tables, genres, and sample books. It does not drop existing tables or data.
4. The included local `config.php` uses XAMPP's default MySQL connection: host `127.0.0.1`, port `3306`, database `library_catalogue`, user `root`, and a blank password. If your MySQL root account has a password or uses a different port, update `DB_PASS` or `DB_PORT` in `config.php`. For a fresh checkout, copy `config.sample.php` to `config.php` first.
5. Open `http://localhost/library-catalogue/`, register an account, and try browsing, searching, and adding a book.

Keep local database credentials private and do not commit `config.php`. For a public deployment, use a dedicated database account with a strong password and HTTPS; do not use the XAMPP root account.

For an existing installation, importing the schema creates missing tables and ignores duplicate sample genres/books; it does not change existing table definitions or data. Apply `sql/migrations/001_create_login_attempts.sql` once if the `login_attempts` table is missing.

## Authentication and ownership

Registration is open. Passwords are stored with PHP's `password_hash()` using the runtime's default algorithm, and login uses `password_verify()`. Login attempts are limited to five per username/IP bucket within a 15-minute window. Book writes require login; only the creator can amend or delete a book. Seed or legacy books without a creator are read-only.
