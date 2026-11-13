# CINEMA BOOKING – FULL-STACK MOVIE TICKET BOOKING SYSTEM

CINEMA BOOKING is an enterprise-grade, modern, and fully-featured Movie Ticket Booking Web Application designed with a sleek dark cinematic aesthetic (deep navy/black backdrop, purple and crimson red glowing accents, premium glassmorphism elements, and smooth micro-animations).

---

## 1. TECHNOLOGY STACK

- **Frontend:**
  - **HTML5:** Semantic structure, accessible markup, and SVG iconography.
  - **CSS3:** Custom design system (`assets/css/style.css`, `assets/css/responsive.css`, `assets/css/admin.css`).
  - **Bootstrap 5.3.3:** Responsive flex grid, modals, offcanvas navigation, tooltips, and badges.
  - **JavaScript (Vanilla ES6+):** Clean separation of concerns (`main.js`, `booking.js`, `seats.js`, `validation.js`).
- **Backend:**
  - **Core PHP 8.2+:** Object-oriented PDO database layer, secure session management, `password_hash()` with `PASSWORD_DEFAULT`, prepared statements, and transactional locking.
  - **MySQL 8.0+ / MariaDB:** Relational database schema with foreign keys, unique constraints, and concurrency indexing.
  - **Web Server:** Apache 2.4 with `mod_rewrite`, `mod_headers`, `mod_expires`, and `mpm_prefork`.
- **Target Deployment Platform:**
  - **Railway.app:** Native Docker containerization, dynamic `$PORT` binding, and managed MySQL integration.
  - **Local Development:** XAMPP / Localhost (PHP 8.2 + MySQL).

---

## 2. PROJECT ARCHITECTURE

```text
movie-ticket/
│
├── index.php                 # Homepage with Hero Slider, Now Showing & Coming Soon
├── movies.php                # Movie Listing with Genre, Language & Status Filters
├── movie-details.php         # Movie Information, Trailer Modal & Date/Showtime Selector
├── theatres.php              # Multiplex Theatres Directory & Active Schedules
├── booking.php               # Multi-step Flow (Movie -> City -> Theatre -> Show)
├── seats.php                 # Real-time Interactive Seat Map (Recliner, Premium, Regular)
├── payment.php               # Multi-Method Demo Gateway (Card, UPI QR, NetBanking)
├── confirmation.php          # Booking Confirmation & Summary Pass
├── my-bookings.php           # Customer Booking History with Cancellation
├── ticket.php                # Perforated Digital Boarding Pass with Barcode & QR
├── login.php                 # Customer Authentication (with One-Click Demo Fill)
├── register.php              # Customer Account Registration
├── logout.php                # Customer Session Sign-Out
│
├── auth/                     # Alternative Auth Route Handlers
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── admin/                    # Administrative Control Portal
│   ├── includes/
│   │   ├── auth-check.php    # Admin Authorization Middleware
│   │   ├── header.php        # Admin Navbar & Styling
│   │   ├── sidebar.php       # Admin Sidebar Navigation
│   │   └── footer.php
│   ├── index.php             # Admin Dashboard (Live Revenue, Bookings & Charts)
│   ├── login.php             # Admin Authentication (Session Fixation Protected)
│   ├── logout.php            # Admin Logout
│   ├── profile.php           # Admin Profile & Password Security Management
│   ├── movies.php            # Full CRUD: Add, Edit, Delete Movies & Trailers
│   ├── theatres.php          # Manage Venues & Multiplex Locations
│   ├── screens.php           # Manage Auditoriums & 60-Seat Matrix Generation
│   ├── shows.php             # Schedule Showtimes & Tier Pricing
│   ├── bookings.php          # Inspect Orders, Cancel & Release Seats
│   └── users.php             # Customer Management & Account Status Toggle
│
├── backend/                  # Dedicated Server Processing Endpoints
│   ├── auth-process.php      # Login, Register, Session Regeneration & CSRF Check
│   ├── movie-process.php     # Query Catalog, Genres, Schedules & Dates
│   ├── booking-process.php   # Authoritative Server Price Calculation & Seat Lock
│   ├── seat-process.php      # JSON API: Live Booked Seats for Screen Layout
│   ├── payment-process.php   # Transactional Checkout, Race Condition Protection
│   └── ticket-process.php    # Ticket Retrieval & Booking Cancellation Logic
│
├── bin/                      # CLI Administration Utilities
│   └── admin-cli.php         # CLI tool to create, reset, and list administrators
│
├── config/
│   ├── database.php          # PDO Connection Manager (Railway, Docker, and XAMPP support)
│   └── tmdb.php              # TMDB API Server Proxy Client
│
├── database/
│   ├── cinema_booking.sql    # Clean baseline schema for fresh development installs
│   ├── migrate.php           # Safe, idempotent migration runner
│   ├── backup.php            # Automated database backup utility
│   └── migrations/
│       ├── 001_create_tables.sql         # Non-destructive CREATE TABLE IF NOT EXISTS
│       └── 002_seed_initial_catalog.sql  # Idempotent INSERT IGNORE master catalogue
│
├── includes/
│   ├── header.php            # Global Header, SEO Meta & Typography
│   ├── navbar.php            # Fixed Frosted Navbar with City Selector
│   ├── footer.php            # Cinematic Dark Footer & Social Links
│   ├── functions.php         # Utility Helpers, CSRF, Secure Cookies, Currency
│   └── auth-check.php        # Customer Authentication Route Guard
│
├── assets/
│   ├── css/
│   │   ├── style.css         # Main Theme & Visual Components
│   │   ├── responsive.css    # Breakpoints & Print Stylesheet
│   │   └── admin.css         # Admin Portal Stylesheet
│   └── js/
│       ├── main.js           # Navbar, Alerts, Modals & Trailer Player
│       ├── booking.js        # Filter Buttons & Date Pills Interactivity
│       ├── seats.js          # Dynamic Seat Selection & Real-Time Price Calculation
│       └── validation.js     # Form Validation & Interactive Card Mockup
│
├── Dockerfile                # Production Docker container (PHP 8.2 + Apache + MPM fix)
├── docker-entrypoint.sh      # Railway dynamic $PORT entrypoint script
├── railway.json              # Railway deployment manifest
├── .dockerignore             # Docker build context exclusions
├── .gitignore                # Git repository exclusions
├── .env.example              # Environment variables template
└── README.md                 # Project Documentation
```

---

## 3. RAILWAY.APP PRODUCTION DEPLOYMENT GUIDE

### Prerequisites
- A [Railway.app](https://railway.com/) account.
- This GitHub repository connected to your Railway account.

### Step 1: Deploy from GitHub
1. In your Railway dashboard, click **+ New Project** -> **Deploy from GitHub repo**.
2. Select your repository: `movie-ticket`.
3. Railway will detect `railway.json` and build the container using the root `Dockerfile`.

### Step 2: Add Railway MySQL Database
1. In your Railway project canvas, click **+ New** -> **Database** -> **Add MySQL**.
2. Connect the MySQL database service to your web application service. Railway will automatically inject the following environment variables into your application:
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLDATABASE`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`

### Step 3: Configure Environment Variables
In your Railway web application service settings under **Variables**, verify:

| Variable Name | Required | Description |
|:---|:---|:---|
| `MYSQLHOST` | **Yes** | Injected automatically by Railway MySQL |
| `MYSQLPORT` | **Yes** | Injected automatically by Railway MySQL |
| `MYSQLDATABASE` | **Yes** | Injected automatically by Railway MySQL |
| `MYSQLUSER` | **Yes** | Injected automatically by Railway MySQL |
| `MYSQLPASSWORD` | **Yes** | Injected automatically by Railway MySQL |
| `PORT` | **Yes** | Injected automatically by Railway (e.g. `8080`) |
| `TMDB_API_KEY` | Optional | TMDB v3 API Key for search & movie details |
| `TMDB_READ_TOKEN`| Optional | TMDB v4 Bearer Read Access Token |

### Step 4: Run Safe Database Migrations
Once both the web container and MySQL service are running on Railway:
1. Open your web service in Railway -> select the **Shell** tab (or run `railway shell` locally).
2. Execute the non-destructive migration runner:
   ```bash
   php database/migrate.php
   ```
   *This safely applies `001_create_tables.sql` and `002_seed_initial_catalog.sql` without dropping any data or inserting duplicate rows.*

### Step 5: Provision Your Production Administrator Account
In the Railway Shell, create your superadmin account:
```bash
php bin/admin-cli.php create --username=admin --email=admin@yourdomain.com --name="Super Admin" --password="YourComplexPassword123!" --role=superadmin
```

### Step 6: Generate Public Domain
1. In your web service under **Settings** -> **Networking**, click **Generate Domain**.
2. Open your new Railway URL to access the live application!

---

## 4. APACHE MPM & RAILWAY PORT ARCHITECTURE

### The Problem: AH00534
Previous deployments failed with:
```text
AH00534: apache2: Configuration error: More than one MPM loaded.
```
This occurred because Debian Apache images package multiple MPM modules (`mpm_prefork`, `mpm_event`, `mpm_worker`). When more than one MPM is enabled in `/etc/apache2/mods-enabled/`, Apache crashes immediately.

### The Solution:
1. **Build-Time Enforcement:** The `Dockerfile` removes all MPM symlinks, explicitly disables `mpm_event` and `mpm_worker`, enables `mpm_prefork` (required for PHP), and validates syntax with `apache2ctl configtest` during the build.
2. **Runtime Verification:** `docker-entrypoint.sh` verifies that exactly one `mpm_*.load` file exists before starting Apache.
3. **Dynamic `$PORT` Binding:** Railway dynamically assigns an ephemeral port via `$PORT` (e.g. 7041, 8080). `docker-entrypoint.sh` rewrites `/etc/apache2/ports.conf` to `Listen 0.0.0.0:${PORT}` and updates the default virtual host `<VirtualHost *:${PORT}>`.

---

## 5. DATABASE SAFETY, MIGRATIONS & BACKUPS

### Database Migrations
All schema updates are handled via versioned SQL files in `database/migrations/`:
- `001_create_tables.sql`: Baseline schema using `CREATE TABLE IF NOT EXISTS` (Zero `DROP TABLE`).
- `002_seed_initial_catalog.sql`: Initial catalogue using `INSERT IGNORE INTO`.

To check status:
```bash
php database/migrate.php status
```

To run pending migrations:
```bash
php database/migrate.php
```

### Database Backups
Before making changes in production, generate a full timestamped SQL dump:
```bash
php database/backup.php
```
*Dumps are saved to `database/backups/backup_cinema_booking_YYYYMMDD_HHMMSS.sql`, which is protected from web access by `.htaccess` and excluded from GitHub by `.gitignore`.*

---

## 6. ADMINISTRATOR CLI & PASSWORD MANAGEMENT

Never hardcode admin credentials in database dumps or Git commits.

### Using the CLI Management Utility:
```bash
# Create an administrator:
php bin/admin-cli.php create --username=admin --email=admin@example.com --name="Admin Name" --password="Password123!" [--role=superadmin]

# Reset an administrator's password:
php bin/admin-cli.php reset-password --username=admin --password="NewPassword123!"

# List all administrators:
php bin/admin-cli.php list
```

### In-App Password Update:
Authenticated administrators can update their password and profile at `/admin/profile.php` via the Admin Portal header dropdown or sidebar navigation.

---

## 7. LOCAL XAMPP SETUP GUIDE

### Step 1: Copy Files to XAMPP htdocs
```text
C:/xampp/htdocs/CINEMA-BOOKING/
```

### Step 2: Start Apache and MySQL in XAMPP
Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.

### Step 3: Initialize Database
Open phpMyAdmin (`http://localhost/phpmyadmin`), click **Import**, choose `database/cinema_booking.sql`, and click **Import**.

### Step 4: Launch Local Website
- Customer Website: `http://localhost/CINEMA-BOOKING/`
- Admin Portal: `http://localhost/CINEMA-BOOKING/admin/`

---

## 8. SECURITY & PRODUCTION HARDENING

- **Session Fixation Prevention:** Session IDs are regenerated via `session_regenerate_id(true)` upon successful user and admin authentication and sign-out.
- **Secure Session Cookies:** `HttpOnly`, `SameSite=Lax`, and dynamic HTTPS detection are enforced.
- **Sanitized Errors:** Database exceptions are logged to server error logs using `error_log()` and never exposed to clients.
- **Access Control:** `.htaccess` blocks direct browser access to `.sql`, `.env`, `.bak`, `.log`, `.git`, and deployment configurations.
- **Git Safety:** `.gitignore` and `.dockerignore` prevent session cookies, backups, and environment secrets from being committed.
