# 🐂 Nova Capital — Stock Portfolio Management System

> **v2 Redesigned & Fully Debugged by [Abhinav](https://github.com/abhii-navv)**
> Forked from [HackstreetBoyzz/Stock-Market-Trading-web-Application](https://github.com/HackstreetBoyzz/Stock-Market-Trading-web-Application)

A premium web-based stock market simulator built with PHP, MySQL.
Trade 6 major tech companies with **$10,000 in virtual capital**, track your portfolio in real-time, and stay updated with market news.

---

## 📋 Table of Contents

- [Overview](#-overview)
- [What's New in v2](#-whats-new-in-v2)
- [Repository Structure](#-repository-structure)
- [Tech Stack](#-tech-stack)
- [Supported Companies](#-supported-companies)
- [Database Setup](#-database-setup)
- [Installation and Setup](#-installation-and-setup)
- [All Bug Fixes](#-all-bug-fixes)
- [Security Notes](#-security-notes)
- [Author](#-author)

---

## 🚀 Overview

Nova Capital is a comprehensive stock portfolio simulator that gives every new user **$10,000 in virtual capital** to invest across 6 featured companies. Users can register, log in, trade stocks, track holdings in real-time, view transaction history, and read market news.

| Folder | Version | Status |
|--------|---------|--------|
| `nova_capital/` | v1 | Original — basic UI, core bugs fixed |
| `nova_capital_new/` | v2 | Full redesign — premium theme, real-time dashboard, per-user isolation ✅ |

---

## ✨ What's New in v2

### 🎨 Full Premium UI Redesign

Every page has been completely redesigned with a consistent fintech aesthetic:

- **Fonts:** Playfair Display (headings) · DM Sans (body) · DM Mono (numbers/tickers)
- **Colors:** Deep green `#003d30` · Mid green `#00694f` · Bright accent `#00c896` · Gold `#c9a84c` · Off-white `#f4f1eb`
- **Components:** Sticky dark-green header · Hero banners · Stat pill cards · Live badge indicators · Pill-style buy/sell badges · Dark-green three-column footer

### 📄 Pages Redesigned

| Page | Changes |
|------|---------|
| `Homepage.html` | Full landing page — hero, feature grid, CTA buttons |
| `signin.php` | Premium auth card with Nova Capital branding |
| `signup.php` | Matching registration card |
| `main.html` | Watchlist grid — animated ticker, hover effects |
| `dash1.php` | Live stat pills, quick-access cards, real-time polling |
| `Apple.html` to `Tesla.html` | Dark hero, stats strip, Chart.js chart, trade card with cost estimator, history table |
| `stock-history.php` | Stats row, filtered table, net investment summary bar |
| `dashboard.html` | Category filter chips, expandable news cards |
| `Average.html` | Range bars, 52W high/low, live clock |
| `Feedback.html` | Two-column form, gold star rating, toast notifications, review cards |

### ⚡ Real-Time Dashboard

- `dash1.php` polls `?refresh=1` every 5 seconds
- Balance, Total Trades, and Portfolio Return update live without page reload
- Updates instantly after any trade on any stock page

### 🔐 Per-User Data Isolation

All PHP files corrected to use the safe session pattern:

```php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';
```

---

## 📂 Repository Structure

```
Stock-Market-Trading-web-Application/
│
├── nova_capital/                    ← v1 Original version
│   ├── Homepage.html
│   ├── main.html
│   ├── dashboard.html
│   ├── Average.html
│   ├── Feedback.html
│   ├── signup.php
│   ├── signin.php
│   ├── login.php
│   ├── registration.php
│   ├── logout.php
│   ├── config.example.php
│   ├── dash1.php
│   ├── stock-history.php
│   ├── get_balance.php
│   ├── get_transactions.php
│   ├── get_csrf_token.php
│   ├── get_owned_stocks_*.php       ← 6 files
│   ├── Apple.html → Tesla.html      ← 6 stock trading pages
│   └── apple_transaction.php → tesla_transaction.php
│
├── nova_capital_new/                ← v2 Redesigned version ✅
│   └── (same structure — fully redesigned and debugged)
│
├── .gitignore
└── README.md
```

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, JavaScript |
| Backend | PHP 7.4+ |
| Database | MySQL 5.7+ |
| Charts | Chart.js |
| Icons | Font Awesome 6 |
| Fonts | Google Fonts (Playfair Display, DM Sans, DM Mono) |
| DB Abstraction | PDO with prepared statements |

---

## 🏢 Supported Companies

| Company | Symbol |
|---------|--------|
| Apple | AAPL |
| Amazon | AMZN |
| Google | GOOGL |
| Meta | META |
| Microsoft | MSFT |
| Tesla | TSLA |

---

## 🗄 Database Setup

Run this once in MySQL or phpMyAdmin:

```sql
CREATE DATABASE IF NOT EXISTS nova_capital;
USE nova_capital;

CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)   NOT NULL UNIQUE,
    email       VARCHAR(100)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    balance     DECIMAL(15,2) DEFAULT 10000.00,
    created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transactions (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT                NOT NULL,
    stock_symbol      VARCHAR(10)        NOT NULL,
    stock_name        VARCHAR(100)       NOT NULL,
    transaction_type  ENUM('buy','sell') NOT NULL,
    quantity          INT                NOT NULL,
    price_per_share   DECIMAL(10,2)      NOT NULL,
    total_amount      DECIMAL(10,2)      NOT NULL,
    transaction_fees  DECIMAL(10,2)      DEFAULT 0.00,
    transaction_date  DATETIME           DEFAULT CURRENT_TIMESTAMP,
    status            VARCHAR(20)        DEFAULT 'completed',
    notes             TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS account_settings (
    user_id             INT         NOT NULL PRIMARY KEY,
    notification_email  TINYINT(1)  DEFAULT 1,
    two_factor_auth     TINYINT(1)  DEFAULT 0,
    account_type        VARCHAR(20) DEFAULT 'basic',
    account_status      VARCHAR(20) DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## ⚙️ Installation and Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Local web server or PHP built-in server

### Step 1 — Enable PHP MySQL Drivers

Open `C:\php\php.ini` and uncomment:

```ini
extension_dir = "C:\php\ext"
extension=mysqli
extension=pdo_mysql
```

### Step 2 — Configure Credentials

Copy `config.example.php` → `config.php` and fill in your values:

```php
$host    = 'localhost';
$db      = 'nova_capital';
$user    = 'root';
$pass    = 'your_password';
$charset = 'utf8mb4';
```

### Step 3 — Run Database SQL

Paste and run the 3 `CREATE TABLE` statements above in phpMyAdmin.

### Step 4 — Start the Server

```powershell
# To run v2:
cd nova_capital_new
php -S localhost:8000
```

### Step 5 — Open in Browser

```
http://localhost:8000/Homepage.html
```

> ⚠️ **Always open via `http://localhost:8000/`** — never double-click files. Opening via `file:///` causes NaN balance errors.

---

## 🐛 All Bug Fixes

### v1 Bug Fixes

| # | File | Problem | Fix |
|---|------|---------|-----|
| 1 | `php.ini` | PDO MySQL driver missing | Enabled `extension_dir`, `pdo_mysql`, `mysqli` |
| 2 | `signup.html` | PHP not executing in `.html` | Renamed to `signup.php` |
| 3 | `login.php` | Duplicate `:id` parameter caused PDO error | Replaced with `:id1` and `:id2` |
| 4 | `registration.php` | INSERT included `created_at` column that didn't exist | Removed — auto-fills via DEFAULT |
| 5 | `signin.php` | `$_SESSION['user_id'] = 1` hardcoded | Removed completely |
| 6 | `signin.php` | Sign Up link pointed to `signup.html` | Updated to `signup.php` |
| 7 | Multiple files | DB credentials duplicated everywhere | All use `require_once 'config.php'` |
| 8 | `registration.php` | `PASSWORD_ARGON2ID` unavailable on some installs | Falls back to `PASSWORD_BCRYPT` |
| 9 | `config.php` | Raw DB errors shown to users | Logged server-side only |
| 10 | `main.html` | Stock logos not clipped into circles | Added `overflow:hidden` wrapper |

### v2 Bug Fixes (on top of v1)

| # | File | Problem | Fix |
|---|------|---------|-----|
| 11 | `login.php` | `$_SESSION['id'] = 1` hardcoded every login to user #1 | Fixed to `$_SESSION['id'] = $user['id']` |
| 12 | `dash1.php` | `getDashboardData()` stub — all stats were hardcoded HTML | Rewrote to query real DB values |
| 13 | All stock `.html` files | Balance showed NaN via `file:///` — CORS blocked PHP fetches | Always serve via `http://localhost:8000/` |
| 14 | All `*_transaction.php` | CSRF token block commented out — every trade rejected | Restored CSRF token generation in all 6 files |
| 15 | `get_transactions.php` | Plain `session_start()` — null `user_id` returned everyone's transactions | Fixed session pattern and added `user_id` filter |
| 16 | All `get_*.php` files | Hardcoded credentials and wrong session pattern | All use `config.php` and correct session start |
| 17 | `stock-history.php` | Hardcoded credentials and missing `user_id` filter | Fixed all three issues and redesigned the page |

---

## 🔒 Security Notes

- `config.php` is excluded from Git via `.gitignore` — **never commit real credentials**
- Only `config.example.php` with placeholder values is committed
- All database queries use **PDO prepared statements** to prevent SQL injection
- CSRF tokens protect all buy/sell transactions
- Passwords are hashed using `PASSWORD_BCRYPT`

---

## 👤 Author

**Abhinav**
GitHub: [@abhii-navv](https://github.com/abhii-navv)

> v2 redesign, all bug fixes, per-user data isolation, real-time dashboard, and full deployment setup done by Abhinav.

Forked from [HackstreetBoyzz/Stock-Market-Trading-web-Application](https://github.com/HackstreetBoyzz/Stock-Market-Trading-web-Application)

---

*Nova Capital — Built with 💚 using PHP, MySQL*
