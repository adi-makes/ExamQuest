-- =============================================================
-- ExamQuest Database Setup Script
-- Run in phpMyAdmin: paste into SQL tab and click Go
-- OR via CLI: mysql -u root -p < database.sql
-- =============================================================

CREATE DATABASE IF NOT EXISTS examquest_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE examquest_db;

-- -------------------------------------------------------
-- Table: syllabus
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS syllabus (
    syllabus_id     INT AUTO_INCREMENT PRIMARY KEY,
    regulation_year VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Table: branch
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS branch (
    branch_id   INT AUTO_INCREMENT PRIMARY KEY,
    branch_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Table: subject
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS subject (
    subject_id   INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(150) NOT NULL,
    semester     VARCHAR(10)  NOT NULL,
    subject_type VARCHAR(50)  NOT NULL DEFAULT 'Core Subject',
    syllabus_id  INT NOT NULL,
    branch_id    INT NOT NULL,
    study_year   INT NOT NULL,
    FOREIGN KEY (syllabus_id) REFERENCES syllabus(syllabus_id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id)   REFERENCES branch(branch_id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------
-- Table: questions
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS questions (
    question_id   INT AUTO_INCREMENT PRIMARY KEY,
    subject_id    INT  NOT NULL,
    question_text TEXT NOT NULL,
    marks         INT  NOT NULL DEFAULT 0,
    frequency     INT  NOT NULL DEFAULT 0,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subject(subject_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================
-- SEED DATA
-- =============================================================

INSERT INTO syllabus (regulation_year) VALUES
    ('VTU CBCS 2021 Scheme'),
    ('VTU CBCS 2018 Scheme'),
    ('Anna University 2021 Regulation'),
    ('Anna University 2017 Regulation');

INSERT INTO branch (branch_name) VALUES
    ('Computer Science & Engineering'),
    ('Electronics & Communication Engineering'),
    ('Electrical & Electronics Engineering'),
    ('Mechanical Engineering'),
    ('Civil Engineering'),
    ('Information Science & Engineering');

-- Subjects: VTU CBCS 2021 (id=1), CSE (id=1), Year 3
INSERT INTO subject (subject_name, semester, subject_type, syllabus_id, branch_id, study_year) VALUES
    ('Database Management Systems',          '5', 'Core Subject',     1, 1, 3),
    ('Operating Systems',                    '5', 'Core Subject',     1, 1, 3),
    ('Computer Networks',                    '5', 'Elective Subject',  1, 1, 3),
    ('Formal Languages and Automata',        '5', 'Theory Subject',    1, 1, 3),
    ('Microprocessors & Microcontrollers',   '6', 'Core Subject',     1, 1, 3),
    ('Software Engineering',                 '6', 'Core Subject',     1, 1, 3),
    ('Machine Learning',                     '6', 'Elective Subject',  1, 1, 3),
    ('Computer Graphics',                    '6', 'Elective Subject',  1, 1, 3);

-- Subjects: VTU CBCS 2021 (id=1), CSE (id=1), Year 4
INSERT INTO subject (subject_name, semester, subject_type, syllabus_id, branch_id, study_year) VALUES
    ('Artificial Intelligence',              '7', 'Core Subject',     1, 1, 4),
    ('Cloud Computing',                      '7', 'Elective Subject',  1, 1, 4),
    ('Cryptography & Network Security',      '8', 'Core Subject',     1, 1, 4);

-- Subjects: VTU CBCS 2021 (id=1), CSE (id=1), Year 2
INSERT INTO subject (subject_name, semester, subject_type, syllabus_id, branch_id, study_year) VALUES
    ('Data Structures and Algorithms',       '3', 'Core Subject',     1, 1, 2),
    ('Object Oriented Programming with Java','3', 'Core Subject',     1, 1, 2),
    ('Discrete Mathematics',                 '4', 'Theory Subject',    1, 1, 2);

-- Subjects: VTU CBCS 2021 (id=1), ECE (id=2), Year 3
INSERT INTO subject (subject_name, semester, subject_type, syllabus_id, branch_id, study_year) VALUES
    ('Digital Signal Processing',            '5', 'Core Subject',     1, 2, 3),
    ('VLSI Design',                          '5', 'Core Subject',     1, 2, 3),
    ('Embedded Systems',                     '6', 'Core Subject',     1, 2, 3),
    ('Analog Circuits',                      '5', 'Core Subject',     1, 2, 3);

-- Subjects: VTU CBCS 2018 (id=2), CSE (id=1), Year 3
INSERT INTO subject (subject_name, semester, subject_type, syllabus_id, branch_id, study_year) VALUES
    ('Database Management Systems',          '5', 'Core Subject',     2, 1, 3),
    ('Computer Networks',                    '5', 'Core Subject',     2, 1, 3),
    ('Software Engineering & Project Management', '6', 'Core Subject', 2, 1, 3);

-- =============================================================
-- QUESTIONS for DBMS (subject_id = 1)
-- =============================================================
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (1, 'Explain ACID properties in Database Management Systems with examples.', 10, 8),
    (1, 'What is Normalization? Explain 1NF, 2NF, and 3NF with examples.', 15, 6),
    (1, 'Differentiate between B-Tree and B+ Tree Indexing with suitable diagrams.', 7, 4),
    (1, 'Explain the architecture of a DBMS with a neat diagram.', 10, 3),
    (1, 'What is a Weak Entity set? Provide a real-world example and draw its ER diagram.', 5, 1),
    (1, 'Explain Relational Algebra and its fundamental operations with examples.', 10, 7),
    (1, 'What is a transaction? Explain concurrency control mechanisms in DBMS.', 10, 5),
    (1, 'Describe the Entity-Relationship model and its components with an example.', 10, 4),
    (1, 'What are triggers? Write a trigger for an inventory management system.', 7, 2),
    (1, 'Explain deadlock detection and prevention techniques in DBMS.', 10, 6);

-- =============================================================
-- QUESTIONS for Operating Systems (subject_id = 2)
-- =============================================================
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (2, 'Explain the various CPU scheduling algorithms with examples: FCFS, SJF, Round Robin, and Priority Scheduling.', 10, 7),
    (2, 'What is Deadlock? Explain the Banker''s algorithm for deadlock avoidance with an example.', 10, 8),
    (2, 'Differentiate between paging and segmentation. Explain page replacement algorithms.', 7, 5),
    (2, 'Explain virtual memory and demand paging with the concept of page faults.', 10, 6),
    (2, 'What are semaphores? Explain the producer-consumer problem using semaphores.', 10, 5);

-- =============================================================
-- QUESTIONS for Computer Networks (subject_id = 3)
-- =============================================================
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (3, 'Explain the OSI model with all 7 layers, their functions, and protocols at each layer.', 15, 9),
    (3, 'What is TCP/IP protocol suite? Compare it with the OSI reference model.', 10, 7),
    (3, 'Explain the concept of subnetting and CIDR notation with examples.', 10, 5),
    (3, 'What is CSMA/CD? Explain Ethernet frame structure and collision handling.', 7, 4),
    (3, 'Describe various routing algorithms: Distance Vector and Link State routing.', 10, 6);

-- =============================================================
-- QUESTIONS for Formal Languages (subject_id = 4)
-- =============================================================
INSERT INTO questions (subject_id, question_text, marks, frequency) VALUES
    (4, 'Define Finite Automata. Differentiate between DFA and NFA with examples.', 10, 6),
    (4, 'Explain Context-Free Grammars and parse trees with suitable examples.', 10, 5),
    (4, 'State and prove the Pumping Lemma for Regular Languages.', 7, 4),
    (4, 'What is a Turing Machine? Explain its components and working with an example.', 10, 3),
    (4, 'Explain the concept of NP-Completeness and give examples of NP-Complete problems.', 10, 2);
