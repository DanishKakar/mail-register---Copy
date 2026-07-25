# د صادره او وارده مکتوبونو ثبت سیستم (Outgoing / Incoming Letters Register)

A PHP + MySQL correspondence-register web application, modeled on the uploaded
Excel register ("صادره او وارده کتاب" — Database Directorate). It has secure
login, role-based access, and separate modules for **Outgoing (صادره)** and
**Incoming (وارده)** letters, mirroring the columns in the original book:
serial number, dates, sender/receiver, reference, subject, page count, action
number, remarks, and (for outgoing) the records/executive department sign-off
checkboxes.

## Requirements
- PHP 8.0+ with `pdo_mysql` extension
- MySQL 5.7+ / MariaDB 10.3+
- Apache (with `.htaccess`/mod_rewrite) or Nginx

## Setup

1. **Create the database.**
   ```bash
   mysql -u root -p < schema.sql
   ```
   This creates the `mail_register` database, all tables, and one default
   administrator account:
   - **username:** `admin`
   - **password:** `Admin@12345`

   ⚠️ **Log in and change this password immediately** (or create a new admin
   user via `users.php` and disable/delete the default one).

2. **Configure the database connection.**
   Edit `config/db.php` and set your real host/database/user/password:
   ```php
   const DB_HOST = '127.0.0.1';
   const DB_NAME = 'mail_register';
   const DB_USER = 'your_db_user';
   const DB_PASS = 'your_db_password';
   ```

3. **Deploy the files** to your web root (e.g. `/var/www/html/mail-register`
   or a subdomain document root). Point your Apache/Nginx vhost at that
   folder.

4. **Serve over HTTPS** in production, then uncomment this line in
   `config/app.php` so session cookies require HTTPS:
   ```php
   ini_set('session.cookie_secure', '1');
   ```

5. Visit `login.php` in your browser and sign in.

## Security features included
- Passwords stored with `password_hash()` (bcrypt) — never plain text.
- All database queries use PDO prepared statements (no SQL injection).
- CSRF tokens on every form (login, add, edit, delete, user management).
- Session hardening: `httponly` + `samesite` cookies, session ID
  regeneration, and a 30-minute idle auto-logout.
- Login throttling: 5 failed attempts from the same IP locks that IP out for
  5 minutes, and every login attempt (success/failure) is written to
  `login_logs` for audit purposes.
- Role-based permissions: `admin` (full access + user management),
  `editor` (add/edit/delete letters), `viewer` (read-only).
- Output escaped with `htmlspecialchars()` everywhere to prevent XSS.
- `config/` and `.sql`/`.md` files blocked from direct web access via
  `.htaccess`.

## Features
- Dashboard with live counts (total & today's outgoing/incoming).
- Add / edit / view / delete letters in both registers.
- Search by serial number, sender/receiver, subject, or reference/incoming
  number, plus date-range filtering.
- Pagination on list views.
- One-click **Export to Excel** (`.xls`) of the currently filtered list —
  opens directly in Microsoft Excel with correct Pashto/Dari (RTL) text.
- Fully right-to-left interface in Pashto, ready to be extended with Dari
  labels if needed.

## Folder structure
```
mail-register/
├── config/
│   ├── app.php        # session/security bootstrap, helpers (e(), csrf, redirect)
│   └── db.php          # PDO connection — EDIT YOUR CREDENTIALS HERE
├── includes/
│   ├── auth.php        # login guard + idle timeout + role helper
│   ├── header.php
│   └── footer.php
├── outgoing/            # صادره: list, add, edit, view, delete, export
├── incoming/             # وارده: list, add, edit, view, delete, export
├── assets/css/style.css # RTL theme
├── index.php             # dashboard
├── login.php / logout.php
├── users.php             # admin-only user management
└── schema.sql
```

## Notes on adapting further
- The original Excel book also has an unlabeled "جلد / مخ / ورازت / ریاست"
  cover header — if you want that captured per record too, it's easy to add
  as extra columns; just say which fields to add.
- If your MySQL/PHP host doesn't support the `.xls`-via-HTML export trick in
  some strict Excel security settings, `.csv` export can be added as an
  alternative — ask and it can be included.
