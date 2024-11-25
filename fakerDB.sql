-- Drop and create database
DROP DATABASE IF EXISTS fakerDB;
CREATE DATABASE IF NOT EXISTS fakerDB;
USE fakerDB;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS visitor_logs;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS stats_cache;
DROP TABLE IF EXISTS USER_info;

-- Create admin user table
CREATE TABLE IF NOT EXISTS USER_info (
    `ID` VARCHAR(20) NOT NULL,
    `PASSWORD` VARCHAR(20) NOT NULL,
    `ACCESS` BOOLEAN NOT NULL DEFAULT FALSE,
    `RECOREDE_DATE` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert admin user
INSERT INTO USER_info (`ID`, `PASSWORD`, `ACCESS`) 
VALUES ('fakergoat', 'F4k3r1996!', TRUE);

-- Create visitor logs table
CREATE TABLE IF NOT EXISTS visitor_logs (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `visit_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `page_url` VARCHAR(255) NULL,
    `session_id` VARCHAR(64) NULL,
    `is_unique` TINYINT(1) DEFAULT 1,
    INDEX idx_visit_time (`visit_time`),
    INDEX idx_ip_address (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `activity_type` ENUM('LOGIN_ATTEMPT', 'SESSION_CHECK', 'PAGE_ACCESS', 'LOGOUT', 'ADMIN_ACTION') NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `status` ENUM('SUCCESS', 'FAILURE') NOT NULL,
    `user_id` VARCHAR(50) NULL,
    `details` TEXT NULL,
    INDEX idx_timestamp (`timestamp`),
    INDEX idx_activity_type (`activity_type`),
    INDEX idx_status (`status`),
    INDEX idx_user_id (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create statistics cache table
CREATE TABLE IF NOT EXISTS stats_cache (
    `stat_id` INT AUTO_INCREMENT PRIMARY KEY,
    `stat_name` VARCHAR(50) NOT NULL,
    `stat_value` INT DEFAULT 0,
    `cache_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `update_interval` INT DEFAULT 300,
    UNIQUE KEY unique_stat (`stat_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial statistics data
INSERT INTO stats_cache (`stat_name`, `stat_value`, `update_interval`) VALUES
    ('total_visitors', 0, 300),
    ('today_visitors', 0, 60),
    ('active_users', 0, 30)
ON DUPLICATE KEY UPDATE `update_interval` = VALUES(`update_interval`);

-- Insert sample visitor data
INSERT INTO visitor_logs (`visit_time`, `ip_address`, `page_url`, `is_unique`) VALUES
    (NOW() - INTERVAL 2 HOUR, '192.168.1.100', '/faker/home/index.html', 1),
    (NOW() - INTERVAL 1 HOUR, '192.168.1.101', '/faker/home/index.html', 1),
    (NOW() - INTERVAL 30 MINUTE, '192.168.1.102', '/faker/admin/index.html', 1);

-- Insert sample activity logs
INSERT INTO activity_logs (`timestamp`, `activity_type`, `ip_address`, `status`, `user_id`, `details`) VALUES
    (NOW() - INTERVAL 2 HOUR, 'LOGIN_ATTEMPT', '192.168.1.100', 'SUCCESS', 'fakergoat', 'Admin login successful'),
    (NOW() - INTERVAL 1 HOUR, 'PAGE_ACCESS', '192.168.1.101', 'SUCCESS', NULL, 'Accessed home page'),
    (NOW() - INTERVAL 30 MINUTE, 'LOGIN_ATTEMPT', '192.168.1.102', 'FAILURE', NULL, 'Invalid credentials');

-- Create visitor statistics update procedure
DELIMITER //

DROP PROCEDURE IF EXISTS update_visitor_stats//

CREATE PROCEDURE update_visitor_stats()
BEGIN
    -- Update total visitors
    UPDATE stats_cache 
    SET `stat_value` = (SELECT COUNT(DISTINCT `ip_address`) FROM visitor_logs),
        `cache_time` = CURRENT_TIMESTAMP
    WHERE `stat_name` = 'total_visitors';

    -- Update today's visitors
    UPDATE stats_cache 
    SET `stat_value` = (
        SELECT COUNT(DISTINCT `ip_address`) 
        FROM visitor_logs 
        WHERE DATE(`visit_time`) = CURRENT_DATE
    ),
    `cache_time` = CURRENT_TIMESTAMP
    WHERE `stat_name` = 'today_visitors';

    -- Update active users
    UPDATE stats_cache 
    SET `stat_value` = (
        SELECT COUNT(DISTINCT `ip_address`) 
        FROM visitor_logs 
        WHERE `visit_time` >= NOW() - INTERVAL 15 MINUTE
    ),
    `cache_time` = CURRENT_TIMESTAMP
    WHERE `stat_name` = 'active_users';
END//

DELIMITER ;

-- Enable event scheduler and create update event
SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS update_stats_event;

CREATE EVENT update_stats_event
ON SCHEDULE EVERY 1 MINUTE
DO CALL update_visitor_stats();

-- Create database user and grant privileges
CREATE USER IF NOT EXISTS 'faker'@'localhost' IDENTIFIED BY 'F4k3r_1s_H4rd_T0_Gu3ss';
GRANT ALL PRIVILEGES ON fakerDB.* TO 'faker'@'localhost';
FLUSH PRIVILEGES;