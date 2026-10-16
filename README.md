# Gojo Shop CRM (Laravel Rebuild)

A full-stack, local-first Sales CRM for remote contractor management, customer lead tracking, call logging, and order verification. Built to replace the static Supabase frontend with a self-hosted PHP/Laravel backend on MySQL.

---

## 🌐 Quick Access (Localhost)

- **Application URL:** [http://localhost/gojo-crm-laravel/public](http://localhost/gojo-crm-laravel/public)
- **Database:** MySQL via XAMPP (`gojo_crm`)

---

## 🚀 Key Features

### 1. Manager Dashboard (`/`)
- Top-level KPI cards: **Assigned Customers**, **Contacted Customers** (% contact rate), **First Orders** (% conversion rate), **Delivered Orders** (% delivery rate).
- **Performance by Contractor** comparison bar chart.
- **Call Results** donut chart (breakdown across all contractors).
- **Recent First Orders** table with quick verification status.
- **Contractor Performance** table with assigned vs contacted vs delivered metrics.

### 2. Customer Directory & Call Queue (`/customers`)
- **Queue Filters**: All, Not Called, Follow-up Due, Contacted, First Orders.
- Search by customer name or Ethiopian phone number (`251XXXXXXXXX`).
- Role-based visibility: Contractors only see their assigned leads; Managers see all leads with contractor filter.
- **Call & Log Modal**:
  - One-click click-to-call link (`tel:+251...`).
  - Call outcomes: *No answer, Interested, Not ready, Callback, Order unverified, Do not contact*.
  - Barrier identification (*Price, Trust, Browsing, App difficulty, Later, Other*).
  - Coupon code offered tracking.
  - Next follow-up date scheduling with overdue indicators.
  - Complete call history timeline per customer.
  - Manager controls: contractor reassignment & first order verification ID + delivery status.

### 3. Batch Excel & CSV Import (`/import`)
- 3-step import wizard:
  1. Drag & drop `.xlsx`, `.xls`, or `.csv` file upload.
  2. Contractor batch selection.
  3. Pre-import preview (Total rows, Valid unique Ethiopian phones, Duplicate rows skipped, Invalid phones).
- Automatic Ethiopian phone normalization (`09...`, `07...`, `+251...` &rarr; `251XXXXXXXXX`).
- Deduplication: Existing customers in database are preserved and not overwritten.

### 4. Contractor Performance (`/my-performance`)
- Personal KPI counters: Assigned leads, Contacted leads, Conversion rate, Delivered orders.
- Personal Call Results donut chart.
- 7-day Daily Activity line chart tracking daily calls logged.

### 5. Team & Compensation Settings (`/settings`)
- Manager-only roster to add, edit, or delete contractor accounts.
- Configure contractor fixed pay (ETB) and commission per delivered order (ETB).
- Manage unique contractor promotional coupon codes.

---

## 🛠️ Tech Stack & Architecture

- **Backend:** Laravel 12 (PHP 8.2)
- **Database:** MariaDB / MySQL (`gojo_crm`)
- **Frontend:** Laravel Blade + Vanilla CSS design system (Inter font, dark sidebar, responsive layout)
- **Charts:** Chart.js
- **Excel/CSV Parser:** PhpSpreadsheet (`phpoffice/phpspreadsheet`)
- **Authentication:** Session-based authentication with role-based middleware (`ManagerMiddleware`)

---

## 📦 Deployment to Live Server (cPanel / Shared Hosting / VPS)

When you are ready to push to a live server:

1. **Upload Files:** Upload the `gojo-crm-laravel` project to your server (e.g., above `public_html`).
2. **Document Root:** Point your domain or subdomain to the `public/` directory.
3. **Database:** Create a MySQL database and user in your hosting control panel, import the schema or run:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```
4. **Environment:** Update `.env` on the live server with:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://your-domain.com`
   - Live DB credentials (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
5. **Optimize:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```