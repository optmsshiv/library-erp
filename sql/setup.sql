CREATE DATABASE erp_config;
USE erp_config;

CREATE TABLE subdomain_map (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    subdomain   VARCHAR(100) NOT NULL UNIQUE,
    db_host     VARCHAR(150) DEFAULT 'localhost',
    db_name     VARCHAR(100) NOT NULL,
    db_user     VARCHAR(100) NOT NULL,
    db_pass     VARCHAR(100) NOT NULL,
    client_name VARCHAR(200),
    plan        ENUM('basic','premium','enterprise') DEFAULT 'basic',
    active      TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);