-- ============================================================================
-- Campus Job Posting System (KLD Campus Hire)
-- Complete MySQL / MariaDB Relational Schema
-- Database: campus_job_portal
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `campus_job_portal` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `campus_job_portal`;

-- ----------------------------------------------------------------------------
-- 1. Table: users
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'employer', 'admin') NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `student_id` VARCHAR(50) NULL,
    `department` VARCHAR(255) NULL,
    `course` VARCHAR(255) NULL,
    `year_level` VARCHAR(50) NULL,
    `sex` VARCHAR(20) NULL,
    `birthdate` DATE NULL,
    `age` INT NULL,
    `phone` VARCHAR(50) NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'active',
    `employer_type` VARCHAR(50) NULL,
    `organization_name` VARCHAR(255) NULL,
    `office_location` VARCHAR(255) NULL,
    `contact_person` VARCHAR(191) NULL,
    `accreditation_number` VARCHAR(100) NULL,
    `verification_status` VARCHAR(50) NOT NULL DEFAULT 'verified',
    `rejection_reason` TEXT NULL,
    `business_permit` VARCHAR(255) NULL,
    `registration_proof` VARCHAR(255) NULL,
    `availability` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_status` (`status`),
    INDEX `idx_users_verification` (`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Table: categories
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
-- 3. Table: jobs
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `department` VARCHAR(255) NOT NULL,
    `organization_name` VARCHAR(255) NULL,
    `category` VARCHAR(191) NOT NULL,
    `category_id` INT NULL,
    `employer_id` INT NULL,
    `employer_name` VARCHAR(191) NULL,
    `employer_type` VARCHAR(50) NOT NULL DEFAULT 'university_office',
    `job_type` VARCHAR(100) NOT NULL DEFAULT 'Student Assistant',
    `work_setup` VARCHAR(50) NOT NULL DEFAULT 'On-Campus',
    `verified_employer` TINYINT(1) NOT NULL DEFAULT 1,
    `location` VARCHAR(255) NULL,
    `pay_rate` VARCHAR(100) NOT NULL DEFAULT '₱85.00 / hour',
    `pay_type` VARCHAR(50) NOT NULL DEFAULT 'Hourly',
    `hours_per_week` VARCHAR(100) NOT NULL DEFAULT '15 - 20 hrs/week',
    `vacancies` INT NOT NULL DEFAULT 1,
    `slots_total` INT NOT NULL DEFAULT 1,
    `slots_filled` INT NOT NULL DEFAULT 0,
    `deadline` DATE NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'active',
    `image` VARCHAR(500) NULL,
    `tags` LONGTEXT NULL,
    `badges` LONGTEXT NULL,
    `description` LONGTEXT NULL,
    `responsibilities` LONGTEXT NULL,
    `qualifications` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_jobs_category` (`category`),
    INDEX `idx_jobs_employer_id` (`employer_id`),
    INDEX `idx_jobs_status` (`status`),
    INDEX `idx_jobs_deadline` (`deadline`),
    INDEX `idx_jobs_job_type` (`job_type`),
    INDEX `idx_jobs_work_setup` (`work_setup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Table: applications
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT NOT NULL,
    `job_title` VARCHAR(255) NOT NULL,
    `department` VARCHAR(255) NOT NULL,
    `student_id` INT NOT NULL,
    `student_name` VARCHAR(191) NOT NULL,
    `student_number` VARCHAR(50) NULL,
    `student_email` VARCHAR(191) NOT NULL,
    `course` VARCHAR(255) NULL,
    `year_level` VARCHAR(50) NULL,
    `sex` VARCHAR(20) NULL,
    `age` INT NULL,
    `phone` VARCHAR(50) NULL,
    `cover_letter` LONGTEXT NULL,
    `availability` LONGTEXT NULL,
    `resume_file` VARCHAR(255) NULL,
    `study_load_file` VARCHAR(255) NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
    `status_label` VARCHAR(100) NOT NULL DEFAULT 'Pending Review',
    `status_badge` VARCHAR(50) NOT NULL DEFAULT 'warning',
    `interview_date` DATE NULL,
    `interview_time` VARCHAR(50) NULL,
    `interview_venue` VARCHAR(255) NULL,
    `supervisor_notes` TEXT NULL,
    `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_applications_job_id` (`job_id`),
    INDEX `idx_applications_student_id` (`student_id`),
    INDEX `idx_applications_status` (`status`),
    INDEX `idx_applications_department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Table: profile_requests
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `profile_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `user_name` VARCHAR(191) NOT NULL,
    `user_email` VARCHAR(191) NOT NULL,
    `student_id` VARCHAR(50) NULL,
    `current_profile` LONGTEXT NULL,
    `requested_profile` LONGTEXT NULL,
    `proof_file` VARCHAR(255) NULL,
    `reason` TEXT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
    `admin_notes` TEXT NULL,
    `dismissed_by_user` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `resolved_at` DATETIME NULL,
    INDEX `idx_profile_requests_user_id` (`user_id`),
    INDEX `idx_profile_requests_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. Table: updates
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `updates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL DEFAULT 'Campus News',
    `published_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `read_time` VARCHAR(50) NOT NULL DEFAULT '3 min read',
    `author_name` VARCHAR(191) NULL,
    `author_role` VARCHAR(191) NULL,
    `author_office` VARCHAR(255) NULL,
    `author_avatar` VARCHAR(50) NULL,
    `image` VARCHAR(500) NULL,
    `summary` TEXT NULL,
    `content` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_updates_slug` (`slug`),
    INDEX `idx_updates_category` (`category`),
    INDEX `idx_updates_published_at` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 7. Table: devblogs
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
