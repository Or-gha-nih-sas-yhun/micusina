# Hostinger deployment

Mi Cusina is a Laravel application. Deploy the repository so the domain points to
the `public` directory. If the Hostinger Git deployment keeps the repository at
the domain root, the repository-level `.htaccess` forwards web requests to
`public/` and blocks application source files.

## One-time Hostinger setup

1. Select PHP 8.2 or newer and enable `openssl`, `pdo`, `pdo_mysql` (or
   `pdo_sqlite` if you intentionally use SQLite), `mbstring`, `fileinfo`, `xml`,
   `curl`, `zip`, and `gd`.
2. Deploy the `main` branch with Hostinger Git deployment.
3. Create `.env` in the project root. Start from `.env.example`, then set at
   least `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://micusina-pos.com`,
   a generated `APP_KEY`, the production database values, and the mail/PayMongo values.
   For reliable login sessions on HTTPS also set
   `SESSION_DOMAIN=micusina-pos.com` and `SESSION_SECURE_COOKIE=true`.
4. Run `bash hostinger-deploy.sh` from the project root over SSH. The script
   installs only production packages, runs migrations, creates the public
   storage link, and rebuilds Laravel's caches.

If Hostinger's automatic Composer step reports a zero-byte or corrupted ZIP,
run `composer2 clear-cache` over SSH and deploy again. The deployment script
also retries with `--prefer-source` so it does not depend on the failed ZIP.
Hostinger's built-in Composer step happens before repository scripts; if it
fails before the project is available, clear the cache and use **Deploy** again,
or disable the automatic dependency step (if your plan exposes that setting)
and run this script as the deployment command.

## Database and writable directories

Use MySQL in production unless SQLite is deliberately configured. Ensure these
directories are writable by PHP:

* `storage/`
* `bootstrap/cache/`

Never commit `.env`, `vendor/`, or real API credentials.

## Document root alternative

If the Hostinger plan allows changing the document root, set it directly to the
project's `public/` directory. This is preferred because Laravel's non-public
files remain outside the web root.
