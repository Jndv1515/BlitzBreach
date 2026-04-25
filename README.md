# ⚡ BLITZBREACH: Terminal Hacking Simulation
> **Speed is your only defense.**

BlitzBreach is a web-based cybersecurity-themed puzzle game. Operators must navigate through secure nodes, utilizing real-world "script kiddie" and web-vulnerability techniques like URL manipulation, DOM inspection, and console hijacking to breach the system.

---

## 🛠️ System Requirements
* **Environment:** XAMPP / WAMP / LAMP (Local PHP Server)
* **PHP Version:** 7.4 or higher
* **Database:** MariaDB / MySQL
* **Browser:** Chrome or Edge (Optimized for Developer Tools)

---

## 🗄️ Database Initialization (Critical)

Before launching the terminal, you must initialize the `websys` database. Follow these steps:

1. Open **phpMyAdmin**.
2. Click **New** and create a database named: `websys`.
3. Select the `websys` database and click the **SQL** tab.
4. Paste and execute the following schema:

```sql
-- BlitzBreach Database Schema --

-- 1. User Authentication Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Leaderboard & Progress Table
CREATE TABLE IF NOT EXISTS leaderboard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    level_id INT NOT NULL,
    time_taken FLOAT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
