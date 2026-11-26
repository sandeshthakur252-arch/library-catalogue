# Library Catalogue (PHP + MySQL + Twig + Ajax)

## Deploy on mi-linux
1. Upload this folder to `public_html/<folder>` (keep the `vendor/` folder - Twig is bundled, no Composer needed).
2. Edit `config.php` with your MySQL host, database, username and password.
3. Import `sql/schema.sql` via phpMyAdmin (creates tables and sample data).
4. Visit the site, click Register, create an account, log in. Browsing/searching is public; add/edit/delete need login.

## Features
- CRUD on books; search by keyword + genre + year range (e.g. all Sci-Fi books from 2023), paginated
- Ajax: autocomplete search suggestions; live ISBN format/duplicate check on the form
- Twig templates (`templates/`) separate markup from PHP logic
- Security: PDO prepared statements, Twig autoescape, CSRF tokens on every POST, bcrypt passwords,
  session fixation protection, POST-only delete, server-side validation, CSP and security headers
