# 🗳 VoteSecure — Online Voting System
**Built by Abebe Zemen | Wollo University, CS Batch 2024**

---

## 📁 Project Structure

```
voting-system/
├── index.php                  ← Login page
├── register.php               ← Voter registration
├── logout.php                 ← Session logout
├── database.sql               ← DB schema + seed data
│
├── includes/
│   ├── db.php                 ← Database config & PDO connection
│   ├── auth.php               ← Auth, voting & query functions
│   └── sidebar.php            ← Shared sidebar component
│
├── assets/
│   ├── style.css              ← Global stylesheet
│   └── app.js                 ← Global JavaScript
│
├── admin/
│   ├── dashboard.php          ← Admin overview & stats
│   ├── elections.php          ← Create / edit / delete elections
│   ├── candidates.php         ← Manage candidates per election
│   ├── voters.php             ← View / verify / remove voters
│   ├── results.php            ← Live results with chart
│   └── audit.php              ← Security audit log
│
└── voter/
    ├── dashboard.php          ← Voter home — active elections
    ├── vote.php               ← Voting interface
    └── history.php            ← Voter's own vote history
```

---

## ⚙️ Setup Instructions

### Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.4+
- Apache or Nginx (XAMPP / WAMP / LAMP all work)

### Step 1 — Database
```sql
-- In phpMyAdmin or MySQL CLI:
source database.sql
```
Or import `database.sql` via phpMyAdmin → Import tab.

### Step 2 — Configure DB
Edit `includes/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL user
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'voting_system');
define('APP_URL', 'http://localhost/voting-system');
```

### Step 3 — Place Files
Copy the `voting-system/` folder to your web server root:
- XAMPP: `C:/xampp/htdocs/voting-system/`
- WAMP:  `C:/wamp64/www/voting-system/`
- Linux: `/var/www/html/voting-system/`

### Step 4 — Open in Browser
```
http://localhost/voting-system/
```

---

## 🔑 Demo Login

| Role  | Email                    | Password   |
|-------|--------------------------|------------|
| Admin | admin@votesystem.com     | Admin@1234 |
| Voter | Register at /register.php |            |

> **Note:** The seed admin password hash in `database.sql` uses bcrypt.
> If login fails, run this in PHP to generate a fresh hash:
> ```php
> echo password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost' => 12]);
> ```
> Then update the `password` column for the admin user in MySQL.

---

## ✨ Features

### Admin Panel
- 📊 Real-time dashboard with stats
- 🗳 Create, edit, delete elections (set dates, status)
- 👤 Add candidates with bio and party affiliation
- 👥 Manage voters (verify / suspend / delete)
- 📈 Live results with doughnut chart (Chart.js)
- 🔍 Full security audit log

### Voter Portal
- 🔐 Secure login & registration
- 🗳 Intuitive card-based voting interface
- ✅ One-vote-per-election enforcement
- 📋 Personal vote history
- 🚫 Cannot re-vote or change vote

### Security
- 🔒 CSRF token protection on all forms
- 🔑 Bcrypt password hashing (cost 12)
- 🛡 PDO prepared statements (SQL injection prevention)
- 📝 Audit log with IP tracking
- 🔐 Role-based access control (admin vs voter)
- ⚡ Session-based authentication

---

## 🛠 Tech Stack

| Layer    | Technology              |
|----------|------------------------|
| Frontend | HTML5, CSS3, JavaScript |
| Backend  | PHP 8 (PDO)            |
| Database | MySQL / MariaDB        |
| Charts   | Chart.js 4 (CDN)       |
| Fonts    | Google Fonts           |

---

*Developed as part of Abebe Zemen's CS portfolio at Wollo University.*
