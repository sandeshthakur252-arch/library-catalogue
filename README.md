# Library Catalogue (PHP + MySQL + Twig + Ajax)

## Features
- CRUD on books; search by keyword + genre + year range (e.g. all Sci-Fi books from 2023), paginated
- Ajax: autocomplete search suggestions; live ISBN format/duplicate check on the form
- Twig templates (`templates/`) separate markup from PHP logic
- Security: PDO prepared statements, Twig autoescape, CSRF tokens on every POST, bcrypt passwords,
  session fixation protection, POST-only delete, server-side validation, CSP and security headers

## Database update
Existing databases must run `sql/migrations/001_create_login_attempts.sql` once before using the updated login page.
The full `sql/schema.sql` recreates the tables and sample data; do not run it on a database whose data must be preserved.
Login attempts are limited to five per username and client IP within each 15-minute window.
