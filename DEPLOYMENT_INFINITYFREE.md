# CINEMA BOOKING – FREE DEPLOYMENT GUIDE (INFINITYFREE)

This guide provides practical, step-by-step instructions to deploy your **CINEMA BOOKING** system online for free using [InfinityFree](https://www.infinityfree.com/). No domain purchase or credit card required.

---

## 1. PRE-DEPLOYMENT ARCHITECTURE CHECK

Your project has been prepared for production:

1. **Auto-Detecting Database Configuration (`config/database.php`):**
   - Automatically detects whether the app is running locally (XAMPP) or on a live server (InfinityFree).
   - Prevents `localhost` hardcoding mistakes on live hosting.
2. **Free-Hosting Compatible Database Schema (`database/cinema_booking.sql`):**
   - Stripped of hardcoded `CREATE DATABASE` and `USE` statements so phpMyAdmin will not throw `#1044 Access Denied` errors on shared servers.
3. **Directory Protection (`.htaccess`):**
   - Directory browsing disabled.
   - Direct web access to `config/` and `database/` is blocked.
4. **Dynamic URL Routing (`base_url()`):**
   - Works whether uploaded to the root of your domain (`yourname.infinityfreeapp.com/`) or inside a subfolder.

---

## 2. WHAT TO UPLOAD (AND WHAT TO EXCLUDE)

### Upload this exact structure to your hosting `htdocs/` folder:

```text
htdocs/
├── .htaccess
├── index.php
├── movies.php
├── movie-details.php
├── theatres.php
├── booking.php
├── seats.php
├── payment.php
├── confirmation.php
├── my-bookings.php
├── ticket.php
├── login.php
├── register.php
├── logout.php
├── admin/
│   ├── includes/
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── movies.php
│   ├── theatres.php
│   ├── screens.php
│   ├── shows.php
│   ├── bookings.php
│   └── users.php
├── backend/
│   ├── auth-process.php
│   ├── booking-process.php
│   ├── movie-process.php
│   ├── payment-process.php
│   ├── seat-process.php
│   └── ticket-process.php
├── config/
│   ├── .htaccess
│   └── database.php
├── includes/
│   ├── auth-check.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── navbar.php
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   ├── responsive.css
│   │   └── admin.css
│   ├── js/
│   │   ├── main.js
│   │   ├── booking.js
│   │   ├── seats.js
│   │   └── validation.js
│   └── images/
│       └── poster-placeholder.svg
└── database/
    ├── .htaccess
    └── cinema_booking.sql
```

### DO NOT Upload:
- Local test scripts (`test_*.php`, `debug_*.php`, `*.txt` cookies).
- `.git/` or `.vscode/` folders.
- Local backup files.

---

## 3. STEP-BY-STEP DEPLOYMENT PROCESS

### Step 1: Create a Free InfinityFree Account
1. Go to [https://www.infinityfree.com/](https://www.infinityfree.com/) and click **Sign Up Now**.
2. Enter your email, set a password, verify the confirmation email, and log in to the **Client Area**.

---

### Step 2: Create a Free Hosting Account & Subdomain
1. In the Client Area dashboard, click **+ Create Account**.
2. **Domain Type:** Select **Free Subdomain**.
3. **Subdomain Name:** Enter your preferred name (e.g., `cinemabooking-live`).
4. **Domain Extension:** Choose any free extension (e.g., `.infinityfreeapp.com`, `.epizy.com`, or `.rf.gd`).
5. Click **Check Availability**.
6. Set an **Account Password** (or leave it blank for an auto-generated password), then click **Create Account**.
7. Once created, click **Manage** to view your account details page.

---

### Step 3: Create the MySQL Database
1. From your Account Details page, click **Control Panel** (vPanel).
   *(If prompted with "Notice: To notify you of changes...", click **I Approve**).*
2. Scroll to the **Databases** section and click **MySQL Databases**.
3. Under **Create New Database**, enter a name suffix like `cinema` or `cinemabooking`.
4. Click **Create Database**.
5. Note the details generated in the table below:
   - **Database Name:** e.g., `if0_38472910_cinema`
   - **MySQL User:** e.g., `if0_38472910`
   - **MySQL Host:** e.g., `sql105.infinityfree.com` *(DO NOT use localhost)*
   - **Password:** Your vPanel hosting account password *(shown in the Client Area under "Account Details" -> "Password")*.

---

### Step 4: Import Database Schema via phpMyAdmin
1. On the **MySQL Databases** page in vPanel, find your newly created database in the list.
2. Click the **phpMyAdmin** link next to it.
3. phpMyAdmin will open in a new tab with your database selected in the left sidebar.
4. Click the **Import** tab at the top.
5. Click **Choose File** and select `database/cinema_booking.sql` from your computer.
6. Leave all settings at their defaults and click **Import** (or **Go**) at the bottom.
7. You should see a green success bar confirming all 11 tables (`movies`, `shows`, `seats`, `theatres`, `bookings`, etc.) have been imported.

---

### Step 5: Upload Project Files
You can upload using either the **Web File Manager** or **FTP (FileZilla)**.

#### Option A: Web File Manager (Recommended for quick setup)
1. Go to your InfinityFree Account Details and click **File Manager**.
2. Double-click the **`htdocs`** folder to enter it.
   *(Delete any default `index2.html` or placeholder file inside `htdocs` if present).*
3. Zip your project files locally on your computer:
   - Select all files and folders inside `d:\landing page` (`assets`, `config`, `includes`, `backend`, `admin`, `index.php`, `.htaccess`, etc.).
   - Right-click -> **Compress to ZIP file** (name it `cinema_upload.zip`).
4. In the File Manager, click the **Upload** button (bottom left) and choose **Upload Zip**.
5. Select `cinema_upload.zip`. The File Manager will upload and automatically extract all files directly into `htdocs/`.

#### Option B: FTP (FileZilla)
1. In the Client Area under **Account Details**, note your **FTP Details**:
   - Host: `ftpupload.net`
   - Username: `if0_38472910`
   - Password: Your account password
   - Port: `21`
2. Connect using FileZilla, navigate to `/htdocs/`, and drag-and-drop all project folders and files.

> **CRITICAL RULE:** Make sure `index.php` is located directly inside `htdocs/` (i.e. `htdocs/index.php`), NOT inside `htdocs/CINEMA-BOOKING/index.php`.

---

### Step 6: Configure Live Database Connection
1. In the File Manager, open the **`config`** folder.
2. Right-click **`database.php`** and select **Edit**.
3. Locate the **PRODUCTION CONFIGURATION** section (around line 30):

```php
} else {
    // ----------------------------------------------------
    // PRODUCTION CONFIGURATION (InfinityFree / Live Hosting)
    // ----------------------------------------------------
    if (!defined('DB_HOST')) define('DB_HOST', 'sql105.infinityfree.com'); // Put your MySQL Host here
    if (!defined('DB_NAME')) define('DB_NAME', 'if0_38472910_cinema');     // Put your full DB name here
    if (!defined('DB_USER')) define('DB_USER', 'if0_38472910');            // Put your vPanel username here
    if (!defined('DB_PASS')) define('DB_PASS', 'YOUR_ACTUAL_PASSWORD');    // Put your vPanel password here
    if (!defined('DB_PORT')) define('DB_PORT', '3306');
}
```

4. Replace `sql105.infinityfree.com`, `if0_38472910_cinema`, `if0_38472910`, and the password with your exact values from Step 3.
5. Click **Save & Close** (floppy disk icon).

---

### Step 7: Enable Free SSL / HTTPS (Optional but Recommended)
1. In the InfinityFree Client Area, click **Free SSL Certificates** in the top navigation.
2. Click **New SSL Certificate**.
3. Select your subdomain from the dropdown.
4. Choose **Let's Encrypt** or **ZeroSSL (Recommended)**.
5. Click **Set up CNAME Records Automatically**.
6. Wait 5-10 minutes for DNS validation, then click **Request Certificate**.
7. Once issued, click **Install SSL Certificate Automatically**.
8. Your site will now be securely accessible via `https://`.

---

## 4. LIVE VERIFICATION CHECKLIST

After completing the steps above, visit your live website URL (e.g. `http://yourname.infinityfreeapp.com`):

- [ ] **Homepage (`/`):** Hero movie banner slider loads, Now Showing & Coming Soon movie cards render properly.
- [ ] **Movie Trailers:** Click "Watch Trailer" on any movie; the modal should pop up and play the official trailer.
- [ ] **City Switching:** Change location from the top navbar (e.g. Mumbai -> Delhi NCR); available theatres update.
- [ ] **Customer Login (`/login.php`):** Click "Fill Demo User" (`john@example.com` / `User@123`) and log in. Your profile badge should appear in the navbar.
- [ ] **Interactive Seat Selection (`/seats.php`):**
  - Choose a movie showtime.
  - Pick seats in Recliner (Row A), Premium (Rows B-D), or Regular (Rows E-F).
  - Check that the subtotal, convenience fee (₹35), and 18% GST calculate in real time.
- [ ] **Demo Payment (`/payment.php`):**
  - Select Card, UPI QR, or NetBanking.
  - Submit the simulated transaction.
- [ ] **Confirmation & Digital Ticket (`/ticket.php`):**
  - Verify the booking reference is generated (`CNM-XXXXX-YY`).
  - Print/preview the ticket using your browser's print dialog (`window.print()`).
- [ ] **Admin Portal (`/admin/`):**
  - Navigate to `/admin/login.php`.
  - Log in with `admin@cinemabooking.com` / `Admin@123`.
  - Verify that the Dashboard stats reflect your recent bookings, revenue, and customer records.

---

## 5. INFINITYFREE LIMITATIONS & TECHNICAL WORKAROUNDS

| Limitation | Impact on This Project | Solution / Alternative |
| :--- | :--- | :--- |
| **MySQL Hostname** | Using `localhost` fails immediately with `Connection refused (10061)`. | Use the exact remote MySQL hostname provided (e.g. `sql105.infinityfree.com`). Configured in `config/database.php`. |
| **Max Concurrent DB Connections** | Free tier allows only 3 concurrent database connections per account. | Persistent connections (`PDO::ATTR_PERSISTENT => false`) are disabled in `config/database.php` so connections close immediately after each request. |
| **PHP `mail()` Disabled** | Free hosts block PHP `mail()` to prevent spam. | All ticket confirmations are rendered immediately on-screen with downloadable digital passes and QR codes. If automated emails are required later, use a free third-party API like **Resend** or **SendGrid** via standard cURL over port 443. |
| **File Upload Size Limit (10 MB)** | Limits direct large video/file uploads via PHP. | Movie posters and trailers use lightweight URLs or CDNs (Unsplash / YouTube embeds) so local server storage stays well under the 10 MB and 30,000 inode threshold. |
| **Execution Time Limit (20-30s)** | Long-running backend scripts get killed by the web server. | All booking transactions, password verifications, and queries are indexed and complete in less than 50 milliseconds. |

---

## 6. DEFAULT CREDENTIALS SUMMARY

| Account | Email / Username | Password | Role |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@cinemabooking.com` | `Admin@123` | Full Admin Portal Access (`/admin/`) |
| **Demo Customer 1** | `john@example.com` | `User@123` | Customer Booking (`/login.php`) |
| **Demo Customer 2** | `sophia@example.com` | `User@123` | Customer Booking (`/login.php`) |

