# 🚀 ApexBlog CMS - Web Development Internship Final Project
**ApexPlanet Software Pvt. Ltd. | 45-Day Internship Program (PHP & MySQL)**  
**7th Semester B.Tech / B.E. / BCA / MCA Capstone Submission**

![PHP 8.x](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x%20%2F%20MariaDB-4479A1?style=flat&logo=mysql&logoColor=white)
![Bootstrap 5](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white)
![PDO Prepared Statements](https://img.shields.io/badge/Security-PDO%20%26%20RBAC-0d7a6f?style=flat)
![Status](https://img.shields.io/badge/Status-Completed-success)

---

## 📌 Project Overview

This project is the final capstone submission for the **45-Day Web Development Internship Program** at **ApexPlanet Software Pvt. Ltd.**. It is designed specifically for **7th Semester University / College submission and viva evaluation**.

It delivers a clean, secure, and production-grade **Content Management & Dynamic Blog System** written in modern procedural PHP with PHP Data Objects (PDO), MySQL, and Bootstrap 5. It strikes the perfect academic balance: **clean, readable, and solid—neither overly basic nor unnecessarily convoluted**.

---

## 📋 Comprehensive Internship Task Mapping

Every single requirement outlined in the ApexPlanet internship curriculum has been fully implemented:

| Task # | Internship Task Title | Implemented Modules & Features | Status |
| :--- | :--- | :--- | :--- |
| **Task 1** | **Setting Up Development Environment** | • Local Server configured (XAMPP / WAMP / Apache & MySQL)<br>• Clean modular project directory structure<br>• Git version control initialized with `.gitignore` and `README.md` | ✅ **Complete** |
| **Task 2** | **Basic CRUD Application** | • Database `blog` with normalized tables (`users` & `posts`)<br>• **Create**: PHP form to create new posts (`posts/create.php`)<br>• **Read**: Post listings and single article view (`posts/view.php`)<br>• **Update**: Editing existing articles (`posts/edit.php`)<br>• **Delete**: Post deletion (`posts/delete.php`)<br>• **Authentication**: Secure registration, login, and session handling | ✅ **Complete** |
| **Task 3** | **Advanced Features Implementation** | • **Search**: Search posts by title or body content with query preservation<br>• **Pagination**: Server-side pagination with dynamic page controls<br>• **UI/UX**: Responsive Bootstrap 5 layout with custom CSS styles | ✅ **Complete** |
| **Task 4** | **Security Enhancements** | • **PDO Prepared Statements**: 100% parameter binding against SQL Injection<br>• **Form Validation**: Both client-side (Bootstrap 5) and server-side checks<br>• **User Roles & RBAC**: `admin`, `editor`, and `user` with strict authorization<br>• **CSRF Protection**: Cryptographically secure token verification<br>• **XSS Prevention**: Full output escaping via `htmlspecialchars()` | ✅ **Complete** |
| **Task 5** | **Final Project and Integration** | • Seamless end-to-end integration of all modules<br>• 1-Click Database Installer & Seeder (`setup/install.php`)<br>• SQL Schema file (`setup/database.sql`)<br>• Comprehensive documentation & academic project report | ✅ **Complete** |

---

## 🔐 Role-Based Access Control (RBAC) Matrix

| Feature / Action | Guest (Public) | User / Author | Editor | Administrator |
| :--- | :---: | :---: | :---: | :---: |
| Browse Published Posts & Search | ✅ | ✅ | ✅ | ✅ |
| View Single Post Details | ✅ | ✅ | ✅ | ✅ |
| User Registration & Login | ✅ | ✅ | ✅ | ✅ |
| Create New Post | ❌ | ✅ | ✅ | ✅ |
| Edit Own Post | ❌ | ✅ | ✅ | ✅ |
| Edit Any Author's Post | ❌ | ❌ | ✅ | ✅ |
| Delete Own Post | ❌ | ✅ | ❌ | ✅ |
| Delete Any Author's Post | ❌ | ❌ | ❌ | ✅ |
| Access Admin/Editor Dashboard | ❌ | ❌ | ✅ | ✅ |
| Manage User Roles (Admin/Editor/User) | ❌ | ❌ | ❌ | ✅ |
| Delete Users | ❌ | ❌ | ❌ | ✅ |

---

## 🔑 Default Demo Credentials

The database comes pre-seeded with sample accounts and technical articles. You can log in with any of these:

| Role | Username | Email | Password | Access Privileges |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `admin@apexplanet.in` | `admin123` | Full control: user management, edit/delete any post, dashboard |
| **Editor** | `editor` | `editor@apexplanet.in` | `editor123` | Moderate all articles, publish/unpublish, dashboard |
| **User (Author)** | `student` | `student@apexplanet.in` | `student123` | Create articles, edit/delete own posts only |

*(Note: On the login page, you can also use the **Quick Demo Autofill** buttons for instant testing during video recording or viva!)*

---

## 📂 Project Directory Structure

```text
apexplanet-blog-cms/
├── assets/
│   ├── css/
│   │   └── style.css            # Custom CSS theme matching ApexPlanet colors
│   └── js/
│       └── main.js              # Client-side validation, alerts, and demo helpers
├── auth/
│   ├── login.php                # Authentication form with PDO, BCRYPT, & CSRF
│   ├── register.php             # Registration form with duplicate & format validation
│   └── logout.php               # Secure session destruction
├── config/
│   ├── config.php               # Session configuration, paths, and constants
│   ├── db.php                   # PDO Database Connection with error interception
│   └── functions.php            # Security, CSRF, RBAC, formatting, and helper utilities
├── includes/
│   ├── header.php               # HTML head, Bootstrap 5 CSS, Google Fonts
│   ├── navbar.php               # Dynamic navbar with role badges and user dropdown
│   ├── footer.php               # Responsive footer with internship attribution
│   └── alerts.php               # Dismissible flash notification alerts
├── posts/
│   ├── create.php               # Form to write new posts (Task-2 Create)
│   ├── view.php                 # Detailed post view with author info (Task-2 Read)
│   ├── edit.php                 # Edit post with RBAC ownership checks (Task-2 Update)
│   └── delete.php               # Post deletion handler with CSRF checks (Task-2 Delete)
├── admin/
│   ├── dashboard.php            # Analytics overview, recent articles & users
│   ├── users.php                # User management & role promotion (Task-4 RBAC)
│   └── posts.php                # Global article moderation directory
├── setup/
│   ├── install.php              # 1-Click web installer to create DB and seed data
│   └── database.sql             # SQL schema dump for phpMyAdmin import
├── index.php                    # Homepage: Hero section, Search, Filter, Pagination
├── .gitignore                   # Git ignore file for PHP environment
├── README.md                    # Project overview & submission guide
└── INTERNSHIP_REPORT.md         # 7th Semester Academic Project Report
```

---

## ⚡ Installation & Setup Guide

### Method 1: Using XAMPP / WAMP (Recommended)

1. **Move Project to Web Root:**
   - Copy or move the `apexplanet-blog-cms` folder into your XAMPP `htdocs` directory:
     ```text
     C:\xampp\htdocs\apexplanet-blog-cms
     ```
   - (Or for WAMP: `C:\wamp64\www\apexplanet-blog-cms`)

2. **Start Services:**
   - Open **XAMPP Control Panel** and start both **Apache** and **MySQL**.

3. **Initialize Database (1-Click Installer):**
   - Open your browser and navigate to:
     ```text
     http://localhost/apexplanet-blog-cms/setup/install.php
     ```
   - Click the green **"Install & Initialize Database"** button.
   - The installer will automatically create the `blog` database, tables, RBAC roles, and seed demo articles!

4. **Launch Application:**
   - Go to `http://localhost/apexplanet-blog-cms/index.php`.

---

### Method 2: Manual Database Import via phpMyAdmin

1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Click on the **Import** tab.
3. Choose the file located at: `setup/database.sql`.
4. Click **Import / Go**. The database `blog` and all tables will be created.

---

### Method 3: Using PHP Built-in Server

If you have PHP installed in your system PATH:
```bash
cd apexplanet-blog-cms
php -S localhost:8000
```
Then visit `http://localhost:8000/setup/install.php`.

---

## 🛡️ Security Implementation Details (Task-4)

1. **SQL Injection Defense:**
   - All queries use **PDO Prepared Statements** with named placeholders (`:param`).
   - `PDO::ATTR_EMULATE_PREPARES` is set to `false`, ensuring queries are pre-compiled natively by MySQL.

2. **Cross-Site Scripting (XSS) Defense:**
   - All dynamic database content rendered in HTML is sanitized using `e()` helper:
     ```php
     htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
     ```

3. **Cross-Site Request Forgery (CSRF) Defense:**
   - Every state-modifying POST request (Login, Register, Create Post, Edit Post, Delete Post, Role Update) requires a cryptographically secure token generated with `random_bytes(32)`.
   - Verified on submission using timing-attack-safe `hash_equals()`.

4. **Session Security & Fixation Prevention:**
   - Session identifiers are regenerated upon login with `session_regenerate_id(true)`.
   - `session.cookie_httponly` is enforced to prevent JavaScript access to cookies.

5. **Password Encryption:**
   - User passwords are encrypted using PHP's native `password_hash($password, PASSWORD_BCRYPT)`.
   - Passwords are verified via `password_verify($password, $user['password'])`.

---

## 🎓 ApexPlanet Internship Submission Guide (Slide 3)

Follow these steps to complete your internship submission as specified in the presentation:

### Step 1: Create a Screen Recording
- Use OBS Studio, Xbox Game Bar (`Win + G`), or Loom to record a 3 to 5-minute project walkthrough.
- **Demo Checklist for the Video:**
  1. Show homepage with existing articles, category filters, and pagination.
  2. Perform a keyword search (e.g. search "security").
  3. Log in as regular author (`student` / `student123`) and create a new article.
  4. Edit the article and demonstrate client & server validation.
  5. Log in as `admin` (`admin` / `admin123`) to showcase the Admin Dashboard and User Roles management.
  6. Demonstrate post deletion and security safeguards.

### Step 2: Upload Video to LinkedIn
1. Log in to your **LinkedIn** profile.
2. Create a new post highlighting your 45-day Web Development internship at **ApexPlanet Software Pvt. Ltd.**.
3. Tag `@ApexPlanet Software Pvt Ltd` and include relevant hashtags: `#WebDevelopment #PHP #MySQL #Internship #ApexPlanet #FullStack`.
4. Add the video to your profile under the **"Featured"** section.
5. Copy the post / video link.

### Step 3: Push Project to GitHub
1. Initialize Git in this directory (if not already done):
   ```bash
   git init
   git add .
   git commit -m "Completed ApexPlanet Web Development Internship Final Project"
   ```
2. Create a new public repository on GitHub (e.g. `apexplanet-web-development-internship`).
3. Push your repository:
   ```bash
   git remote add origin https://github.com/YOUR_USERNAME/apexplanet-web-development-internship.git
   git branch -M main
   git push -u origin main
   ```

### Step 4: Submit on ApexPlanet Internship Portal
1. Go to the **ApexPlanet Internship** portal.
2. Log in and navigate to **"Manage Task"**.
3. Verify your identity with your **Offer Letter ID** and registered **Email Address**.
4. Click on **Task-5 (Final Project and Certification)**.
5. Paste your **LinkedIn Video Link** and **GitHub Repository Link**.
6. Click **Submit** to finalize your internship completion certificate!

---

## 👨‍💻 Developer Attribution
- **Intern Name:** 7th Semester Engineering Intern
- **Program:** Web Development (PHP, MySQL) - 45 Days
- **Organization:** ApexPlanet Software Pvt. Ltd.
- **Contact:** info@apexplanet.in | +91 9905879870
