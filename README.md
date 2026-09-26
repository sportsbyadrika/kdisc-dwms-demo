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
| Theme     | Indigo-violet palette (`#5b4fc7` / `#3e3596` / `#f5f4fd`), white surfaces |

## Configuration

Everything is read from a `.env` file at the project root — there is no build
step and no Composer:

```bash
cp .env.example .env      # then edit DB_NAME, DB_USER, DB_PASS
```

`.env` is git-ignored and denied by `.htaccess`, so it is never committed and
never readable over the web. Real environment variables (a cPanel `SetEnv`, for
example) take precedence over the file.

| Key | Purpose |
|-----|---------|
| `APP_BASE_URL` | Empty at a domain or sub-domain root; `/dwms` in a sub folder |
| `APP_DEBUG` | `false` in production — `true` prints file paths in errors |
| `DB_*` | Database host, name, user, password |
| `MAIL_DEMO_OTP` | `true` shows OTPs on screen; set `false` once mail works |
| `SESSION_TTL` | Idle seconds before a session expires |
| `MAX_UPLOAD_MB` | Largest accepted upload; keep at or below the host's limit |

If a host cannot keep a `.env`, `app/config.php` (copied from
`app/config.sample.php`) is used instead when present.

## Deploying with cPanel Git Version Control

`.cpanel.yml` is included and is set up for:

| | |
|---|---|
| Repository path | `/home/shooting/repositories/kdisc-dwms-demo` |
| Deployment path | `/home/shooting/public_html/dwms.kdiscmis.org.in` |

Both paths are literal in `.cpanel.yml` — change them there if the account or
sub-domain differs.

1. **cPanel → Git™ Version Control → Create**, clone the repository to
   `/home/shooting/repositories/kdisc-dwms-demo`.
2. **cPanel → MySQL® Databases**: create the database and user, add the user to
   the database with ALL PRIVILEGES. Both names carry the account prefix, e.g.
   `shooting_dwms` and `shooting_dwmsuser`.
3. **Manage → Pull or Deploy → Update from Remote**, then **Deploy HEAD Commit**.
   The first deployment creates the directory tree and copies `.env.example` to
   `.env`.
4. Edit `/home/shooting/public_html/dwms.kdiscmis.org.in/.env` with the real
   database credentials (File Manager → Show Hidden Files, or over SSH).
5. Visit `https://dwms.kdiscmis.org.in/setup`, check the requirements are green
   and press **Install now**.
6. Sign in, change the three default passwords, then remove the two `/setup`
   routes from `app/routes.php` and set `APP_DEBUG=false` and
   `MAIL_DEMO_OTP=false` in `.env`.

Every later deployment is just **Update from Remote → Deploy HEAD Commit**.
The deployment never overwrites `.env` (`cp -n`) and never clears `uploads/`,
so credentials and user files survive.

A deployment copies files only — it never touches the database. If the commit
you deployed adds tables or columns, apply them afterwards as described in
[Database updates](#database-updates-migrations).

## Installing manually (without cPanel Git)

1. Upload the repository contents into the web root, or into a sub folder.
2. Create a MySQL database and user, and grant the user all privileges.
3. `cp .env.example .env` and fill in the database credentials. If the project
   sits in a sub folder, set `APP_BASE_URL=/dwms` and uncomment `RewriteBase`
   in `.htaccess`.
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

## Database updates (migrations)

`database/schema.sql` describes the tables a *fresh* install creates. Anything
added after a site went live ships as a numbered file in
`database/migrations/`, and an applied file is recorded in the `migrations`
table so it is never run twice.

Apply pending updates in whichever way suits the server:

- **From the browser** — visit `/setup`. The **Database updates** card lists
  every pending file with an **Apply** button, and turns green once there is
  nothing left. Fresh installs run the migrations automatically.
- **From the shell** — `php database/migrate.php`. Use this on a site where the
  two `/setup` routes have already been removed, as the install steps advise.
  The script refuses to run over HTTP.

Adding an update is just a new `database/migrations/YYYY_MM_DD_NN_name.sql`.
Write it so that running it twice is harmless — the shipped files guard every
`ALTER TABLE` with an `information_schema` check and use
`INSERT … ON DUPLICATE KEY UPDATE`.

## User groups

A user carries three independent attributes:

| | |
|---|---|
| **Role** | what they may do — the permission list |
| **Office** | where they sit in the offices → departments → sections tree |
| **Group** | which kind of organisation or function they belong to |

Groups exist because the first two do not answer "show me every placement
coordinator": a coordinator sits inside a college office and holds an ordinary
role, so the grouping has to be stored separately. Nine groups ship in
`user_groups`:

Government Departments · Universities · Educational Institutions · Programme
Executives · Placement Coordinators · District Officials · Job Stations ·
SDPK Centers · K-DISC Officials

`/official/users` shows them as cards with a live count; selecting a card
filters the table to that group, and **No group** appears only while some user
is still unassigned. The group is also a field on the user form, and it
defaults to whichever card is currently selected when a new user is added.
Groups live in a table, so more can be added without a code change.

### Kerala demo dataset

`database/migrations/2026_09_26_02_kerala_demo_dataset.sql` populates all nine
groups with roughly 120 Kerala offices and one contact account each — the
universities (Digital University Kerala, KTU, MG, Calicut, Kerala, Kannur,
CUSAT, Kerala Agricultural, Veterinary, Fisheries, Health Sciences, Sanskrit …),
government and aided and self-financing engineering colleges, polytechnics,
ITIs, arts and science colleges, medical and paramedical colleges, district
employment exchanges, job stations and SDPK centres.

Read before you deploy it:

- The **institutions are real**; every **person, e-mail address and mobile
  number is invented** for the demo.
- Demo accounts **cannot sign in**. They hold a bcrypt hash of a random string
  nobody has, and `must_reset = 1`. An administrator has to issue a password
  before any of them becomes usable.
- Job station and SDPK centre names are **representative placeholders keyed to
  a location**, not a verified published list.
- To keep a production site clean, delete the file before deploying — or, if it
  has already been applied, delete the accounts from `/official/users`.

## Local development

```bash
cp .env.example .env                 # point DB_* at a local MySQL
php -S localhost:8000 server.php     # dev router; Apache uses .htaccess instead
npm install                          # only if you want to change the theme
npm run watch                        # rebuild assets/css/app.css on change
```

`server.php` mirrors the `.htaccess` denials, so a file that would be exposed
in production is refused locally too.

`assets/css/app.css` is committed, so the CSS toolchain is optional.

## What is in the MVP

### Public
- Home page with an admin-configurable hero panel, the three **Jobs / Skills /
  Career Services** cards, live counts, latest vacancies and a full footer.
- Job search with a keyword bar and an e-commerce style facet panel (category,
  district, employment type, work mode, qualification, experience, salary).
  Facet counts reflect what a click actually returns.
- Job detail pages render the employer's **curation sheet** as a structured
  table. Applying while signed out parks the vacancy, opens a login modal that
  names the job and links to registration, then returns the visitor to it.
- Skilling programmes and career services get the same treatment, with their
  own facets, detail pages and interest / booking actions.
- About, Contact (with enquiry capture), FAQ, For employers, Privacy, Terms,
  Accessibility and Sitemap.

### Job seekers
- Three-step registration wizard: e-mail → one-time password → basic profile
  (name, photograph, mobile number, password). Nothing is written to the
  database until the address is verified.
- Dashboard showing e-mail, mobile and e-KYC status with verified badges, a
  weighted profile-completeness meter, applications, saved jobs and
  district-aware recommendations.
- **e-KYC**: Aadhaar number with a local Verhoeff check-digit validation, an
  explicit consent statement naming the department the details are shared with
  (recorded with a timestamp), Send OTP, OTP entry and Submit.
- Profile: addresses, documents and proofs, resumes, qualifications,
  experience, certifications, achievements and skills.
- Application tracking with a status track, and withdrawal.

### Employers
- Three-step sign-up, then a four-step organisation profile wizard
  (details → statutory → address and contact → review and submit) with PAN and
  GSTIN format validation.
- Four-step **job curation sheet** wizard (role → eligibility → engagement →
  process and publish), saved progressively so nothing is lost part way.
- Per-vacancy applicant list with resume, structured profile, match score and
  status updates the candidate sees; dashboard with a 14-day application trend
  and a per-job funnel.

### Officials
- Role-based access: each role carries a permission list, and both the sidebar
  and every action are gated by it.
- Super admin creates **offices → departments → sections** as one tree, then
  users against them. A new user gets a one-time password shown once and must
  change it at first sign-in.
- Users carry a **group** as well as a role and an office. The users page leads
  with a card per group, each tagged with its count, and selecting one filters
  the table — see [User groups](#user-groups).
- Roles and permissions are editable; the super administrator role always keeps
  full access.
- Employer verification (verify / reject / suspend, with a recorded note),
  job moderation, the job seeker registry with document verification,
  enquiries, and site settings.
- Home page hero panel, skilling programmes and career services are all
  content-managed with a draft → published → archived workflow.

## Layout

```
index.php              front controller — every request enters here
.htaccess              extension-less URL rewriting + hardening
.env.example           copy to .env on the server and fill in
.cpanel.yml            cPanel Git Version Control deployment tasks
server.php             router for PHP's built-in server (development only)
app/
  bootstrap.php        autoloader, .env loading, session, error handling
  config.sample.php    reads every value from .env
  routes.php           route table
  helpers.php          env(), url(), view(), validate(), store_upload(), icon() …
  Core/                Env, Database, Router, Auth, Lookup, Migrator, icons
  Controllers/         one controller per feature area
  Views/               layouts, partials and page templates
  .htaccess            denies direct web access
assets/                compiled css, js and svg (+ .htaccess: no execution)
database/              schema.sql + seed.sql (+ .htaccess: denied)
  migrations/          numbered .sql files applied once and recorded
  migrate.php          CLI runner for those files
uploads/               user uploads (+ .htaccess: never executed)
```

## Security notes

- Passwords are stored with `password_hash()` (bcrypt).
- Every state-changing form carries a CSRF token, verified server side.
- All queries use PDO prepared statements.
- `app/`, `database/`, `assets/` and `uploads/` each carry `.htaccess` rules;
  nothing under `uploads/` or `assets/` is ever executed.
- The document root denies every dotfile — `.env` above all — plus `.yml`,
  `.json`, `.md`, `.sql` and `.log`, using both mod_rewrite and `<FilesMatch>`
  so it holds with or without mod_rewrite. `.well-known/` stays reachable for
  SSL renewal. Verified against Apache 2.4.
- Sessions are HTTP-only, `SameSite=Lax`, and expire after inactivity.
- Aadhaar numbers are never stored — only the masked reference and the
  recorded consent (see *Deferred integrations*).

## Accessibility

Colour is defined once, as tokens in `tailwind.config.js` — nothing hardcodes a
hex. Every rendered text/background pair on the public site and across the
seeker, employer and official areas was measured against WCAG AA (4.5:1 for
body text, 3:1 for large text) and passes.

## Deferred integrations

These are stubbed with complete UI and database structure so the real service
can be dropped in without a schema change:

- **Aadhaar e-KYC** — consent, OTP request and OTP verification screens are
  live; `KycController` currently issues a demo OTP instead of calling UIDAI.
- **Resume parsing** — resumes upload and store with a `parse_status` of
  `pending` and a `parsed_data` column reserved for the extracted fields.
- **E-mail / SMS delivery** — OTPs are generated and stored; with
  `mail.demo_otp = true` they are shown on screen instead of being sent.
