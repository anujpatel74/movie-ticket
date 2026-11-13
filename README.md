# CINEMA BOOKING – FULL STACK MOVIE TICKET BOOKING SYSTEM

CINEMA BOOKING is an enterprise-grade, modern, and fully-featured Movie Ticket Booking Web Application designed with a sleek dark cinematic aesthetic (deep navy/black backdrop, purple and crimson red glowing accents, premium glassmorphism elements, and smooth micro-animations).

---

## 1. TECHNOLOGY STACK

- **Frontend:**
  - **HTML5:** Semantic structure, accessible markup, and SVG iconography.
  - **CSS3:** Custom design system (`assets/css/style.css`, `assets/css/responsive.css`, `assets/css/admin.css`).
  - **Bootstrap 5.3.3:** Responsive flex grid, modals, offcanvas navigation, tooltips, and badges.
  - **JavaScript (Vanilla ES6+):** Clean separation of concerns (`main.js`, `booking.js`, `seats.js`, `validation.js`).
- **Backend:**
  - **Core PHP 8.x:** Object-oriented PDO database layer, session management, secure password hashing (BCrypt), prepared statements, and transactional locking.
  - **MySQL / MariaDB:** Relational database schema with foreign keys, unique constraints, and indexing.
  - **XAMPP / Apache:** Local server environment.

---

## 2. PROJECT ARCHITECTURE

```text
CINEMA-BOOKING/
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
├── logout.php                # Session Sign-Out
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
│   ├── login.php             # Admin Authentication
│   ├── logout.php            # Admin Logout
│   ├── movies.php            # Full CRUD: Add, Edit, Delete Movies & Trailers
│   ├── theatres.php          # Manage Venues & Multiplex Locations
│   ├── screens.php           # Manage Auditoriums & 60-Seat Matrix Generation
│   ├── shows.php             # Schedule Showtimes & Tier Pricing
│   ├── bookings.php          # Inspect Orders, Cancel & Release Seats
│   └── users.php             # Customer Management & Account Status Toggle
│
├── backend/                  # Dedicated Server Processing Endpoints
│   ├── auth-process.php      # Login, Register, Password Hashing & CSRF Check
│   ├── movie-process.php     # Query Catalog, Genres, Schedules & Dates
│   ├── booking-process.php   # Authoritative Server Price Calculation & Seat Lock
│   ├── seat-process.php      # JSON API: Live Booked Seats for Screen Layout
│   ├── payment-process.php   # Transactional Checkout, Race Condition Protection
│   └── ticket-process.php    # Ticket Retrieval & Booking Cancellation Logic
│
├── config/
│   └── database.php          # PDO Database Connection & Error Diagnostics
│
├── includes/
│   ├── header.php            # Global Header, SEO Meta & Typography
│   ├── navbar.php            # Fixed Frosted Navbar with City Selector
│   ├── footer.php            # Cinematic Dark Footer & Social Links
│   ├── functions.php         # Utility Helpers, CSRF, Currency, Sanitization
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
├── database/
│   └── cinema_booking.sql    # Complete SQL Schema, Foreign Keys & Seed Data
│
└── README.md                 # Complete Documentation & Setup Guide
```

---

## 3. XAMPP LOCAL SETUP GUIDE

### Step 1: Deploy Files to XAMPP htdocs
Copy this entire directory into your XAMPP web root:
```text
C:/xampp/htdocs/CINEMA-BOOKING/
```

### Step 2: Start Apache and MySQL
1. Open the **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. Click **Start** for **MySQL**.

### Step 3: Import the Database
1. Open your web browser and navigate to:
   ```text
   http://localhost/phpmyadmin/
   ```
2. Click on the **Import** tab at the top.
3. Click **Browse...** / **Choose File** and select:
   ```text
   C:/xampp/htdocs/CINEMA-BOOKING/database/cinema_booking.sql
   ```
4. Click **Import** (or **Go**) at the bottom.
   > The database `cinema_booking` will be created automatically along with all 11 relational tables and sample seed data.

### Step 4: Verify Database Connection
The database credentials in `config/database.php` default to standard XAMPP settings:
- **Host:** `localhost`
- **Database:** `cinema_booking`
- **User:** `root`
- **Password:** ` ` (empty)
- **Port:** `3306`

### Step 5: Launch the Application
Open your browser and navigate to:
```text
http://localhost/CINEMA-BOOKING/
```

Admin Portal URL:
```text
http://localhost/CINEMA-BOOKING/admin/
```

---

## 4. DEFAULT DEMO CREDENTIALS

| Account Type | Email / Username | Password | Role |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@cinemabooking.com` | `Admin@123` | Super Admin |
| **Demo Customer** | `john@example.com` | `User@123` | Customer |
| **Demo Customer 2** | `sophia@example.com` | `User@123` | Customer |

*Both the user and admin login forms include convenient one-click auto-fill buttons for immediate testing.*

---

## 5. ADVANCED FEATURES & BOOKING FLOW

1. **City Selection:** Switch between major cities (Mumbai, Delhi NCR, Bengaluru, Hyderabad). All theatre listings and showtimes update dynamically.
2. **Movie Discovery:** Explore Now Showing & Coming Soon catalogues, filter by Genre (Action, Sci-Fi, Drama, Comedy) or Search by keywords.
3. **Trailer Playback:** Click "Watch Trailer" on any movie card to load official YouTube trailers in a high-definition cinema modal.
4. **Interactive Seat Selection:**
   - **Tiers:** Luxury Recliners (Row A), Prime Premium (Rows B-D), Standard Regular (Rows E-F).
   - **Live Calculations:** Subtotal, ₹35 convenience fee, and 18% GST updated in real-time.
   - **Seat Limits:** Up to 8 seats per booking.
   - **Client/Server Verification:** `backend/seat-process.php` provides real-time verification against MySQL to ensure booked seats are immediately locked.
5. **Multi-Option Demo Payment Gateway:**
   - **Card Simulator:** Live card mockup with auto-formatted spacing (`4242 4242 4242 4242`) and cardholder visual updates.
   - **UPI Simulator:** Dynamic QR code generation with instant mock UPI address validation.
   - **NetBanking:** Popular bank selector.
6. **Concurrency & Race Condition Prevention:**
   - At checkout time, a strict **`SELECT ... FOR UPDATE`** database transaction is executed on the chosen seat IDs.
   - The unique key constraint `uq_show_seat_booked (show_id, seat_id)` physically prevents double-booking at the MySQL engine level even during high concurrency.
7. **Digital Ticket:**
   - Boarding-pass design with perforated tear-line styling.
   - Barcode and QR code display.
   - Fully styled for high-resolution paper print via `window.print()` and `@media print`.
8. **Admin Operations:**
   - Track live sales volume, gross revenue, and customer accounts.
   - Add/edit/delete movies with custom poster and backdrop URLs.
   - Create auditoriums with automatic 60-seat matrix generation.
   - Schedule showtimes with custom pricing tiers.
   - Search customer orders and perform mock refunds / ticket cancellations.

---

## 6. SECURITY & ROBUSTNESS

- **SQL Injection Prevention:** 100% prepared statements via PHP PDO.
- **Cross-Site Scripting (XSS):** All dynamic strings sanitized using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **Cross-Site Request Forgery (CSRF):** Cryptographic session tokens verified on all state-altering POST requests.
- **Password Security:** One-way password hashing using `PASSWORD_BCRYPT`.
- **Admin Authorization:** Isolated session credentials and strict middleware guards on all administrative routes.

---

## 7. VERIFICATION & TESTING CHECKLIST

- [x] All 41 PHP files linted cleanly with `php -l` (0 syntax errors).
- [x] Responsive on mobile (375px), tablet (768px), and desktop (1200px+).
- [x] CSS and JavaScript cleanly separated into `assets/css/` and `assets/js/`.
- [x] Database schema verified with foreign keys, indexes, and unique constraints.
- [x] XAMPP compatibility verified.
- [x] InfinityFree Free Hosting compatibility verified (see [DEPLOYMENT_INFINITYFREE.md](file:///d:/landing%20page/DEPLOYMENT_INFINITYFREE.md)).

