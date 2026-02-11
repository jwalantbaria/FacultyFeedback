CREATE DATABASE IF NOT EXISTS faculty_feedback CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE faculty_feedback;

CREATE TABLE IF NOT EXISTS subjects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS faculty_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    academic_year VARCHAR(20) NOT NULL,
    semester VARCHAR(10) NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    number_of_students INT UNSIGNED NOT NULL,
    result_file VARCHAR(255) DEFAULT NULL,
    feedback_file VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

CREATE TABLE IF NOT EXISTS results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    enrollment_no VARCHAR(50) NOT NULL,
    student_name VARCHAR(150) NOT NULL,
    grade VARCHAR(5) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_session_enrollment (session_id, enrollment_no),
    INDEX idx_results_session (session_id),
    FOREIGN KEY (session_id) REFERENCES faculty_sessions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS feedback (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    enrollment_no VARCHAR(50) DEFAULT NULL,
    student_name VARCHAR(150) DEFAULT NULL,
    student_email VARCHAR(150) DEFAULT NULL,
    submitted_at VARCHAR(100) DEFAULT NULL,
    q1 DECIMAL(4,2) NOT NULL,
    q2 DECIMAL(4,2) NOT NULL,
    q3 DECIMAL(4,2) NOT NULL,
    q4 DECIMAL(4,2) NOT NULL,
    q5 DECIMAL(4,2) NOT NULL,
    q6 DECIMAL(4,2) NOT NULL,
    q7 DECIMAL(4,2) NOT NULL,
    q8 DECIMAL(4,2) NOT NULL,
    q9 DECIMAL(4,2) NOT NULL,
    q10 DECIMAL(4,2) NOT NULL,
    q11 DECIMAL(4,2) NOT NULL,
    q12 DECIMAL(4,2) NOT NULL,
    overall_score DECIMAL(4,2) NOT NULL,
    opinion TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_feedback_session (session_id),
    INDEX idx_feedback_enrollment (enrollment_no),
    FOREIGN KEY (session_id) REFERENCES faculty_sessions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL UNIQUE,
    average_feedback_score DECIMAL(4,2) NOT NULL,
    total_responses INT UNSIGNED NOT NULL,
    grade_analysis_json JSON NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES faculty_sessions(id) ON DELETE CASCADE
);

INSERT INTO subjects (name) VALUES
    ('Database Management Systems'),
    ('Operating Systems'),
    ('Software Engineering')
ON DUPLICATE KEY UPDATE name = VALUES(name);
