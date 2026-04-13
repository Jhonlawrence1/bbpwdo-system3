USE bbpwdo;

-- Create tables
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pwd_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    suffix VARCHAR(20),
    sex VARCHAR(20),
    age INT,
    birthdate DATE,
    blood_type VARCHAR(10),
    civil_status VARCHAR(30),
    contact_number VARCHAR(20),
    address TEXT,
    pwd_id_number VARCHAR(50),
    issued_date DATE,
    expiry_date DATE,
    is_registered ENUM('Yes', 'No') DEFAULT 'No',
    employment_status VARCHAR(50),
    employment_type VARCHAR(50),
    education_elementary VARCHAR(100),
    education_highschool VARCHAR(100),
    education_college VARCHAR(100),
    education_vocational VARCHAR(100),
    disability_type TEXT,
    assistive_device TEXT,
    guardian_name VARCHAR(100),
    guardian_relationship VARCHAR(50),
    guardian_contact VARCHAR(20),
    guardian_address TEXT,
    skills TEXT,
    trainings TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS family_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pwd_id INT NOT NULL,
    name VARCHAR(100),
    age INT,
    civil_status VARCHAR(30),
    relationship VARCHAR(50),
    occupation VARCHAR(100),
    FOREIGN KEY (pwd_id) REFERENCES pwd_records(id) ON DELETE CASCADE
);

-- Insert admin user (username: admin, password: 1985)
INSERT INTO users (username, password) VALUES 
('admin', '$2y$10$THuq6ZobEsSSzM6V99Tjk.6ZIans3pY7skBojqsYX0QIfSw3xHbfy');