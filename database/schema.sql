-- ============================================================================
-- Campus Job Posting System (KLD Campus Hire)
-- Complete 3NF Normalized MySQL / MariaDB Relational Schema
-- Database: campus_job_portal
-- Architecture: Class Table Inheritance (Users/Students/Employers), 
--               Pure Junction (Applications), Zero Redundant Columns
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `campus_job_portal` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `campus_job_portal`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `devblogs`;
DROP TABLE IF EXISTS `updates`;
DROP TABLE IF EXISTS `profile_requests`;
DROP TABLE IF EXISTS `applications`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `employer_profiles`;
DROP TABLE IF EXISTS `student_profiles`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- 1. Table: users (Core Authentication & Identity Supertype)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'employer', 'admin') NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `phone` VARCHAR(50) NULL,
    `status` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Table: student_profiles (Student Subtype, 1-to-1 with users)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `student_profiles` (
    `user_id` INT PRIMARY KEY,
    `student_id` VARCHAR(50) NOT NULL UNIQUE,
    `department` VARCHAR(255) NOT NULL,
    `course` VARCHAR(255) NOT NULL,
    `year_level` VARCHAR(50) NOT NULL,
    `sex` VARCHAR(20) NULL,
    `birthdate` DATE NULL,
    `age` INT NULL,
    `availability` LONGTEXT NULL,
    `verification_status` ENUM('verified', 'pending_approval', 'rejected') NOT NULL DEFAULT 'pending_approval',
    `rejection_reason` TEXT NULL,
    `registration_proof` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_students_department` (`department`),
    INDEX `idx_students_verification` (`verification_status`),
    CONSTRAINT `fk_student_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Table: employer_profiles (Employer Subtype, 1-to-1 with users)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `employer_profiles` (
    `user_id` INT PRIMARY KEY,
    `employer_type` ENUM('university_office', 'approved_partner') NOT NULL DEFAULT 'university_office',
    `organization_name` VARCHAR(255) NOT NULL,
    `office_location` VARCHAR(255) NOT NULL,
    `contact_person` VARCHAR(191) NULL,
    `accreditation_number` VARCHAR(100) NULL,
    `verification_status` ENUM('verified', 'pending_approval', 'rejected') NOT NULL DEFAULT 'pending_approval',
    `rejection_reason` TEXT NULL,
    `business_permit` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_employers_type` (`employer_type`),
    INDEX `idx_employers_verification` (`verification_status`),
    CONSTRAINT `fk_employer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Table: categories (Job Classification Taxonomy)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(191) NOT NULL,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `icon` VARCHAR(100) NOT NULL DEFAULT 'bi-briefcase',
    `description` TEXT NULL,
    `theme` VARCHAR(50) NOT NULL DEFAULT 'kld-green',
    `badge_tag` VARCHAR(100) NULL,
    `badge_icon` VARCHAR(100) NULL,
    `job_count` INT NOT NULL DEFAULT 0,
    `hourly_range` VARCHAR(100) NULL,
    `image` VARCHAR(500) NULL,
    `popular_roles` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Table: jobs (Requisition Header)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `department` VARCHAR(255) NOT NULL,
    `category_id` INT NULL,
    `employer_id` INT NULL,
    `job_type` VARCHAR(100) NOT NULL DEFAULT 'Student Assistant',
    `work_setup` VARCHAR(50) NOT NULL DEFAULT 'On-Campus',
    `location` VARCHAR(255) NULL,
    `pay_rate` VARCHAR(100) NOT NULL DEFAULT '₱85.00 / hour',
    `pay_type` VARCHAR(50) NOT NULL DEFAULT 'Hourly',
    `hours_per_week` VARCHAR(100) NOT NULL DEFAULT '15 - 20 hrs/week',
    `vacancies` INT NOT NULL DEFAULT 1,
    `slots_total` INT NOT NULL DEFAULT 1,
    `slots_filled` INT NOT NULL DEFAULT 0,
    `deadline` DATE NULL,
    `status` ENUM('active', 'paused', 'closed', 'filled') NOT NULL DEFAULT 'active',
    `image` VARCHAR(500) NULL,
    `tags` LONGTEXT NULL,
    `badges` LONGTEXT NULL,
    `description` LONGTEXT NULL,
    `responsibilities` LONGTEXT NULL,
    `qualifications` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_jobs_category_id` (`category_id`),
    INDEX `idx_jobs_employer_id` (`employer_id`),
    INDEX `idx_jobs_status` (`status`),
    INDEX `idx_jobs_deadline` (`deadline`),
    INDEX `idx_jobs_job_type` (`job_type`),
    INDEX `idx_jobs_work_setup` (`work_setup`),
    CONSTRAINT `fk_jobs_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_jobs_employer` FOREIGN KEY (`employer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. Table: applications (Pure Junction & Hiring Workflow State)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `cover_letter` LONGTEXT NULL,
    `availability` LONGTEXT NULL,
    `resume_file` VARCHAR(255) NULL,
    `study_load_file` VARCHAR(255) NULL,
    `status` ENUM('pending', 'under_review', 'interview_scheduled', 'accepted', 'declined') NOT NULL DEFAULT 'pending',
    `interview_date` DATE NULL,
    `interview_time` VARCHAR(50) NULL,
    `interview_venue` VARCHAR(255) NULL,
    `supervisor_notes` TEXT NULL,
    `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_student_job` (`job_id`, `student_id`),
    INDEX `idx_applications_job_id` (`job_id`),
    INDEX `idx_applications_student_id` (`student_id`),
    INDEX `idx_applications_status` (`status`),
    CONSTRAINT `fk_applications_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_applications_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 7. Table: profile_requests (Student COR Profile Correction Queue)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `profile_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `current_profile` LONGTEXT NULL,
    `requested_profile` LONGTEXT NULL,
    `proof_file` VARCHAR(255) NULL,
    `reason` TEXT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `admin_notes` TEXT NULL,
    `dismissed_by_user` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `resolved_at` DATETIME NULL,
    INDEX `idx_profile_requests_user_id` (`user_id`),
    INDEX `idx_profile_requests_status` (`status`),
    CONSTRAINT `fk_profile_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 8. Table: updates (Career Center News & Policy Announcements)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `updates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL DEFAULT 'Campus News',
    `published_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `read_time` VARCHAR(50) NOT NULL DEFAULT '3 min read',
    `author_id` INT NULL,
    `author_avatar` VARCHAR(50) NULL,
    `image` VARCHAR(500) NULL,
    `summary` TEXT NULL,
    `content` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_updates_slug` (`slug`),
    INDEX `idx_updates_category` (`category`),
    INDEX `idx_updates_published_at` (`published_at`),
    INDEX `idx_updates_author_id` (`author_id`),
    CONSTRAINT `fk_updates_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 9. Table: notifications (System Delivery Log)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` VARCHAR(50) NOT NULL DEFAULT 'system',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `link` VARCHAR(255) NULL,
    `icon` VARCHAR(100) NOT NULL DEFAULT 'bi-bell',
    `badge_color` VARCHAR(50) NOT NULL DEFAULT 'primary',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notifications_user_id` (`user_id`),
    INDEX `idx_notifications_user_read` (`user_id`, `is_read`),
    INDEX `idx_notifications_created_at` (`created_at`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 10. Table: devblogs (Standalone Sprint Engineering Chronicle)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `devblogs` (
    `id` VARCHAR(50) PRIMARY KEY,
    `sprint_number` VARCHAR(50) NOT NULL,
    `sprint_title` VARCHAR(255) NOT NULL,
    `sprint_dates` VARCHAR(100) NULL,
    `sprint_focus` TEXT NULL,
    `daily_logs` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_devblogs_sprint_number` (`sprint_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
