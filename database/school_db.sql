-- Database: school_db
CREATE DATABASE IF NOT EXISTS school_db;
USE school_db;

-- Table structure for `users`
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','teacher') NOT NULL
);

-- Default Admin & Teacher Accounts
INSERT INTO users (username, password, role) VALUES
('admin', MD5('admin123'), 'admin'),
('teacher', MD5('teacher123'), 'teacher');
