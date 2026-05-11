-- ============================================================
-- Online Voting System — Database Schema
-- Created by: Abebe Zemen
-- ============================================================

CREATE DATABASE IF NOT EXISTS voting_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE voting_system;

-- Users table (voters + admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    national_id VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('voter','admin') DEFAULT 'voter',
    is_verified TINYINT(1) DEFAULT 0,
    profile_photo VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Elections table
CREATE TABLE elections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('upcoming','active','closed') DEFAULT 'upcoming',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Candidates table
CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    party VARCHAR(100) DEFAULT NULL,
    bio TEXT,
    photo VARCHAR(255) DEFAULT NULL,
    position INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
);

-- Votes table
CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    election_id INT NOT NULL,
    voter_id INT NOT NULL,
    candidate_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    UNIQUE KEY unique_vote (election_id, voter_id),
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    FOREIGN KEY (voter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
);

-- Audit log
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================================
-- Seed Data
-- ============================================================

-- Default admin (password: Admin@1234)
INSERT INTO users (full_name, email, national_id, password, role, is_verified) VALUES
('System Admin', 'admin@votesystem.com', 'ADMIN-001',
 '$2y$12$LR.MnNKExkDlzMhO0Xofd.gIqbmFvuAO4r3TDpT1Eiun3Y6/OB0vS', 'admin', 1);

-- Sample voters (password: Voter@1234)
INSERT INTO users (full_name, email, national_id, password, role, is_verified) VALUES
('Abebe Girma', 'abebe@example.com', 'ETH-123456', '$2y$12$abc...hashed', 'voter', 1),
('Tigist Haile', 'tigist@example.com', 'ETH-234567', '$2y$12$abc...hashed', 'voter', 1),
('Dawit Bekele', 'dawit@example.com', 'ETH-345678', '$2y$12$abc...hashed', 'voter', 1);

-- Sample election
INSERT INTO elections (title, description, start_date, end_date, status, created_by) VALUES
('Student Union President 2024',
 'Vote for your preferred candidate for Student Union President at Wollo University.',
 '2024-10-01 08:00:00', '2024-10-07 20:00:00', 'active', 1);

-- Sample candidates
INSERT INTO candidates (election_id, full_name, party, bio) VALUES
(1, 'Samuel Tesfaye', 'Progress Party', 'Third-year CS student committed to digital transformation of campus services.'),
(1, 'Hana Mulugeta', 'Unity Front', 'Engineering student with a vision for inclusive campus governance.'),
(1, 'Yonas Alemu', 'Reform Alliance', 'Mathematics student focused on academic excellence and student welfare.');
