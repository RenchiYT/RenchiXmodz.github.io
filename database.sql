-- RenchiXmodz - Database Schema
-- Run: mysql -u root -p < database.sql

CREATE DATABASE IF NOT EXISTS renchixmodz;
USE renchixmodz;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    discord_id VARCHAR(50) DEFAULT NULL,
    discord_avatar VARCHAR(255) DEFAULT NULL,
    role ENUM('user','admin','banned') DEFAULT 'user',
    free_fire_uid VARCHAR(20) DEFAULT NULL,
    proxy_token VARCHAR(64) DEFAULT NULL UNIQUE,
    token_expires DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL
);

-- Proxy sessions table
CREATE TABLE IF NOT EXISTS proxy_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    server_region VARCHAR(20) NOT NULL,
    session_id VARCHAR(64) NOT NULL UNIQUE,
    ip_address VARCHAR(45) DEFAULT NULL,
    bytes_sent BIGINT DEFAULT 0,
    bytes_received BIGINT DEFAULT 0,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ended_at DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Proxy servers table
CREATE TABLE IF NOT EXISTS proxy_servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    region_code VARCHAR(10) NOT NULL UNIQUE,
    region_name VARCHAR(50) NOT NULL,
    server_host VARCHAR(255) NOT NULL,
    server_port INT DEFAULT 443,
    ping_ms INT DEFAULT 0,
    is_online TINYINT(1) DEFAULT 1,
    load_percent INT DEFAULT 0
);

-- Insert default proxy servers
INSERT INTO proxy_servers (region_code, region_name, server_host, server_port, ping_ms) VALUES
('us_east', 'US East (New York)', 'us-east.proxy.renchixmodz.xyz', 443, 45),
('us_west', 'US West (LA)', 'us-west.proxy.renchixmodz.xyz', 443, 52),
('eu', 'Europe (Frankfurt)', 'eu.proxy.renchixmodz.xyz', 443, 38),
('sg', 'Singapore', 'sg.proxy.renchixmodz.xyz', 443, 28),
('jp', 'Japan (Tokyo)', 'jp.proxy.renchixmodz.xyz', 443, 32),
('br', 'Brazil (São Paulo)', 'br.proxy.renchixmodz.xyz', 443, 65),
('in', 'India (Mumbai)', 'in.proxy.renchixmodz.xyz', 443, 22);

-- Activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Discord integration settings
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NOT NULL
);

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'RenchiXmodz'),
('site_url', 'https://renchixmodz.xyz'),
('discord_client_id', ''),
('discord_client_secret', ''),
('discord_guild_id', ''),
('proxy_token_valid_hours', '24'),
('maintenance_mode', '0');
