# DWMS 2.0 — Digital Workforce Management System

An MVP workforce portal built for **shared hosting**: plain PHP 7.4+ with a small
front controller, MySQL/MariaDB, a compiled Tailwind CSS theme and Alpine.js.
No Composer, no Node runtime and no build step are needed on the server —
only PHP and MySQL.

Every URL is extension-less (`/jobs`, `/dashboard/kyc`), routed through
`index.php` by `.htaccess`.

---

## Stack

| Layer     | Choice                                              |
|-----------|-----------------------------------------------------|
| Language  | PHP 7.4+ (no framework, PSR-4 style autoloader)     |
| Database  | MySQL 5.7+ / MariaDB 10.3+ via PDO prepared statements |
| CSS       | Tailwind CSS 3, compiled to `assets/css/app.css`    |
| JS        | Alpine.js 3, vendored at `assets/js/alpine.min.js`  |
| Theme     | LinkedIn-inspired palette (`#0a66c2` / `#004182` / `#f4f2ee`) |

## Installing on a shared server

1. Upload the repository contents into the web root (`public_html`), or into a
   sub folder.
2. Create a MySQL database and user in cPanel, and grant the user all privileges.
3. Copy `app/config.sample.php` to `app/config.php` and fill in the database
   credentials. If the project sits in a sub folder, set `base_url`, e.g.
   `'base_url' => '/dwms'`.
4. Make `uploads/` writable (`chmod 755`, or `775` on stricter hosts).
5. Visit `https://your-domain/setup`, confirm the checks are green and press
   **Install now** — this creates every table and loads demo content.
6. Sign in and change the default passwords, then remove the two `/setup`
   routes from `app/routes.php`.

### Default sign-ins (demo data)

| Role       | URL                | E-mail              | Password      |
|------------|--------------------|---------------------|---------------|
| Super admin| `/official/login`  | admin@dwms.local    | `Admin@12345` |
| Employer   | `/employer/login`  | hr@technova.in      | `Employer@123`|
| Job seeker | `/login`           | seeker@dwms.local   | `Seeker@123`  |

Change all three before the site is reachable publicly.

## Local development

```bash
php -S localhost:8000 server.php     # dev router; Apache uses .htaccess instead
npm install                          # only if you want to change the theme
npm run watch                        # rebuild assets/css/app.css on change
```

`assets/css/app.css` is committed, so the CSS toolchain is optional.

## Layout

```
index.php              front controller — every request enters here
.htaccess              extension-less URL rewriting + hardening
server.php             router for PHP's built-in server (development only)
app/
  bootstrap.php        autoloader, session, error handling
  config.sample.php    copy to config.php and edit
  routes.php           route table
  helpers.php          url(), view(), validate(), store_upload(), icon() …
  Core/                Database, Router, Auth, Lookup, icons
  Controllers/         one controller per feature area
  Views/               layouts, partials and page templates
assets/                compiled css, js and svg
database/              schema.sql + seed.sql
uploads/               user uploads (photos, documents, resumes, logos)
```

## Security notes

- Passwords are stored with `password_hash()` (bcrypt).
- Every state-changing form carries a CSRF token, verified server side.
- All queries use PDO prepared statements.
- `app/`, `database/` and `uploads/` carry `.htaccess` rules; uploads have PHP
  execution disabled.
- Sessions are HTTP-only, `SameSite=Lax`, and expire after inactivity.
- Aadhaar numbers are never stored — only the masked reference and the
  recorded consent (see *Deferred integrations*).

## Deferred integrations

These are stubbed with complete UI and database structure so the real service
can be dropped in without a schema change:

- **Aadhaar e-KYC** — consent, OTP request and OTP verification screens are
  live; `KycController` currently issues a demo OTP instead of calling UIDAI.
- **Resume parsing** — resumes upload and store with a `parse_status` of
  `pending` and a `parsed_data` column reserved for the extracted fields.
- **E-mail / SMS delivery** — OTPs are generated and stored; with
  `mail.demo_otp = true` they are shown on screen instead of being sent.
