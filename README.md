# 💰 Income Tracker Application

A lightweight, secure, and responsive **PHP & MySQL Income Management Dashboard** built with Bootstrap 5. It features role-based access control (Admin & Regular User), real-time financial analytics, dynamic data filtering, full CRUD functionality, and a built-in Dark/Light mode switcher.

---

## 🌟 Key Features

* **🔒 Role-Based Authentication & Access Control:**
  * **Admin Role Full privileges including creating, reading, editing, and deleting income entries.
  * **User Role:** View entries and record new income. Restricted from editing or deleting existing records.
* **⚡ Dynamic REST API Backend:** Asynchronous AJAX requests powered by PHP PDO and MySQL for seamless updates without page refreshes.
* **📊 Real-Time Analytics:** Live KPI summary cards calculating income totals for **Today**, **This Week**, **This Month**, and **This Year**.
* **🔍 Date Filtering:** Filter transaction records dynamically by All, Daily, Weekly, Monthly, or Yearly intervals.
* **🌙 Dark / Light Mode Toggle:** Top-right navbar theme toggle switch with persistent `localStorage` user preferences.
* **🛡️ Security First:** Prepared SQL statements (`PDO`) to prevent SQL injection attacks, password hashing using `BCRYPT`, and session guards on restricted endpoints.

---

## 📁 Project Structure

```text
income_tracker/
├── schema.sql    # Database structure, tables, and seed users
├── db.php        # PDO database connection & session initializer
├── login.php     # Authentication page & password verification
├── logout.php    # Session termination script
├── api.php       # Backend REST API endpoint for CRUD & statistics
├── index.php     # Main responsive frontend dashboard
└── README.md     # Project documentation

🚀 Installation & Setup Guide
1. Prerequisites
Ensure you have a local web server environment installed:
PHP (v7.4 or higher)
MySQL / MariaDB
XAMPP, WAMP, or MAMP

2. Database Configuration
Start Apache and MySQL in your local web server control panel (e.g., XAMPP Control Panel).
Open phpMyAdmin in your browser (http://localhost/phpmyadmin/).
Click on the SQL tab and execute the contents of the schema.sql file.
This creates the income_tracker database.Generates the users and income_entries tables.
Creates default pre-configured admin accounts.

3. Application DeploymentCopy or clone the project folder into your web server root directory:
XAMPP: C:/xampp/htdocs/income_tracker
WAMP: C:/wamp64/www/income_tracker
MAMP: /Applications/MAMP/htdocs/income_tracker

Open db.php in a text editor and update your database credentials if necessary:
PHP$host = 'localhost';
$db   = 'income_tracker';
$user = 'root'; // Your MySQL username
$pass = '';     // Your MySQL password

💻 Tech StackFrontend:
HTML5, CSS3, JavaScript (ES6+ Fetch API), Bootstrap 5.3, Bootstrap Icons
Backend: PHP 8.x (PDO Object-Oriented Architecture)
Database: MySQL / MariaDB

📖 API Endpoint Documentation (api.php)
All API routes require an active authenticated user session.

