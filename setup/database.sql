-- ==========================================================
-- ApexPlanet Software Pvt Ltd - Web Development Internship
-- 7th Semester Final Project: PHP & MySQL Blog CMS
-- ==========================================================
-- Database: `blog`
-- Tasks Covered:
-- Task-2: Database Setup & Basic CRUD
-- Task-3: Search, Pagination & UI
-- Task-4: Security Enhancements & Role-Based Access Control
-- Task-5: Final Integrated Project
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `blog` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `blog`;

-- --------------------------------------------------------
-- Table structure for table `users` (Task-2 & Task-4)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'editor', 'user') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `posts` (Task-2, Task-3, Task-4)
-- --------------------------------------------------------
CREATE TABLE `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'Web Development',
  `content` TEXT NOT NULL,
  `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`user_id`),
  INDEX (`category`),
  INDEX (`status`),
  INDEX (`created_at`),
  CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed Data: Default Users
-- Default Password for all accounts: "password"
-- (Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
-- You can also run setup/install.php to create fresh passwords.
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@apexplanet.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()),
(2, 'editor', 'editor@apexplanet.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor', NOW()),
(3, 'student_intern', 'intern@apexplanet.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW());

-- --------------------------------------------------------
-- Seed Data: Sample Blog Posts for Testing Search & Pagination
-- --------------------------------------------------------
INSERT INTO `posts` (`id`, `user_id`, `title`, `category`, `content`, `status`, `created_at`) VALUES
(1, 1, 'Getting Started with Modern PHP 8 and Apache Server', 'Web Development', 
'Setting up a robust development environment is the foundation of full-stack web engineering. Throughout the ApexPlanet Web Development internship program, we configured Apache, MySQL, and PHP alongside VS Code and Git. 

PHP 8 brings substantial improvements to the table, including Just-In-Time (JIT) compilation, named arguments, attributes, constructor property promotion, match expressions, and union types. When coupled with an organized directory structure and version control, building scalable web systems becomes an intuitive and productive workflow. In this article, we examine essential php.ini configurations, virtual hosts, and debugging best practices.', 'published', DATE_SUB(NOW(), INTERVAL 5 DAY)),

(2, 2, 'Building Robust CRUD Systems with PHP PDO and Prepared Statements', 'PHP & MySQL', 
'Data persistence is at the heart of dynamic applications. While legacy PHP code often utilized mysql_* or basic mysqli queries prone to concatenation bugs, modern industry standards strictly mandate PHP Data Objects (PDO) with prepared statements.

Prepared statements decouple the SQL command structure from user-supplied parameters. The database engine pre-compiles the query template, treating injected user strings purely as literal data values. This completely eliminates SQL injection vulnerabilities (SQLi). Additionally, PDO provides database abstraction, clean exception-based error reporting, and uniform fetch modes across relational database engines.', 'published', DATE_SUB(NOW(), INTERVAL 4 DAY)),

(3, 3, 'Designing Efficient Pagination and Search for High-Volume Web Listings', 'Software Engineering', 
'As database tables grow into thousands of records, retrieving all rows in a single SQL query degrades server response times and floods client browsers. 

Implementing server-side pagination with SQL LIMIT and OFFSET clauses ensures fast, constant-time database queries. Combined with full-text or parameterized LIKE filters across titles and post bodies, users can quickly locate relevant articles. A critical engineering detail is preserving active search filters and sorting flags when navigating across pagination page numbers, which we solve elegantly via query string concatenation.', 'published', DATE_SUB(NOW(), INTERVAL 3 DAY)),

(4, 1, 'Securing Web Applications Against Common Vulnerabilities (OWASP Top 10)', 'Security & DevOps', 
'In modern web development, security cannot be an afterthought. Every production web application must defend against three primary attack vectors:
1. SQL Injection (SQLi): Mitigated using PDO prepared statements.
2. Cross-Site Scripting (XSS): Mitigated by escaping all dynamic outputs via htmlspecialchars with UTF-8 flags.
3. Cross-Site Request Forgery (CSRF): Prevented using cryptographically secure anti-CSRF session tokens on all state-altering POST requests.
4. Session Hijacking & Fixation: Mitigated by regenerating session identifiers upon authentication and enforcing HttpOnly cookie flags.', 'published', DATE_SUB(NOW(), INTERVAL 2 DAY)),

(5, 2, 'Role-Based Access Control (RBAC) in Enterprise Web Portals', 'Software Engineering', 
'Role-Based Access Control (RBAC) restricts system access based on assigned user roles. In our Blog CMS application, we defined three principal tiers:
- Administrator: Full system oversight, managing user permissions, content moderation, and site configuration.
- Editor: Ability to review, modify, and publish articles across all authors.
- Author / User: Ability to write and manage personal submissions.
- Guest / Public: Read-only access to published content with search capabilities.

Structuring clean middleware guards and declarative permission helper functions in PHP ensures that authorization logic remains maintainable and auditable.', 'published', DATE_SUB(NOW(), INTERVAL 1 DAY)),

(6, 3, 'My 7th Semester Internship Experience at ApexPlanet Software Pvt Ltd', 'Internship Experience', 
'Over the 45-day internship at ApexPlanet Software Pvt Ltd, I traversed the complete web engineering lifecycle: from local environment orchestration and database normalization to developing authentication systems, search filters, pagination algorithms, and comprehensive application hardening.

Building this cohesive PHP and MySQL Blog CMS has bridged theoretical academic concepts with real-world industry engineering standards. I am proud to present this system as my 7th semester internship capstone project.', 'published', NOW());
